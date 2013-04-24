<?php

namespace Schmutzka\Models;

class ArticleInCategory extends BaseJoint
{

	/** 
	 * Save categories to article
	 * @param int
	 * @param array
	 */
	public function saveCategoryToArticle($articleId, $categoryList)
	{
		$this->table("article_id", $articleId)->delete();

		$array["article_id"] = $articleId;
		foreach ($categoryList as $id) {
			$array["article_category_id"] = $id;
			$this->insert($array);
		}
	
	}


}
