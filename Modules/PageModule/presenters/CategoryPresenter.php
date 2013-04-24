<?php

namespace PageModule;

use Forms;
use Grids;

class CategoryPresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\PageCategory */
	public $pageCategoryModel;


	/**
	 * @param int
	 */
	public function renderEdit($id) 
	{ 
		$this->loadItem($this->pageCategoryModel, $id);
	} 


	/**
	 * Page category form
	 */
	public function createComponentPageCategoryForm()
	{
		return new Forms\CategoryForm($this->pageCategoryModel, $this->id);
	}


	/**
	 * Page category grid
	 */
	protected function createComponentPageCategoryGrid()
	{
		return new Grids\CategoryGrid($this->pageCategoryModel);
	}

}
