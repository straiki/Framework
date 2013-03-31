<?php

namespace Schmutzka\Models;

use Schmutzka\Utils\Name;
use Nette;
use NotORM;

abstract class Base extends Nette\Object
{
	/** @var string */
	protected $tableName;

	/** @var string */
	protected $lang;

	/** @var NotORM */
	protected $db;


	public function __construct(NotORM $db, Nette\Application\Application $application)
	{
		$this->db = $db;

		if ($lang = $this->getLang($application)) {
			$this->lang = $lang;
		}

		$this->tableName = Name::tableFromClass(get_class($this));
	}


	/**
	 * Table shortcut
	 */
	final public function table()
	{
		// todo: autodected array?
		$args  = (array_filter(func_get_args()) ?: array());
		return call_user_func_array(array($this->db, $this->tableName), $args);
	}


	/********************** basic operations **********************/


	/** 
	 * @param array
	 */
	public function all($key = array())
	{
		if ($key) {
			return $this->table($key);

		} else {
			return $this->table();
		}
	}


	/** 
	 * @param array
	 * @return int
	 */
	public function insert($array)
	{
		$this->table()->insert($array);
		return $this->getLastId();
	}


	/**
	 * @param mixed
	 * @return array|NULL
	 */
	public function item($key)
	{
		if (is_array($key)) {
			return $this->table($key)->fetchRow(); // todo: protect fetchRow() from error if empty result

		} else {
			return $this->table("id", $key)->fetchRow(); // todo: protect fetchRow() from error if empty result
		}
	}


	/**
	 * @param array
	 * @return array
	 */
	public function update($array, $key)
	{
		if (is_array($key)) {
			$this->table($key)->update($array);	

		} else {
			$this->table("id", $key)->update($array);
		}

		return $this->item($key);
	}


	/**
	 * @param mixed
	 * @param array
	 */
	public function duplicate($key, $change = array())
	{
		if (is_array($key)) {
			$result = $this->all($key);

		} else {
			$row = $this->item($key);
			unset($row["id"]);
			return $this->insert($row);
		}

		foreach ($result as $row) {
			unset($row["id"]);
			if ($change) {
				foreach ($change as $keyChange => $valueChange) {
					if (isset($row[$keyChange])) {
						$row[$keyChange] = $valueChange;
					}
				}
			}

			$this->insert($row);
		}
	}


	/**
	 * @param mixed
	 * @param int
	 */
	public function delete($key)
	{
		if (is_array($key)) {
			return $this->table($key)->delete();

		} else {
			return $this->table("id", $key)->delete();
		}
	}


	/**
	 * @param array
	 * @return int
	 */
	public function count($key = array())
	{
		return $this->table($key)->count("*");
	}


	/********************** fetch* **********************/


	/**
	 * Get table rows as pairs
	 * @param string
	 * @param int
	 * @param array
	 * @param string
	 * @return array
	 */
	public function fetchPairs($id = "id", $column = NULL, $key = array(), $order = NULL)
	{
		return $this->table($key)->order($order)->fetchPairs($id, $column);
	}


	/**
	 * Fetch random table row
	 * @return array
	 */
	public function fetchRandom()
	{
		return $this->table()->order("RAND()")->limit(1)->fetchRow();
	}


	/**
	 * Fetch single
	 * @param array
	 * @param mixed
	 * @return mixed
	 */
	public function fetchSingle($column, $key)
	{
		if (is_array($key)) {
			return $this->table($key)->fetchSingle($column);

		} else {
			return $this->table("id", $key)->fetchSingle($column);
		}
	}


	/**
	 * Get last id
	 * @param string
	 */
	public function getLastId($column = "id")
	{
		return $this->table()->order("$column DESC")->fetchSingle($column);
	}

	
	/**
	 * Insert, update on duplicate key
	 * @param array
	 * @param mixed
	 */
	public function upsert($data, $unique)
	{
		if (!is_array($unique)) {
			if (!$unique) {
				return $this->table()->insert($data);
			}
	
			$unique = array("id" => $unique);
		}

		return $this->table()->insert_update($unique, $data, $data);
	}


	/********************** helpers **********************/


	/**
	 * Get lang from url if set
	 * @param Nette\Application\Application
	 * @return string|NULL
	 */
	private function getLang(Nette\Application\Application $application)
	{
		if ($requests = $application->getRequests()) {
			$parameters = $requests[0]->getParameters();
			if (isset($parameters["lang"])) {
				return $parameters["lang"];
			}
		}

		return NULL;
	}

}
