<?php

namespace Schmutzka\Models;

use Nette;

class BaseTree extends Base
{
	/** @var array */
	public $structure;

	/** @var string key column name */
	private $idColumn;

	/** @var string parent key column name */
	private $parentColumn;


	/**
	 * @param array
	 * @param string
	 * @param string
	 * @callback build()
	 */
	public function __construct($dataArray, $parentColumn = "parent_id", $idColumn = "id")
	{
		$this->parentColumn = $parentColumn;
		$this->idColumn = $idColumn;

		$this->structure = $this->build($dataArray);	
	}


	/**
	 * Build tree structure
	 * @param result
	 * @param string
	 * @param string	 
	 * @return array
	 */
	private function build($dataArray)
	{
		$structure = array();
		$i = 0;
	
		foreach ($dataArray as $row) {
			if (!$row[$this->parentColumn]) { // main cells with no parent
				$structure[$row[$this->idColumn]][] = $row;
			}
		}

		foreach ($structure as $row) {
			$structure[$row[0][$this->idColumn]][] = $this->getChildren($row[0][$this->idColumn], $dataArray);
		}

		return $structure;
	}


	/**
	 * Returns children by record id
	 * @param int
	 * @return array
	 */
	private function getChildren($parentId, $dataArray) {
		$array = array();
		foreach($dataArray as $row) {
			if($row[$this->parentColumn] == $parentId) {
				if(!is_array($row)) {  // result from database
					$row = iterator_to_array($row);
				}
				$array[$row[$this->idColumn]][] = $row; // info o buňce
			}
		}

		foreach($array as $key => $values) { 
			$array[$key][] = self::getChildren($key, $dataArray); // info o dětech
		}

		if(count($array)) {
			return $array;
		}
		return NULL;
	}


	/**
	 * Convert siple structure to fullroad pair lsit
	 * @param result (fetchPairs by id)
	 * @param string
	 * @param string
	 * @param string	
	 * @return array
	 * from: 5 => Current category
	 * to: 5 => Main \ Subcategory \ Current category
	 */
	public static function fullroadView($treeList, $parentColumn = "parent_id", $nameColumn = "name",  $sep = " » ")
	{
		$array = array();

		foreach($treeList as $key => $row) {
			$item = $row[$nameColumn];
			$above = $treeList[$key]["parent_id"];
	
			while(isset($above)) {
				$item = $treeList[$above][$nameColumn] . $sep . $item;
				$above = $treeList[$above]["parent_id"];
			}
			$array[$key] = $item;
		}

		return $array;		
	}


	/**
	 * Get structure
	 */
	public function getStructure()
	{
		return $this->structure;
	}

}
