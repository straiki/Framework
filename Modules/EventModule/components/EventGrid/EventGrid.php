<?php

namespace EventModule\Components;

use Nette;
use NiftyGrid;
use Schmutzka\Application\UI\Module\Grid;

class EventGrid extends Grid
{
	/** @inject @var Schmutzka\Models\Event */
	public $eventModel;

	/** @inject @var Schmutzka\Models\EventCategory  */
	public $eventCategoryModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;


	protected function configure($presenter)
	{
		$source = new NiftyGrid\DataSource($this->eventModel->fetchAll()->order('date DESC, time DESC'));
		$this->setDataSource($source);
		$this->setModel($this->eventModel);

		$this->addColumn('title', 'Název');

		if ($this->moduleParams->categories && $categoryList = $this->eventCategoryModel->fetchPairs('id', 'name')) {
			$this->addColumn('event_category_id', 'Kategorie', '20%')->setListRenderer($categoryList);
		}

		$this->addColumn('when', 'Datum', '10%')->setDateRenderer();

		if ($this->moduleParams->galleryLink && $galleryList = $this->galleryModel->fetchPairs('id', 'name')) {
			$this->addColumn('gallery_id', 'Galerie', '18%')->setListRenderer($galleryList);
		}
		if ($this->moduleParams->calendar) {
			$this->addColumn('display_in_calendar', 'V kalendáři', '5%')->setBoolRenderer();
		}

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
	}

}