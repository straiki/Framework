<?php

namespace TextSnippetModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class TextSnippetGrid extends Grid
{
	/** @inject @var Schmutzka\Models\TextSnippet */
	public $textSnippetModel;


	public function build()
	{
		$this->setPrimaryKey('id');
		$this->addColumn('name', 'Název');
		$this->addColumn('uid', 'Identifikátor');
		$this->addColumn('edited', 'Upraveno');
		$this->addColumn('user_id', 'Upravil');
		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
