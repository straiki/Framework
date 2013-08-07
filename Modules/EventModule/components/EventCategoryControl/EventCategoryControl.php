<?php

namespace EventModule\Controls;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class EventCategoryControl extends Control
{
	/** @inject @var Schmutzka\Models\EventCategory */
	public $eventCategoryModel;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText('name', 'Název kategorie:')
			->addRule(Form::FILLED, 'Povinné');

		if ($this->moduleParams->expiration) {
			$form->addCheckbox('use_expiration', 'Povolit expiraci');
		}

		$form->addSubmit('send', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}

}
