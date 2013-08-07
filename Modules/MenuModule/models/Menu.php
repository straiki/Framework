<?php

namespace Schmutzka\Models;


class Menu extends BaseTree
{

	/**
	 * @param  int
	 * @param  int
	 * @param  int
	 */
	public function updateParentRank($parent, $rank, $id)
	{
		$data = array(
			'parent_id' => $parent,
			'rank' => $rank
		);

		$this->update($data, $id);
	}


	/**
	 * @return NotORM_Result
	 */
	public function fetchFront()
	{
		return $this->fetchStructure(array(
			'active' => 1
		));
	}


	/**
	 * @param  array
	 * @return NotORM_Result
	 */
	public function fetchData($cond = array())
	{
		return $this->fetchAll($cond)
			->order('rank')
			->select('menu.*, page.title pageTitle');
	}

}
