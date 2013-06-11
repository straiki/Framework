<?php

namespace ArticleModule\Components;

use NiftyGrid;
use Schmutzka;
use Schmutzka\Application\UI\Module\Grid;

class ArticleGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;


	protected function configure($presenter)
	{
		$source = new NiftyGrid\DataSource($this->articleModel->fetchAll());
		$this->setDataSource($source);
		$this->setModel($this->articleModel);

		$this->addColumn("title", "Název");
		if ($this->moduleParams->categories) {
			$this->addColumn("article_categories_name", "Kategorie")->setRenderer(function ($row) {
				return implode($row->article_categories_name, ", ");
			});
		}

		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Upravil", "12%")->setListRenderer($this->userModel->fetchPairs("id", "login"));
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
