<?php

namespace GalleryModule\Components;

use NiftyGrid;
use Schmutzka\Application\UI\Module\Grid;

class GalleryGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;


	/**
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$source = new NiftyGrid\DataSource($this->galleryModel->fetchAll());
		$this->setDataSource($source);
		$this->setModel($this->galleryModel);

		$this->addColumn("name", "Název");

		if ($presenter->moduleParams->description) {
			$this->addColumn("description", "Popisek");
		}
		$this->addColumn("created", "Vytvořeno")->setDateRenderer();

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}
