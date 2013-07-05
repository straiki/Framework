<?php

namespace Schmutzka\Models;

use Schmutzka\Utils\Name;
use Nette;
use NotORM;

abstract class Base extends Nette\Object
{
	/** @inject @var NotORM */
	public $db;


	/**
	 * Table shortcut
	 */
	final public function table()
	{
		$tableName = Name::tableFromClass(get_class($this));

		$args = func_get_args();
		if (count($args) == 1 && is_numeric($args[0])) {
			array_unshift($args, "id");
		}

		foreach ($args as $key => $value) {
			if ($key === "id") {
				$args[$tableName . ".id"] = $value;
				unset($args[$key]);
			}
		}

		return call_user_func_array(array($this->db, $tableName), $args);
	}


	/********************** basic operations **********************/


	/**
	 * @param  array
	 * @return  NotORM_Result
	 */
	public function fetchAll($key = array())
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
	 * @param array|int
	 * @return array|NULL
	 */
	public function item($key)
	{
		try {
			return $this->table($key)->fetchRow();

		} catch (\Exception $e) {
			return FALSE;
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
			$result = $this->fetchAll($key);

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
	 * @param string $column
	 * @return array
	 */
	public function fetchPairs($id = "id", $column = NULL, $key = array())
	{
		return $this->table($key)->fetchPairs($id, $column);
	}


	/**
	 * Fetch list shortcut
	 * @param array
	 * @return array
	 */
	public function fetchList($key = array())
	{
		return $this->fetchPairs("id", "name", $key);
	}


	/**
	 * Get list by user id
	 * @param  int $userId
	 * @return  array { [ id => name ] }
	 */
	public function fetchListByUser($userId)
	{
		return $this->table(array("user_id" => $userId))
			->fetchPairs("id", "name");
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
	 * @param  string
	 * @return array
	 */
	public function fetchByUid($uid)
	{
		return $this->table("uid", $uid)->fetchRow();
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

}
