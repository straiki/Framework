<?php

namespace EmailModule\Components;

use Nette;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;

class CustomEmailControl extends Control
{
	/** @inject @var Schmutzka\Models\CustomEmail */
	public $customEmailModel;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText("name", "Název šablony:")
			->addRule(Form::FILLED, "Zadejte název šablony");
		$form->addText("uid", "Systémové UID:")
			->addRule(Form::FILLED, "Zadejte systémové UID");
		$form->addText("subject", "Předmět:");
		$form->addText("available_values", "Dostupné proměnné:")
			->setAttribute("class", "width600");

		if ($this->user->role == "admin") {
			$form["available_values"]->setOption("description", "Ve formátu %VALUE%, oddělujte čárkou");

		} else {
			$form["available_values"]->setDisabled();
		}

		$form->addTextarea("body", "Obsah:")
			->setAttribute("class", "ckeditor");

		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function preProcessValues($values)
	{
		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		return $values;
	}

}
