<?php

namespace EmailModule\Forms;

use Schmutzka\Forms\Form;
use Schmutzka;
use Nette;

class SettingsForm extends Form
{
	/** @inject @var Schmutzka\Models\EmailSettings */
	public $emailSettingsModel;


	/**
	 * Build form
	 */
	public function build()
    {
		parent::build();

		$this->addText("info_email_name","Info email - zobrazované jméno:");
		$this->addText("noreply_email_name","No-reply email - zobrazované jméno:");

		$this->addSubmit();
		$this->setDefaults($this->emailSettingsModel->item(1));
	}


	/**
	 * Process form
	 */
	public function process(Form $form)
	{
		$values = $form->values;

		$this->emailSettingsModel->update($values, 1);
		$this->flashMessage("Uloženo.","success");
		$this->redirect("this");
	}

}
