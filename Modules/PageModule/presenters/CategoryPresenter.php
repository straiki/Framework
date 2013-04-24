<?php

namespace PageModule;


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
		$this->loadItemHelper($this->pageCategoryModel, $id);
	}

}
