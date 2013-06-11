<?php

namespace EventModule\Components;

use NiftyGrid;
use Schmutzka;
use Schmutzka\Application\UI\Module\Grid;

class EventCategoryGrid extends Grid
{
	/** @inject @var Schmutzka\Models\EventCategory */
    public $eventCategoryModel;


    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->eventCategoryModel->fetchAll());
        $this->setDataSource($source);
        $this->setModel($this->eventCategoryModel);

		$this->addColumn("name", "Název");
		if ($this->moduleParams->enable_expiration) {
			$this->addColumn("use_expiration", "Expirovat", "20%")->setBoolRenderer();
		}
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
    }

}
