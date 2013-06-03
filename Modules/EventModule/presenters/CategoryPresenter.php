<?php

namespace EventModule;

use Schmutzka;

class CategoryPresenter extends Schmutzka\Application\UI\Module\Presenter
{
	/** @inject @var Schmutzka\Models\EventCategory */
	public $eventCategoryModel;

}
