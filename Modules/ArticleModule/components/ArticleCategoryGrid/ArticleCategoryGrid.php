<?php

namespace ArticleModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class ArticleCategoryGrid extends Grid
{
	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;


	public function build()
	{
		$this->setPrimaryKey('id');
		$this->addColumn('name', 'Název');

		$this->addRowAction('edit', 'Upravit', $this->editRecord);
		$this->addRowAction('delete', 'Smazat', $this->deleteRecord)
			->setConfirmation('Opravdu?');

		$this->setDataLoader($this->dataLoader);
	}

}
