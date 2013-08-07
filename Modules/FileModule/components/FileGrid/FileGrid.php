<?php

namespace FileModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class FileGrid extends Grid
{
	/** @inject @var Schmutzka\Models\File */
    public $fileModel;


	public function build()
    {
		$this->addColumn('name', 'Název');
		$this->addColumn('created', 'Upraveno');
		$this->addColumn('user_id', 'Přiřazeno k');
		$this->addEditRowAction();
		$this->addDeleteRowAction();
    }

}
