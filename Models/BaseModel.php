<?php

namespace Models;

use Schmutzka\Utils\Name;

class Base extends \Nette\Object
{

	/** @var \Notorm */
	protected $db;

	/** @var \Nette\Caching\Cache */
	protected $cache;

	/** @var string */
	protected $tableName;

	/** @var string */
	protected $lang;


	public function __construct(\NotORM $notorm, \Nette\Caching\Cache $cache, \Nette\DI\Container $context)
	{
		$this->db = $notorm;
		$this->cache = $cache;

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
		}
		else {
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
		}
		else {
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
		}
		else {
			$row = $this->item($key);
			unset($row["id"]);
			return $this->insert($row);
		}

		foreach($result as $row) {
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
		}
		else {
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
		}
		else {
			$record = $this->table("id", $key);		
		}

		if ($record->count("*")) {
			return $record;
		}
		else {
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
		$record = $this->exist($key);

		if ($record) {
			return $record->fetchRow();
		}
		else {
			return FALSE;
		}
	}
	
	
	/**
	 * Get number of table rows
	 * @param array
	 * @return int
	 */
	public function count($where = NULL)
	{
		$key = $this->tableName . ($where ?  "_" . md5(serialize($where)) : NULL);
	
		if (isset($this->cache[$key])) {
			return (int) $this->cache[$key];
		}

		if ($where) {
			$count = $this->table($where)->count("*");
		}
		else {
			$count = $this->table()->count("*");
		}

		if ($count > 1000) {
			$this->cache->save($key, $count, array(
				"expire" => (int) (time() + 60 * 60 * 24 * ($count/500000)), // Jean's magic constant
			));
		}

		return $count;
	}


	/**
	 * Get table rows as pairs
	 * @param string $column
	 * @return array
	 */
	public function fetchPairs($id = "id", $column = NULL, array $where = array())
	{
		$result = $this->table();
		if ($where) {
			$result->where($where);
		}

		return $result->fetchPairs($id, $column);
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
	public function fetchSingle($column, $where)
	{
		if (is_array($where)) {
			return $this->table($where)->fetchSingle($column);
		}
		else {
			return $this->table("id", $where)->fetchSingle($column);
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
		}
		else {
			return !$this->table($key)->count("*");
		}
	}


	/**
	 * Magic function
	 * @use 1: findByTag("apple") -> where("tag", "apple")
	 * @howtouse: http://pla.nette.org/cs/jednoduchy-model-s-notorm#toc-relacie-1-n
	 */
	public function __call($name, $args)
	{
        if (strpos($name, "findBy") !== FALSE) {
            $cammelCaseSplit = preg_split("~(?<=\\w)(?=[A-Z])~", str_replace("findBy", "", $name));
            $loweredCammels = array_map(function($in) {
                return strtolower($in);
            }, $cammelCaseSplit);
            $findCondition = implode(".", $loweredCammels);

            if (isset($args[1]) && true === $args[1]) {
                // M:N relation
                $relationTableName = $loweredCammels[0] . "s_" . $this->tableName;
                $mn = $this->db->{$relationTableName}($findCondition, $args[0])
                    ->select(substr($this->tableName, 0, -1) . "_id");

                try {
                    $result = $this->table("id", $mn);
                } catch (\PDOException $e) {
                    if (false !== strpos($e->getMessage(), "Table") && false !== strpos($e->getMessage(), "doesn't exist")) {
                        // switch table name elements
                        $relationTableName = $this->tableName . "_" . $loweredCammels[0] . "s";
                        $mn = $this->db->{$relationTableName}($findCondition, $args[0])
                            ->select(substr($this->tableName, 0, -1) . "_id");

                        $result = $this->table("id", $mn);
                    } else {
                        throw $e;
                    }
                }

                return $result;
            } 
			else {
                // no or 1:N relation
                return $this->table()->where($findCondition, $args[0]);
            }
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