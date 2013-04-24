<?php

namespace Schmutzka\Models;

class PageInCategory extends BaseJoint
{

	/** 
	 * Save categories to pages
	 * @param int
	 * @param array
	 */
	public function saveCategoryToPage($pageId, $categoryList)
	{
		$this->table("page_id", $pageId)->delete();

		$array["page_id"] = $pageId;
		foreach ($categoryList as $id) {
			$array["page_category_id"] = $id;
			$this->insert($array);
		}
	}

}