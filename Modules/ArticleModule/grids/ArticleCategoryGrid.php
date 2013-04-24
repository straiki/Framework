<?php

namespace ArticleModule\Grids;

use Schmutzka;
use NiftyGrid;

class ArticleCategoryGrid extends NiftyGrid\Grid
{
	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;


	/**
	 * Configure
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$source = new NiftyGrid\DataSource($this->articleCategoryModel->all());
		$this->setDataSource($source);
		$this->setModel($this->articleCategoryModel);

		$this->addColumn("name", "Název");
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
