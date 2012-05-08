<?php

/**
 * Base model functions
 * @dev: possible fully-cached queries
 * @dev: cache invalidation on update/insert/delete of record + it's id? or whole? could be awesome :)
 * @2DO: insert_update
 */

namespace Models;

class Base extends \Nette\Object
{

    /** @var \Notorm */
    protected $db;

    /** @var \Nette\Caching\Cache */
    protected $cache;

	/** @var string */
	protected $tableName;


	public function __construct(\NotORM $notorm, \Nette\Caching\Cache $cache)
	{
		$this->db = $notorm;
		$this->cache = $cache;
		$this->tableName = $this->tableNameByClass(get_class($this));
	}


	/**
	 * Get table name by class name [Pages => pages, ArticleTag => article_tag]
	 * @param string
	 * @return string
	 */
	private function tableNameByClass($className)
	{
		$tableName = explode("\\", $className);
		$tableName = lcfirst(array_pop($tableName));

		$replace = array(); // A => _a
		foreach (range("A", "Z") as $letter) {
			$replace[$letter] = "_".strtolower($letter);
		}

		return strtr($tableName, $replace); 
	}


	/** 
	 * Retun records by condition
	 * @param string
	 * @param string
	 */
	public function all($key = NULL, $value = NULL)
	{
		if (is_array($column)) { // array where
			return $this->db->{$this->tableName}($key);
		}
		elseif ($column AND $value) { // 1 column condition
			return $this->db->{$this->tableName}($key, $value);
		}
		else {
			return $this->db->{$this->tableName};
		}
	}


	/** 
	 * Insert record
	 * @param array
	 * @param string
	 * @return mixed
	 */
	public function insert($array, $returnColumn = "id")
	{
		$row = $this->db->{$this->tableName}()->insert($array);
		if ($returnColumn) {
			return $row[$returnColumn];
		}
		else {
			return $row->fetchRow();
		}
	}


	/**
	 * Update record
	 * @param array/object ArrayHash
	 * @param int/array
	 * @param string
	 */
	public function update($array, $id, $columnName = "id")
	{
		$array = (array) $array; // NotORM array strict
		if(is_array($id) OR is_object($id)) { // array / arrayHash
			$where = (array) $id;
			return $this->db->{$this->tableName}->where($where)->update($array);	
		}
		else {
			return $this->db->{$this->tableName}($columnName, $id)->update($array);
		}
	}

	
	/**
	 * Delete record
	 * @param int
	 * @param string
	 */
	public function delete($id, $columnName = "id")
	{
		if($record = $this->exist($id, $columnName)) {
			return $record->delete();
		}
		else { // doesn't exist
			return NULL;
		}
	}


	/**
	 * Check record existance
	 * @param int / array / arrayHash
	 * @param string
	 * @param int
	 * @param string
	 * @return NULL/record
	 */
	public function exist($id, $columnName = "id", $checkId = NULL, $checkColumnName = NULL)
	{		
		if(is_array($id) OR is_object($id)) { // array / arrayHash
			$where = (array) $id;
			$record = $this->db->{$this->tableName}->where($where);	
		}
		else {
			$record = $this->db->{$this->tableName}($columnName, $id);	
		}

		if($checkId AND $checkColumnName) {
			$record->where($checkColumnName, $checkId);
		}

		if($record->count("*")) {
			return $record;
		}
		else { // doesn't exist
			return FALSE;
		}
	}


	/**
	 * Get 1 item
	 * @param int
	 * @param string	
	 * @param int
	 * @param string
	 */
	public function item($id, $columnName = "id", $checkId = NULL, $checkColumnName = NULL)
	{
		if(is_array($id) OR is_object($id)) { // array / arrayHash
			$where = (array) $id;
			if($record = $this->exist($where)) {
				return $record->fetchRow();
			}
			else {
				return FALSE;
			}
		}
		elseif($record = $this->exist($id, $columnName, $checkId, $checkColumnName)) {
			return $record->fetchRow();
		}
		else { // doesn't exist
			return FALSE;
		}
	}
	

	/**
	 * Get 1 item column by another column identification
	 * @param mixed
	 * @param string
	 */
	public function itemColumn($needle, $columnName, $return = "id")
	{
		$record = $this->item($needle, $columnName);
		if($record) {
			return $record[$return];
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
		$cache = $this->cache;

		if($where) {
			$key = $this->tableName."_".md5(serialize($where));
		}
		else {
			$key = $this->tableName;
		}
	
		if(isset($cache[$key])) {
			return (int) $cache[$key];
		}
		else {
			if($where) {
				$count = $this->db->{$this->tableName}($where)->count("*");
			}
			else {
				$count = $this->db->{$this->tableName}()->count("*");
			}
			if($count < 1000) {
				return $count;
			}
			$cache->save($key, $count, array(
			    "expire" => (int) (time() + 60 * 60 * 24 * ($count / 500000)), // Jean's magic constant
			));
			return $count;
		}
	}


	/**
	 * Get table rows as pairs (keys = IDs, values = column).
	 * @param string $column
	 * @return array
	 */
	public function fetchPairs($column, $id = "id", array $where = NULL)
	{
		$result = $this->db->{$this->tableName};
		if($where) {
			$result->where($where);
		}

		if($column == "id") {
			return $result->fetchPairs("id");
		}

		return $result->fetchPairs($id, $column);
	}


	/**
	 * Fetch random table row.
	 * @return array
	 */
	public function fetchRandom()
	{
		return $this->db->{$this->tableName}()->order("RAND()")->limit(1)->fetchRow();
	}


	/**
	 * Fetch single
	 * @param array
	 * @param string
	 * @return mixed
	 */
	public function fetchSingle($where, $column)
	{
		return $this->db->{$this->tableName}->where($where)->fetchSingle($column);
	}


	/**
	 * Updates if id is set
	 * @param array
	 * @param mixed|int
	 * @param string
	 */
	public function upsert($array, $recordId = NULL, $columnName = "id")
	{
		try {
			return $this->db->{$this->tableName}($columnName, $recordId)->update($array);
		} catch (\Exception $e) {
			return $this->db->{$this->tableName}->insert($array);
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
                    $result = $this->db->{$this->tableName}("id", $mn);
                } catch (\PDOException $e) {
                    if (false !== strpos($e->getMessage(), "Table") && false !== strpos($e->getMessage(), "doesn't exist")) {
                        // switch table name elements
                        $relationTableName = $this->tableName . "_" . $loweredCammels[0] . "s";
                        $mn = $this->db->{$relationTableName}($findCondition, $args[0])
                            ->select(substr($this->tableName, 0, -1) . "_id");

                        $result = $this->db->{$this->tableName}("id", $mn);
                    } else {
                        throw $e;
                    }
                }

                return $result;
            } 
			else {
                // no or 1:N relation
                return $this->db->{$this->tableName}()->where($findCondition, $args[0])->fetchRow();
            }
        }
    }



}