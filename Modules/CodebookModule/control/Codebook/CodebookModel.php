<?php

namespace Models;

class Codebook extends Base
{

	/**
	 * Check table existence, create elsewhere
	 * @var string
	 */
	public function checkExistance($tableName)
	{
		try { // how to safely check existance?
			$this->table();
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
		$result = $this->table("type", $type)->order("rank")->fetchPairs("id");
		if ($withCount AND $whereUsed AND is_array($whereUsed)) {
			$array = array();
			foreach ($result as $key => $value) {
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
	 * Get codebook list
	 * @param string
	 * @param bool include hidden
	 * @param string
	 */
	public function getCodesByType2($type, $showAll = TRUE, $column = NULL)
	{
		if (!$column) {
			return $this->table("type", $type)->order("rank")->fetchPairs("id", "value");
		}

		if ($showAll == TRUE) {
			$result = $this->table("type", $type)->order("rank")->fetchPairs("id");
			$array = array();
			foreach ($result as $key => $value) {
				$array[$key] = $value;
				$array[$key]["column"] = $column;
				$array[$key]["useCount"] = $this->getCodeUseCount($column, $key); // počet použití daného kódu
			}
			return $array;
		}

		return $this->table("type", $type)->where("display", 1)->order("rank")->fetchPairs("id");
	}


	/**
	 * Returns all code by type in pairs
	 * @param string
	 */
	public function getCodeListByType($type)
	{
			return $this->table("type", $type)->order("rank, value")->fetchPairs("id", "value");
	}



	/**
	 * Number of use case
	 * @param array
	 */
	private function getCodeUseCount(array $whereUsed, $value)
	{
		return $this->db->{$whereUsed["table"]}->where($whereUsed["column"], $value)->count("*");
	}


	/**
	 * Return last rank
	 * @param int
	 */
	public function getNextRank($codeType)
	{
		$last = $this->table("type", $codeType)->order("rank DESC")->fetchSingle("rank");
		if ($last) {
			return round($last, -1) + 10;
		}
		else {
			return 10;
		}
	}


	/**
	 * Converts all codes to different one
	 * @param array
	 * @param mixed
	 * @param mixed
	 */
	public function convert($whereUsed, $old, $new)
	{
		$result = $this->db->{$whereUsed["table"]}($whereUsed["column"], $old);
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
		return $this->db->{$anulTable}($column, $id)->update($array);
	}

}