<?php

namespace Schmutzka\Models;

use Nette;

class Article extends Base
{
	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @var string item select */
	private $select = "article.*, gallery_file.name as titlePhoto, CONCAT(user.name, ' ', user.surname) AS authorName";


	/**
	 * Fetch front
	 * @param  array
	 * @param int|NULL
	 * @return  NotORM_Result
	 */
	public function fetchFront($cond = array(), $limit = NULL)
	{
		$result = $this->table()
			->select($this->select);

		if ($cond) {
			$result->where($cond);
		}

		if ($limit) {
			$result->limit($limit);
		}

		if ($this->moduleParams->publish_state) {
			$result->where("publish_state", "public");
		}

		if ($this->moduleParams->publish_datetime) {
			$result->where("publish_datetime <= ? OR publish_datetime IS NULL", new Nette\DateTime)
				->order("publish_datetime DESC");

		} else {
			$result->order("id DESC");
		}

		if ($this->moduleParams->categories_multi) {
			foreach ($result as $key => $row) {
				$result[$key]["categoryList"] = $this->articleInCategoryModel->getCategoryListByArticle($key);
			}
		}

		return $result;
	}


	/**
	 * Fetch front by category id
	 * @param  int
	 * @return  NotORM_Result
	 */
	public function fetchFrontByCategory($categoryId)
	{
		if ($this->moduleParams->categories_multi) {
		$result = $this->articleInCategoryModel->fetchAll()->where("article_in_category.article_category_id", $categoryId)
			->join("gallery_file", "LEFT JOIN gallery_file ON article.gallery_file_id = gallery_file.id")
			->join("user", "LEFT JOIN user ON article.user_id = user.id")
			->select($this->select);

		} else {
			$result = $this->articleInCategoryModel->fetchAll(array("article_category_id" => $categoryId));
		}

		return $result;
	}


	/**
	 * Get item front
	 * @param int
	 * @return array|FALSE
	 */
	public function getItemFront($id)
	{
		$result = $this->table("article.id", $id)
			->select($this->select);

		if ($this->moduleParams->publish_state) {
			$result->where("publish_state", "public");
		}

		if ($result) {
			return $result->fetchRow();

		} else {
			return FALSE;
		}
	}


	/********************** helpers **********************/


	/**
	 * @return  array
	 */
	public function getModuleParams()
	{
		return $this->paramService->getModuleParams("article");
	}

}
