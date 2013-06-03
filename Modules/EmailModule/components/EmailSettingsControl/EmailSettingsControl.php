<?php

namespace EmailModule\Components;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;

class EmailSettingsControl extends Control
{
	/** @persistent @var int */
	public $id = 1;

	/** @inject @var Schmutzka\Models\EmailSettings */
	public $emailSettingsModel;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText("info_email_name", "Info email - zobrazované jméno:");
		$form->addText("noreply_email_name", "No-reply email - zobrazované jméno:");
		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}

}
