<?php

namespace Models;

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


	/**
	 * @param NotORM
	 * @param Nette\DI\Container
	 */
	public function __construct(NotORM $db, Nette\DI\Container $context)
	{
		$this->db = $db;

		if (isset($context->params["activeLang"])) {
			$this->lang = $context->params["activeLang"];
		}

		$this->tableName = Name::tableFromClass(get_class($this));
	}


	/** 
	 * Retun records by condition
	 * @param string
	 * @param string
	 */
	public function all(array $key = array())
	{
		if ($key) {
			return $this->table($key);

		} else {
			return $this->table();
		}
	}


	/** 
	 * Insert record
	 * @param array
	 * @return int
	 */
	public function insert($array)
	{
		$this->table()->insert($array);
		return $this->getLastId();
	}


	/**
	 * Update record
	 * @param array
	 * @param mixed
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
	 * Duplicates record
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
	 * Delete record
	 * @param array
	 * @param string
	 */
	public function delete($key)
	{
		if ($record = $this->exist($key)) {
			return $record->delete();

		} else {
			return FALSE;
		}
	}


	/**
	 * Get 1 item
	 * @param mixed
	 * @return array
	 */
	public function item($key)
	{
		if ($record = $this->exist($key)) {
			return $record->fetchRow();

		} else {
			return FALSE;
		}
	}


	/**
	 * Check if record exists
	 * @param array
	 * @return array/FALSE
	 */
	public function exist($key)
	{		
		if (is_array($key)) {
			$record = $this->table($key);		

		} else {
			$record = $this->table("id", $key);		
		}

		return $record;
	}

	
	/**
	 * Get number of table rows
	 * @param array
	 * @return int
	 */
	public function count($key = NULL)
	{
		if ($key) {
			return $this->table($key)->count("*");

		} else {
			return $this->table()->count("*");
		}
	}


	/**
	 * Get table rows as pairs
	 * @param string $column
	 * @return array
	 */
	public function fetchPairs($id = "id", $column = NULL, array $key = array())
	{
		return $this->table($key)->fetchPairs($id, $column);
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
		$record = $this->table()->order("$column DESC")->fetchSingle($column);
		if (is_null($record)) {
			return 0;
		}

		return $record;	
	}


	/**
	 * Insert, update on duplicate key (
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


	/**
	 * Check if value in column is free
	 * @param array
	 * @param mixed
	 * @return bool
	 */
	public function isFree(array $key, $id = NULL)
	{
		if ($id) {
			return !$this->table($key)->where("NOT id", $id)->count("*");

		} else {
			return !$this->table($key)->count("*");
		}
	}


	/**
	 * Table shortcut
	 */
	final public function table()
	{
		return call_user_func_array(array($this->db, $this->tableName), func_get_args());
	}


	/**
	 * Set up language
	 * @param string
	 */
	public function setLang($lang)
	{
		$this->lang = $lang;
	}

}