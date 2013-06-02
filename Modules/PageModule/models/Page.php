<?php

namespace Schmutzka\Models;

class Page extends Base
{
	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;


	/**
	 * Fetch front
	 */
	public function fetchFront()
	{
		return $this->table("publish_state", "public");
	}	


	/**
	 * Get item front
	 * @param int
	 * @return array|FALSE
	 */
	public function getItemFront($id)
	{
		$result = $this->table("id", $id)
			->where("publish_state", "public");

		if (count($result)) {
			return $result->fetchRow();
		}

		return FALSE;
	}


	/**
	 * Get item
	 * @param  int
	 * @return  array
	 */
	public function item($id)
	{
		$item = parent::item($id);
		if ($this->paramService->cmsSetup->modules->page->access_to_roles) {
			$item["access_to_roles"] = unserialize($item["access_to_roles"]);
		}

		return $item;
	}

}
