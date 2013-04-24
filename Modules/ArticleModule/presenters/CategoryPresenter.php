<?php

namespace ArticleModule;

use Forms;
use Grids;

class CategoryPresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;


	/**
	 * @param int
	 */
	public function renderEdit($id) 
	{ 
		$this->loadItem($this->articleCategoryModel, $id);
	} 


	/**
	 * Article category form
	 */
	public function createComponentArticleCategoryForm()
	{
		return new Forms\CategoryForm($this->articleCategoryModel, $this->id);
	}


	/**
	 * Article category grid
	 */
	protected function createComponentArticleCategoryGrid()
	{
		return new Grids\CategoryGrid($this->articleCategoryModel);
	}

}