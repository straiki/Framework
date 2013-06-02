<?php

namespace PageModule\Grids;

use Nette;
use Schmutzka;
use NiftyGrid;
use Schmutzka\Application\UI\Module\Grid;

class PageGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;


	/**
	 * @param  Nette\Application\IPresenter $presenter
	 */
	protected function configure(Nette\Application\IPresenter $presenter)
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
