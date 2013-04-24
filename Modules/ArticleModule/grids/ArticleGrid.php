<?php

namespace ArticleModule\Grids;

use NiftyGrid;
use Schmutzka;

class ArticleGrid extends NiftyGrid\Grid
{
	/** @persistent */
    public $id;

	/** @var Schmutzka\Models\Article */
    private $articleModel;

	/** @var Schmutzka\Models\ArticleCategory */
	private $articleCategoryModel;

	/** @var array */
    private $moduleParams;


	public function inject(Schmutzka\Models\Article $articleModel, Schmutzka\Models\ArticleCategory $articleCategoryModel, Schmutzka\Config\ParamService $paramService)
	{
        $this->articleModel = $articleModel;
		$this->articleCategoryModel = $articleCategoryModel;
		$this->moduleParams = $paramService->getModuleParams($this->getReflection()->getName());
	}


	/**
	 * Configure
	 * @param Presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->articleModel->all());
        $this->setDataSource($source);
		$this->useFlashMessage = FALSE;

		// grid structure
		$this->addColumn("title", "Název", "20%");

		if ($this->moduleParams["categories"]) {
			if ($this->moduleParams["categories_multi"]) {
				$this->addColumn("article_category_id", "Kategorie", "20%")->setRenderer(function ($row) {
					dd("TDO articleInCategory model!§!!!");
				});

			} else {
				$this->addColumn("article_category_id", "Kategorie", "20%")->setListRenderer($this->articleCategoryModel->fetchPairs("id", "name"));
			}
		}

		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}
