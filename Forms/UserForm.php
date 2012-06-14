<?php

namespace Schmutzka\Forms;

use Schmutzka\Forms\Form,
	Nette\Utils\Html,
	Nette\Mail\Message;

class UserForm extends  Form
{
	/** @var \Models\User */
	private $userModel;


	/** @var array */
	public $roleList = array(
		"admin" => "Správce",
		"user" => "Uživatel",
	);

	/** @var int */
	public $passwordMinLength = 6;

	/** @persistent */
	public $id;

	/** @var string */
	public $redirect = "default";

	/** @var array */
	public $regexp = array();


	public function __construct($context)
	{
		parent::__construct();
		$this->userModel = $context->models->user;
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
			->addRule(Form::EMAIL, "Email nemá správný formát");
	
		$this->addSelect("role", "Role:", $this->roleList)
			->setPrompt("Vyberte")
			->addRule(Form::FILLED,"Vyberte roli");

		$this->addPassword("password","Heslo:", 20);

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

		$this->addSubmit("send","Uložit");
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
			if(isset($values["changePassword"]) AND $values["changePassword"]) { 
				$values["password"] = sha1($values["password"]);
			}
			else {
				unset($values["password"]);
			}
			unset($values["changePassword"]);

			// email collision check
			if($this->userModel->item($values["email"], "email") AND $values["email"] != $userRow["email"]) {
				$this->presenter->flashMessage("Tento email je již obsazený.","flash-error");
				$this->presenter->redirect($this->redirect, array("id" => NULL));
			}
	
			$this->userModel->update($values, $this->id);
		}
		else {
			// password check
			$values["password"] = sha1($values["password"]);

			// email collision check
			if($this->userModel->item($values["email"], "email")) {
				$this->flashMessage("Tento email je již obsazený.","flash-error");
				$this->redirect($this->redirect, array("id" => NULL));
			}

			$values["reg_time"] = new \DateTime; // time of registrartion

			$this->userModel->insert($values);	
		}

		$this->flashMessage("Uloženo.", "flash-success");
		$this->redirect($this->redirect, array("id" => NULL));
	}
	
}