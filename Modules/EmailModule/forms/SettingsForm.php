<?php

namespace EmailModule\Forms;

use Nette;
use Schmutzka;
use Schmutzka\Application\UI\Form;

class SettingsForm extends Form
{
	/** @inject @var Schmutzka\Models\EmailSettings */
	public $emailSettingsModel;


	public function build()
    {
		parent::build();

		$this->addText("info_email_name","Info email - zobrazované jméno:");
		$this->addText("noreply_email_name","No-reply email - zobrazované jméno:");

		$this->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		$this->setDefaults($this->emailSettingsModel->item(1));
	}


	public function process(Form $form)
	{
		$values = $form->values;

		$this->emailSettingsModel->update($values, 1);
		$this->presenter->flashMessage("Uloženo.","success");
		$this->presenter->redirect("this");
	}

}
