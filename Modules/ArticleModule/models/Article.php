<?php

namespace Schmutzka\Models;

use Nette;


class Article extends Base
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;

	/** @var string item select */
	private $select = 'article.*, gallery_file.name titlePhoto, user.name authorName';


	/**
	 * @param  array
	 * @return  NotORM_Result
	 */
	public function fetchAll($cond = array())
	{
		$result = parent::fetchAll();
		$result = $this->completeResult($result);

		return $result;
	}


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

		$result = $this->completeResult($result);

		return $result;
	}


	/**
	 * Fetch front by category id(s)
	 * @param  int|array
	 * @return  NotORM_Result
	 */
	public function fetchFrontByCategory($categoryId)
	{
		$result = $this->articleInCategoryModel->fetchAll()
			->select($this->select);

		if ($this->moduleParams->categories) {
			$result->where('article_in_category.article_category_id', $categoryId)
				->join('gallery_file', 'LEFT JOIN gallery_file ON article.gallery_file_id = gallery_file.id')
				->join('user', 'LEFT JOIN user ON article.user_id = user.id');
		}

		$result = $this->completeResult($result);

		return $result;
	}


	/**
	 * Get item front
	 * @param int
	 * @return array|FALSE
	 */
	public function fetchItemFront($id)
	{
		$result = $this->table('article.id', $id)
			->select($this->select);

		if ($this->moduleParams->publishState) {
			$result->where('publish_state', 'public');
		}

		if ($result) {
			$item = $result->fetchRow();
			if ($this->moduleParams->attachmentGallery && $item['gallery_id']) {
				$item['gallery_files'] = $this->galleryFileModel->fetchOrderedListByGallery($page['gallery_id']);
			}

			return $item;

		} else {
			return FALSE;
		}
	}


	/**
	 * @param  id
	 * @return  array
	 */
	public function item($id)
	{
		$item = parent::item($id);
		$item = $this->completeItem($item);

		return $item;
	}


	/********************** helpers **********************/


	/**
	 * @return  array
	 */
	public function getModuleParams()
	{
		return $this->paramService->getModuleParams('article');
	}


	/**
	 * Complete result
	 * @param  NotORM_Result
	 * @return  NotORM_Result
	 */
	private function completeResult($result)
	{
		$result = $this->addPublicState($result);
		$result = $this->addOrder($result);
		$result = $this->addCategories($result);

		return $result;
	}


	/**
	 * Public state helper
 	 * @param  NotORM_Result
	 * @return  NotORM_Result
	 */
	private function addPublicState($result)
	{
		if ($this->moduleParams->publishState) {
			$result->where('publish_state', 'public');
		}

		return $result;
	}


	/**
	 * Order helper
	 * @param  NotORM_Result
	 * @return  NotORM_Result
	 */
	private function addOrder($result)
	{
		if ($this->moduleParams->publishDatetime) {
			$result->where('publish_datetime <= ? OR publish_datetime IS NULL', new Nette\DateTime)
				->order('publish_datetime DESC');

		} else {
			$result->order('id DESC');
		}

		return $result;
	}


	/**
	 * Add category list to result
	 * @param NotORM_Result
	 * @return NotORM_Result
	 */
	private function addCategories($result)
	{
		if ($this->moduleParams->categories) {
			foreach ($result as $key => $row) {
				$result[$key] = $this->completeItem($row);
			}
		}

		return $result;
	}


	/**
	 * @param  array|NotORM_Row
	 * @return array
	 */
	private function completeItem($item)
	{
		$item['article_categories'] = $this->articleInCategoryModel->fetchByMain($item['id']);
		$item['article_categories_name'] = $this->articleInCategoryModel->fetchByMain($item['id'], 'article_category.name');

		return $item;
	}

}
