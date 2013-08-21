<?php

namespace PageModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class PageGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;


	public function build()
	{
		$this->setPrimaryKey('id');
		$this->addColumn('title', 'Název');
		$this->addColumn('edited', 'Upraveno');
		$this->addColumn('user_id', 'Upravil');
		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
