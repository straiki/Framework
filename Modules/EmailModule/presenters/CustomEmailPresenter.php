<?php

namespace EmailModule;

use Schmutzka\Application\UI\Module\Presenter;


class CustomEmailPresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\CustomEmail */
	public $customEmailModel;

}
