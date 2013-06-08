<?php

namespace ArticleModule\Grids;

use NiftyGrid;
use Schmutzka;
use Schmutzka\Application\UI\Module\Grid;

class ArticleGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;


	/**
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$source = new NiftyGrid\DataSource($this->articleModel->fetchAll());
		$this->setDataSource($source);
		$this->setModel($this->articleModel);

		$this->addColumn("title", "Název");
		if ($this->moduleParams->categories) {
			if ($this->moduleParams->categoriesMulti) {
				$articleInCategoryModel = $this->articleInCategoryModel;
				$this->addColumn("article_category_id", "Kategorie")->setRenderer(function ($row) use ($articleInCategoryModel) {
					$categories = array();
					foreach ($articleInCategoryModel->getCategoryListByArticle($row->id) as $category) {
						$categories[] = $category;
					}

					echo implode($categories, ", ");
				});

			} else {
				$this->addColumn("article_category_id", "Kategorie")->setListRenderer($this->articleCategoryModel->fetchPairs("id", "name"));
			}
		}

		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Upravil", "12%")->setListRenderer($this->userModel->fetchPairs("id", "login"));
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
