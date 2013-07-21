<?php

namespace PageModule\Components;

use Nette;
use NiftyGrid;
use Schmutzka;
use Schmutzka\Application\UI\Module\Grid;

class PageGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;


	protected function configure($presenter)
	{
		$moduleParams = $presenter->moduleParams;
		$source = new NiftyGrid\DataSource($this->pageModel->fetchAll());
		$this->setDataSource($source);
		$this->setModel($this->pageModel);

		$this->addColumn("title", "Název");
		$this->addColumn("edited", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Upravil", "15%")->setListRenderer($this->userModel->fetchPairs("id", "login"));
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
