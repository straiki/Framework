<?php

namespace Components;

use Closure;
use Nette;
use Schmutzka\Application\UI\Control;
use Schmutzka\Utils\Arrays;


/**
 * @method setTitle(string)
 * @method getTitle()
 * @method setLink(string)
 * @method getLink()
 * @method setDescription(string)
 * @method getDescription()
 * @method setLanguage(string)
 * @method getLanguage()
 */
class RssControl extends Control
{
	/** @inject @var Nette\Caching\Cache */
	public $cache;

	/** @var string */
	private $title;

	/** @var string */
	private $link;

	/** @var string */
	private $description;

	/** @var string */
	private $language;

	/** @var array */
	private $sources = array();

	/** @var array */
	private $itemKeys = array(
		'title' => 'title',
		'link' => 'link',
		'description' => 'description',
		'pubDate' => 'pubDate'
	);


	public function renderDefault()
	{
		if ($this->link == NULL) {
			$this->link = $this->presenter->link('//Homepage:default');
		}

		$this->template->title = $this->title;
		$this->template->link = $this->link;
		$this->template->description = $this->description;
		$this->template->language = $this->language;
		$this->template->items = $this->sourcesToItems($this->sources);
	}


	/**
	 * Add external source
	 * @lazy
	 * @param NotORM_Result|array
	 * @param array
	 * @param Closure
	 */
	public function addSource($result, $recode = array(), Closure $linkBuilder = NULL)
	{
		$this->sources[] = array(
			'result' => $result,
			'recode' => $recode,
			'linkBuilder' => $linkBuilder
		);
	}


	/********************** helpers **********************/


	/**
	 * Transform sources to items
	 * @param  array
	 * @return  array
	 */
	private function sourcesToItems($sources = array())
	{
		$cacheKey = $this->presenter->name . $this->presenter->view;
		if ($items = $this->cache->load($cacheKey)) {
			return $items;
		}

		$items = array();
		foreach ($sources as $source) {
			$itemKeys = array_merge($this->itemKeys, $source['recode']);

			foreach ($source['result'] as $row) {
				$item = array();
				foreach ($itemKeys as $key => $sourceKey) {
					$value = $row[$sourceKey];

					if ($key == 'link' && $source['linkBuilder']) {
						$value = $source['linkBuilder']($row);
					}

					$item[$key] = $value;
				}

				$items[] = $item;
			}
		}

		// newest items first
		Arrays::sortBySubKeyReverse($items, 'pubDate');

		foreach ($items as $key => $item) {
			if ($item['pubDate']) {
				$value = strtotime((string) $item['pubDate']);
				$item['pubDate'] = gmdate('D, d M Y H:i:s', $value) . ' GMT';
				$items[$key] = $item;
			}
		}

		$this->cache->save($cacheKey, $items, array(
			'expire' => '1 day'
		));

		return $items;
	}

}
