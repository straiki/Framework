<?php

namespace ArticleModule\Grids;

use NiftyGrid;
use Schmutzka;

class ArticleGrid extends NiftyGrid\Grid
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	/**
	 * Configure
	 * @param Presenter
	 */
	protected function configure($presenter)
	{
		$moduleParams = $presenter->moduleParams;

		$source = new NiftyGrid\DataSource($this->articleModel->all());
		$this->setDataSource($source);
		$this->setModel($this->articleModel);

		$this->addColumn("title", "Název");

		if ($moduleParams["categories"]) {
			if ($moduleParams["categories_multi"]) {
				$articleInCategoryModel = $this->articleInCategoryModel;
				$this->addColumn("article_category_id", "Kategorie", "40%")->setRenderer(function ($row) use ($articleInCategoryModel) {
					$categories = "";
					foreach ($articleInCategoryModel->getCategoryListByArticle($row->id) as $category) {
						$categories .= $category . ", ";
					}

					echo rtrim($categories, ", ");
				});

			} else {
				$this->addColumn("article_category_id", "Kategorie", "20%")->setListRenderer($this->articleCategoryModel->fetchPairs("id", "name"));
			}
		}

		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Upravil", "15%")->setListRenderer($this->userModel->fetchPairs("id", "login"));

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
