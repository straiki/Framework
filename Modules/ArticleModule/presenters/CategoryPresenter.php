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
		$this->loadItemHelper($this->articleCategoryModel, $id);
	}

}
