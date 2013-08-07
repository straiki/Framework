<?php

namespace Schmutzka\Templates;

use Nette;

class CmsHelpers extends Nette\Object
{
	/** @inject @var Nette\Http\IRequest */
	public $httpRequest;


	public function loader($helper)
	{
		if (method_exists($this, $helper)) {
			return callback($this, $helper);
		}
	}


	/**
	 * Enable page/article links
	 * @param string
	 * @return string
	 */
	public function enablePageArticleLinks($string)
	{
		preg_match_all('#(\[[^\[\]\n]++\])#U', $string, $matches);

		$pageList = $this->pageModel->fetchPairs('id', 'url');
		$articleList = $this->articleModel->fetchPairs('id', 'url');
		$from = $to = $replaceList = array();

		foreach ($matches as $row) {
			if ($row) {
				$item = $row[0];
				$item = trim($item, '[]');

				$itemParts = explode(':', $item);
				if (count($itemParts) != 3) {
					continue;
				}
				list($type, $id, $node) = $itemParts;
				$node = trim($node, '\'');

				if ($type == 'page') {
					$from[] = $row[0];
					$to[] = '<a href='../stranka/' . $pageList[$id] . ''>' . $node . '</a>';  // move to appliaction, link!, base path +

				} elseif ($type = 'article') {
					$from[] = $row[0];
					$to[] = '<a href='../clanek/' . $pageList[$id] . ''>' . $node . '</a>';
				}

			}
		}

		$string = str_replace($from, $to, $string);

		return $string;
	}

}
