<?php

namespace PageModule\Grids;

use Nette;
use Schmutzka;
use NiftyGrid;

class PageGrid extends NiftyGrid\Grid
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	protected function configure(Nette\Application\IPresenter $presenter)
	{
		$moduleParams = $presenter->moduleParams;
		$source = new NiftyGrid\DataSource($this->pageModel->fetchAll());
		$this->setDataSource($source);
		$this->setModel($this->pageModel);

		$this->addColumn("title", "Název", "35%");
		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Upravil", "15%")->setListRenderer($this->userModel->fetchPairs("id", "login"));

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
