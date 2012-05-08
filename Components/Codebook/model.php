<?php

namespace Models;

class CodebookControl extends Base
{
	
	

	/**
	 * Check table existence, create elsewhere
	 * @var string
	 */
	public function checkExistance($tableName)
	{
		try { // how to safely check existance?
			$this->db()->{$tableName}();
		}
		catch (\PDOException $e) { // table does not exist -> create it
			$createSql = file_get_contents(__DIR__."\create.sql");
			$this->getPdo($createSql);
		}	
	}


	/**
	 * Return all codes by type
	 * @param string
	 * @param bool
	 * @param array
	 */
	public function getCodesByType($type, $withCount = FALSE,  array $whereUsed = NULL)
	{
		$result = $this->db()->codebook("type", $type)->order("rank")->fetchPairs("id");
		if($withCount AND $whereUsed AND is_array($whereUsed))  {
			$array = array();
			foreach($result as $key => $value) {
				$array[$key] = $value;
				$array[$key]["useCount"] = $this->getCodeUseCount($whereUsed, $key); // počet použití daného kódu
			}
			return $array;
		}
		else {
			return $result;
		}
	}


	/**
	 * Returns all code by type in pairs
	 * @param string
	 */	
	public function getCodeListByType($type)
	{
			return $this->db()->codebook("type", $type)->order("rank")->fetchPairs("id", "value");
	}



	/**
	 * Number of use case
	 * @param array 
	 */
	private function getCodeUseCount(array $whereUsed, $value)
	{
		return $this->db()->{$whereUsed["table"]}->where($whereUsed["column"], $value)->count("*");
	}


	/**
	 * Return last rank
	 * @param int
	 */
	public function getNextRank($codeType)
	{
		$last = $this->db()->codebook("type", $codeType)->order("rank DESC")->fetchSingle("rank");
		if($last) {
			return round($last, -1) + 10;
		}
		else {
			return 10;
		}
	}


	/**
	 * Insert item
	 * @param array 
	 */
	public function insert($values, $returnColumn = "id")
	{
		return $this->db()->codebook()->insert($values);
	}


	/**
	 * Update item
	 * @param array 
	 * @param int
	 */
	public function update($values, $id, $returnColumn = "id")
	{
		return $this->db()->codebook($returnColumn, $id)->update($values);
	}


	/**
	 * Delete item
	 * @param int
	 */
	public function delete($id, $column = "id")
	{
		return $this->db()->codebook($column, $id)->delete();
	}


	/**
	 * Get an item
	 * @param int
	 */
	public function item($id, $column = "id", $checkId = NULL, $checkColumnName = NULL)
	{
		return $this->db()->codebook($column, $id)->fetchRow();
	}


	/**
	 * Converts all codes to different one
	 * @param array
	 * @param mixed
	 * @param mixed
	 */
	public function convert($whereUsed, $old, $new)
	{
		$result = $this->db()->{$whereUsed["table"]}($whereUsed["column"], $old);
		if($result->count("*")) { // je co převádět
			return $result->update(array($whereUsed["column"] => $new));
		}
		else { /// není co přávádět
			return FALSE;
		}
	}




	/** 
	 * Anulls code use
	 * @param string
	 * @param string
	 * @param int
	 */
	public function anull($anulTable, $column, $id)
	{
		$array = array(
			$column => NULL
		);
		return $this->db()->{$anulTable}($column, $id)->update($array);
	}








	/**
	 * Uloží hodnotu číselníku
	 * @array hodnoty
	 */
	public function save($values, $id = NULL)
	{
		if($id) {
			return $this->db()->codebook("id", $id)->update($values);
		}
		else {
			return $this->db()->codebook()->insert($values);
		}
	}


}