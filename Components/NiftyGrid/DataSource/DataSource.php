<?php

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author Jakub Holub
 * @author Tomáš Votruba
 *
 * @license New BSD Licence
 */
namespace NiftyGrid;

use NiftyGrid\FilterCondition,
	NotORM_Result;

class DataSource implements IDataSource
{
	/** @var NotORM_Result */
	private $data;


	/**
	 * @param NotORM_Result
	 */
	public function __construct(NotORM_Result $data)
	{
		$this->data = $data;
	}


	public function getData()
	{
		return $this->data->fetchPairs("id");
	}


	public function getPrimaryKey()
	{
		return "id";
	}


	public function getCount($column = "*")
	{
		return $this->data->count($column);
	}


	public function orderData($by, $way)
	{
		$this->data->order($by." ".$way);
	}


	public function limitData($limit, $offset)
	{
		$this->data->limit($limit, $offset);
	}


	public function filterData(array $filters)
	{
		foreach ($filters as $filter){
			if ($filter["type"] == FilterCondition::WHERE) {
				$column = $filter["column"];
				$value = $filter["value"];

				if (!empty($filter["columnFunction"])) {
					$column = $filter["columnFunction"]."(".$filter["column"].")";
				}
				$column .= $filter["cond"];

				if (!empty($filter["valueFunction"])) {
					$column .= $filter["valueFunction"]."(?)";
				}
				$this->data->where($column, $value);
			}
		}
	}

}