<?php

namespace Schmutzka\Models;

use Nette;
use Schmutzka;


class Article extends Base
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @var Schmutzka\Models\GalleryFile */
	private $galleryFileModel;


	public function injectModels(Schmutzka\Models\GalleryFile $galleryFileModel = NULL)
	{
		$this->galleryFileModel = $galleryFileModel;
	}


	/**
	 * @param  array
	 * @return  NotORM_Result
	 */
	public function fetchAll($cond = array())
	{
		$result = parent::fetchAll();
		$this->addOrder($result);

		return $result;
	}


	/**
	 * @param  array
	 * @param int
	 * @return  NotORM_Result
	 */
	public function fetchFront($cond = array(), $limit = NULL)
	{
		$result = $this->fetchAll($cond);

		if ($this->moduleParams->publishState) {
			$result->where('publish_state', 'public');
		}

		if ($limit) {
			$result->limit($limit);
		}

		return $result;
	}


	/**
	 * Fetch front by category id(s)
	 * @param  int|array
	 * @return  NotORM_Result
	 */
	public function fetchFrontByCategory($categoryId)
	{
		$result = $this->articleInCategoryModel->fetchAll();

		if ($this->moduleParams->categories) {
			$result->where('article_in_category.article_category_id', $categoryId)
				->join('gallery_file', 'LEFT JOIN gallery_file ON article.gallery_file_id = gallery_file.id');
		}

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
	 * @param  int|array
	 * @return  NotORM_Row
	 */
	public function fetch($key)
	{
		$row = parent::fetch($key);

		if ($this->moduleParams->categories) {
			$row['article_categories'] = $row->article_in_category()
				->fetchPairs('article_category_id', 'article_category_id');
		}

		return $row;
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
	 * @param  array|NotORM_Row
	 * @return array
	 */
	private function completeItem($item)
	{
		dd($item);

		$item['article_categories'] = $this->articleInCategoryModel->fetchByMain($item['id']);
		$item['article_categories_name'] = $this->articleInCategoryModel->fetchByMain($item['id'], 'article_category.name');

		return $item;
	}

}
