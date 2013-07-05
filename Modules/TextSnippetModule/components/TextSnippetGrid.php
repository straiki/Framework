<?php

namespace TextSnippetModule\Components;

use Schmutzka;
use NiftyGrid;

class TextSnippetGrid extends NiftyGrid\Grid
{
	/** @inject @var Schmutzka\Models\TextSnippet */
	public $textSnippetModel;


	/**
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$source = new NiftyGrid\DataSource($this->textSnippetModel->fetchAll());
		$this->setDataSource($source);
		$this->setModel($this->textSnippetModel);

		$this->addColumn("name", "Název", "25%");
		$this->addColumn("uid", "Identifikátor", "20%");
		$this->addColumn("content", "Obsah", NULL, 400);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(NULL, TRUE);
	}

}
