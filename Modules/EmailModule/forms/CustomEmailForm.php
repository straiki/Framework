<?php

namespace EmailModule\Forms;

use Nette;
use Schmutzka\Application\UI\Form;
use Schmutzka\Forms\ModuleForm;

class CustomEmailForm extends ModuleForm
{
	/** @inject @var Schmutzka\Models\CustomEmail */
	public $customEmailModel;


	public function build()
    {
		parent::build();

		$this->addText("name", "Název šablony:")
			->addRule(Form::FILLED, "Povinné");

		$this->addText("uid", "Systémové UID:")
			->addRule(Form::FILLED, "Povinné");

		$this->addText("subject", "Předmět:");

		$this->addText("available_values", "Dostupné proměnné:")
			->setAttribute("class", "width600");
		if ($this->user->role != "superadmin") {
			$this["available_values"]->setDisabled();

		} else {
			$this["available_values"]->setOption("description", "Ve formátu %VALUE%, oddělujte čárkou");
		}

		$this->addTextarea("body", "Obsah:")
			->setAttribute("class", "ckeditor");
	}


	public function process(Form $form)
	{
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->redirect("default", array("id" => NULL));
		}

		$values = $form->values;

		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		if ($this->id) {
			$this->customEmailModel->update($values, $this->id);

		} else {
			$this->customEmailModel->insert($values);
		}

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect("default", array("id" => NULL));
	}

}