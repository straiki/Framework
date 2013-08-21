<?php

namespace EmailModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class CustomEmailGrid extends Grid
{
	/** @inject @var Schmutzka\Models\CustomEmail */
	public $customEmailModel;


	public function build()
	{
		$this->addColumn('name', 'Název');
		$this->addColumn('uid', 'Systémové UID');
		$this->addColumn('subject', 'Předmět');
		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
