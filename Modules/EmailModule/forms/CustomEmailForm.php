<?php

namespace EmailModule\Forms;

use Schmutzka\Forms\Form;
use Models;
use Nette;
use Nette\Utils\Strings;
use Schmutzka\Utils\Filer;

class CustomEmailForm extends Form
{
	/** @persistent */
	public $id;

	/** @var Models\CustomEmail */
	private $customEmailModel;

	/** @var Schmutzka\Security\User */
	private $user;


	/**
	 * @param Models\CustomEmail
	 * @param Nette\Security\User
	 * @param int
	 */
	public function __construct(Models\CustomEmail $customEmailModel, Nette\Security\User $user, $id) 
	{ 
		parent::__construct(); 
		$this->customEmailModel = $customEmailModel;
		$this->user = $user;
		$this->id = $id;
	}


	/**
	 * Build form
	 */
	public function build()
    {
		parent::build();

		$this->addText("name","Název šablony:")
			->addRule(Form::FILLED, "Povinné");

		$this->addText("uid", "Systémové UID:")
			->addRule(Form::FILLED, "Povinné");

		$this->addText("subject", "Předmět:");

		$this->addText("available_values", "Dostupné proměnné:")
			->setAttribute("class", "width600");
		if ($this->user->role != "superadmin") {
			$this["available_values"]->setDisabled();

		} else {
			$this["available_values"]->setOption("description","Ve formátu %VALUE%, oddělujte čárkou");
		}


		$this->addTextarea("body","Obsah:")
			->setAttribute("class","tinymce");

		$this->addSubmit();

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->customEmailModel->item($this->id);
			$this->setDefaults($defaults);
		}
	}


	/**
	 * Process form
	 */
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

		$this->flashMessage("Uloženo.", "flash-success");
		$this->redirect("default", array("id" => NULL));
	}

}