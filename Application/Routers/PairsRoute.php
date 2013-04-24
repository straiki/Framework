<?php

/**
 * @use
 * $frontRouter[] = new PairsRoute("predmet/<id>", "Subject:detail", NULL, $container->database->subject, $container->cache, "subjectList");
 */

namespace Schmutzka\Application\Routers;

use Nette,
	Nette\Utils\Strings,
	Nette\Application\Request,
	Schmutzka\Utils\Arrays;

use Schmutzka\Utils\Name;
use NotORM_Result;

class PairsRoute extends \Nette\Application\Routers\Route
{
	/** @var NotORM\Result */
	private $table;

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
	 * @param NotORM_Result
	 * @param Nette\Caching\Cache
	 * @param array
	 */
	public function __construct($mask, $metadata = array(), $flags = 0, NotORM_Result $table, Nette\Caching\Cache $cache, $columns = array("id", "name")) 
	{	
		$this->mask = $mask;
		$this->metadata = $metadata;
		$this->table = $table;
		$this->cache = $cache;
		list($this->primaryKey, $this->secondaryKey) = $columns;

		$this->cacheTag = "route_" . sha1($mask);

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
		if (!is_numeric($keyParam) && !empty($keyParam)) {
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
	 * @return string|NULL
	 */
	public function constructUrl(Request $appRequest, Nette\Http\Url $refUrl)
	{
		if (isset($appRequest->parameters[$this->primaryKey])) {

			$params = $appRequest->parameters;
			$keyParam = $params[$this->primaryKey];

			// check if router matches (also router presenter)
			$mpv = explode(":", $this->metadata);
			$action = array_pop($mpv);

			if (($params["action"] == $action) && is_numeric($keyParam)) {
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
	 * @returns array
 	 */
	private function getPairList()
	{
		$key = $this->cacheTag;
		if (!$pairList = $this->cache->load($key)) {
			$pairList = $this->table->fetchPairs($this->primaryKey, $this->secondaryKey);

			foreach ($pairList as $key => $value) {
				$pairList[$key] = Strings::webalize($value);
			}
			
			$this->cache->save($key, $pairList, array(
				"tag" => $this->cacheTag,
				"expire" => "+30 mins"
			));
		}

		return $pairList;
	}


	/**
	 * Get cached list flipped
	 */
	private function getPairListFlipped()
	{
		$pairList = $this->getPairList();
		$pairList = array_flip($pairList);
	
		return $pairList;
	}


	/**
	 * Get by key
	 * @param int
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
	 * Get by value
	 * @param string|mixed
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