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
		$this->addColumn('article_categories_name', 'Kategorie');

		/*
			 ->setRenderer(function ($row) {
				return implode($row->article_categories_name, ', ');
			});
		}
		*/

		$this->addColumn('edited', 'Upraveno');
		$this->addColumn('user_id', 'Upravil');

		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
