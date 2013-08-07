<?php

namespace Schmutzka\Application\Routers;

use Nette;
use Nette\Application\Request;
use Nette\Utils\Strings;
use Nette\Http\Url;
use Schmutzka;
use Schmutzka\Utils\Arrays;
use Schmutzka\Utils\Name;


/**
 * @use: $frontRouter[] = new PairsRoute('predmet/<id>', 'Subject:detail', NULL, $this->subjectModel, $this->cache, array('id', 'url'));
 */
class PairsRoute extends Nette\Application\Routers\Route
{
	/** @var Schmutzka\Models\Base */
	private $model;

	/** @var array */
	private $columns;

	/** @var string */
	private $primaryKey;

	/** @var string */
	private $secondaryKey;

	/** @var Nette\Caching\Cache */
	private $cache;

	/** @var string */
	private $cacheTag;

	/** @var string */
	private $mask;

	/** @var string */
	private $metadata;


	/**
	 * @param string
	 * @param array
	 * @param array
	 * @param Schmutzka\Models\Base
	 * @param Nette\Caching\Cache
	 * @param array
	 */
	public function __construct($mask, $metadata = array(), $flags = 0, Schmutzka\Models\Base $model, Nette\Caching\Cache $cache, $columns = array('id', 'name'))
	{
		$this->mask = $mask;
		$this->metadata = $metadata;
		$this->model = $model;
		$this->cache = $cache;
		list($this->primaryKey, $this->secondaryKey) = $columns;

		$this->cacheTag = 'route_' . sha1($mask);

		parent::__construct($mask, $metadata, $flags);
	}


	/**
	 * Maps HTTP request to a Request object.
	 * @return HttpRequest|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		$appRequest = parent::match($httpRequest);
		if (!$appRequest) {
			return $appRequest;
		}

		$keyParam = $appRequest->parameters[$this->primaryKey];
		if (!empty($keyParam)) {
			$id = $this->getByValue($keyParam);
			if ($id === NULL) {
				return NULL;
			}

			$params = $appRequest->parameters;
			$params[$this->primaryKey] = $id;
			$appRequest->parameters = $params;
		}

		return $appRequest;
	}


	/**
	 * Constructs absolute URL from Request object.
	 * @param  Request
	 * @param  Url
	 * @return string|NULL
	 */
	public function constructUrl(Request $appRequest, Url $refUrl)
	{
		if (isset($appRequest->parameters[$this->primaryKey])) {

			$params = $appRequest->parameters;
			$keyParam = $params[$this->primaryKey];

			// check if router matches (also router presenter)
			$mpv = explode(':', $this->metadata);
			$action = array_pop($mpv);

			if (($params['action'] == $action) && $keyParam) {
				$value = $this->getByKey($keyParam);

				if ($value === NULL) {
					return NULL;
				}

				$params = $appRequest->parameters;
				$params[$this->primaryKey] = $value;
				$appRequest->parameters = $params;
			}
		}

		return parent::constructUrl($appRequest, $refUrl);
	}


	/********************** helpers **********************/


	/**
	 * Get cached list
	 * @return array
 	 */
	private function getPairList()
	{
		$pairList = $this->cache->load($this->cacheTag);

		if ($pairList == NULL) {
			$pairList = $this->model->fetchPairs($this->primaryKey, $this->secondaryKey);
			foreach ($pairList as $key => $value) {
				$pairList[$key] = Strings::webalize($value);
			}

			$this->cache->save($this->cacheTag, $pairList, array(
				'tag' => $this->cacheTag,
				'expire' => '30 mins'
			));
		}

		return $pairList;
	}


	/**
	 * Get cached list flipped
	 * @return  array
	 */
	private function getPairListFlipped()
	{
		$pairList = $this->getPairList();
		return array_flip($pairList);
	}


	/**
	 * @param  int
	 * @return  string|NULL
	 */
	private function getByKey($key)
	{
		$pairList = $this->getPairList();
		if (isset($pairList[$key])) {
			return $pairList[$key];
		}

		return NULL;
	}


	/**
	 * @param  string
	 * @return  srting|NULL
	 */
	private function getByValue($value)
	{
		$pairListFlipped = $this->getPairListFlipped();
		if (isset($pairListFlipped[$value])) {
			return $pairListFlipped[$value];
		}

		return NULL;
	}

}
