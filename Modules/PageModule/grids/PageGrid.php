<?php

namespace PageModule\Grids;

use Nette;
use Schmutzka;
use NiftyGrid;

class PageGrid extends NiftyGrid\Grid
{
	/** @var Schmutzka\Models\Page */
    private $pageModel;

	/** @var Schmutzka\Models\PageCategpry */
    private $pageCategoryModel;

	/** @var Schmutzka\Models\User */
    private $userModel;

	/** @var array */
	private $moduleParams;


    public function inject(Schmutzka\Models\Page $pageModel, Schmutzka\Models\PageCategory $pageCategoryModel, Schmutzka\Models\User $userModel, Schmutzka\Config\ParamService $paramService)
    {
        $this->pageModel = $pageModel;
		$this->pageCategoryModel = $pageCategoryModel;
		$this->userModel = $userModel;
		$this->moduleParams = $paramService->getModuleParams($this->getReflection()->getName());
    }


    protected function configure(Nette\Application\IPresenter $presenter)
    {
        $source = new NiftyGrid\DataSource($this->pageModel->all());
        $this->setDataSource($source);
		$this->setModel($this->pageModel);

		$this->addColumn("title", "Název", "35%");

		if ($this->moduleParams->categories) {
			$this->addColumn("page_category_id", "Kategorie", "25%")->setListRenderer($this->pageCategoryModel->fetchPairs("id", "name"));
		}

		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Upravil", "15%")->setListRenderer($this->userModel->fetchPairs("id", "login"));

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}
