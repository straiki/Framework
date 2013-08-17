<?php

namespace ArticleModule\Components;

use Schmutzka;
use Schmutzka\Application\UI\Module\Grid;


class ArticleGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;


	public function build()
	{
		$this->addColumn('title', 'Název');
		if ($this->moduleParams->categories) {
			$this->addColumn('id', 'Kategorie'); // hack
		}
		$this->addColumn('edited', 'Upraveno');
		$this->addColumn('user_id', 'Upravil');

		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
