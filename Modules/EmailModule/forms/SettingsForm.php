<?php

namespace EmailModule\Forms;

use Schmutzka\Forms\Form;
use Models;
use Nette;

class SettingsForm extends Form
{
	/** @var Models\EmailSettings */
	private $settingsModel;


	/**
	 * @param Models\EmailSettings
	 */
	public function __construct(Models\EmailSettings $settingsModel) 
	{ 
		parent::__construct(); 
		$this->settingsModel = $settingsModel;
	}


	/**
	 * Build form
	 */
	public function build()
    {
		parent::build();

		$this->addText("info_email_name","Info email - zobrazované jméno:");
		$this->addText("noreply_email_name","No-reply email - zobrazované jméno:");

		$this->addSubmit();
		$this->setDefaults($this->settingsModel->item(1));
	}


	/**
	 * Process form
	 */
	public function process(Form $form)
	{
		$values = $form->values;

		$this->settingsModel->update($values, 1);
		$this->flashMessage("Uloženo.","flash-success");
		$this->redirect("this");
	}

}