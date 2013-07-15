<?php

namespace Schmutzka\Models;

class PageTree extends BaseTree
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @var string*/
	protected $parentColumn = "menu_parent";


	/**
	 * @return NotORM_Result
	 */
	public function fetchFront()
	{
		$cond = array(
			"menu_active" => 1
		);

		return $this->fetchStructure($cond);
	}


	/**
	 * @param  array
	 * @return NotORM_Result
	 */
	public function fetchData($cond = array())
	{
		return $this->pageModel->fetchAll($cond)
			->order("menu_rank");
	}

}
