<?php

namespace EventModule;

use Schmutzka;

class HomepagePresenter extends Schmutzka\Application\UI\Module\Presenter
{
	/** @inject @var Schmutzka\Models\Event */
	public $eventModel;

}
