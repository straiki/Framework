<?php

namespace EmailModule;

use Schmutzka;

class CustomEmailPresenter extends Schmutzka\Application\UI\Module\Presenter
{
	/** @inject @var Schmutzka\Models\CustomEmail */
	public $customEmailModel;

}
