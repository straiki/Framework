<?php

namespace UserModule\Forms;

use Schmutzka;
use Schmutzka\Application\UI\Form;
use Nette\Utils\Html;

class UserForm extends  Form
{
	/** @persistent */
	public $id;

	/** @var array */
	public $roles = array(
		"admin" => "Správce",
		"user" => "Uživatel",
	);

	/** @var int */
	public $passwordMinLength = 6;

	/** @var string */
	public $redirect = "default";

	/** @var array */
	public $regexp = array();

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Config\ParamService */
	public $userModel;

	/** @var array */
	private $settings;


	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->settings = $presenter->moduleParams;
	}


	public function build()
	{
		parent::build();

		if ($this->id) { // editation
			$this->addGroup("");
		}

		$this->addText("name", "Jméno:");
		$this->addText("surname", "Příjmení:")
			->addRule(Form::FILLED,"Zadejte příjmení");

		$this->addText("email","Email:")
			->addRule(Form::FILLED, "Zadejte email")
			->addRule(Form::EMAIL, "Email nemá správný formát")
			->setAttribute("autocomplete","off");

		if (isset($this->settings["roles"])) {
			$this->roles = $this->settings["roles"];
		}
		$this->addSelect("role", "Role:", (array) $this->roles)
			->setPrompt("Vyberte")
			->addRule(Form::FILLED,"Vyberte roli");

		$this->addPassword("password","Heslo:", 20)
			->setAttribute("autocomplete","off");

		if ($this->id) {
			$this->addCheckbox("changePassword", "Změnit heslo?")
				->setDefaultValue(FALSE)
				->addCondition(Form::FILLED)
					->toggle("change_password");
		}

		if ($this->id) {
			$this->addGroup("")->setOption('container', Html::el('fieldset')->id("change_password")->style("display:none"));
		}
		$this["password"]
			->addCondition(Form::FILLED)
				->addRule(Form::MIN_LENGTH,"Heslo musí mít aspoň %d znaků.", $this->passwordMinLength);

		if ($this->regexp) {
			$this["password"]->addCondition(Form::FILLED)
				->addRule(Form::REGEXP, $this->regexp[1], $this->regexp[0]);
		}

		if (!$this->id) {
			$this["password"]->addRule(Form::FILLED, "Zadejte heslo");
		}

		$this->addPassword("password2","Potvrzení hesla:",20)
			->addRule(Form::EQUAL,"Hesla se neshodují. Zadejte je znovu, prosím.", $this["password"])
			->addCondition(Form::FILLED)
				->addRule(Form::MIN_LENGTH,"Heslo musí mít aspoň %d znaků.", $this->passwordMinLength);

		$this->addGroup();
	}


	protected function afterBuild()
	{
		$this->addSubmit("send", "Uložit"); // intentionally - enables override by custom form

		if ($this->id) {
			if ($defaults = $this->userModel->item($this->id)) {
				$this->setDefaults($defaults);
			}
		}
	}


	/**
	 * Process form
	 * @form
	 */
	public function process($form)
	{
		$values = $form->values;
		unset($values["password2"]);

		if ($this->id) { // edit
			$userRow = $this->userModel->item($this->id);

			// password check
			if (isset($values["changePassword"]) AND $values["changePassword"]) {
				$values["password"] = sha1($values["password"]);

			} else {
				unset($values["password"]);
			}
			unset($values["changePassword"]);

			// email collision check
			if ($this->userModel->item($values["email"], "email") && $values["email"] != $userRow["email"]) {
				$this->presenter->flashMessage("Tento email je již obsazený.","error");
				$this->presenter->redirect($this->redirect, array("id" => NULL));
			}

			$this->userModel->update($values, $this->id);

		} else {
			// password check
			$values["password"] = sha1($values["password"]);

			// email collision check
			if ($this->userModel->item($values["email"], "email")) {
				$this->flashMessage("Tento email je již obsazený.","error");
				$this->redirect($this->redirect, array("id" => NULL));
			}

			$values["created"] = new Nette\DateTime;

			$this->userModel->insert($values);
		}

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect($this->redirect, array("id" => NULL));
	}

}
