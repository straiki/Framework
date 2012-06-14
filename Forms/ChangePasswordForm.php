<?php

namespace Schmutzka\Forms;

class ChangePasswordForm extends Form
{

	/** \SystemContainer */
	private $context;

	/** \Models\User */
	private $userModel;


	/** @var int */
	public $minLength = 5;

	/** @var string */
	public $tableName = "user";


	/**
	 * Build the form
	 */
	public function __construct(\SystemContainer $context)
	{
		parent::__construct();

		$this->context = $context;
		$this->userModel = $context->models->user;

		if (isset($context->params["passwordMinLength"])) { 
			$this->minLength = $context->params["passwordMinLength"];
		}

		$this->addPassword("oldPassword","Současné heslo:",15)
			->addRule(Form::FILLED,"Zadejte současné heslo");
		$this->addPassword("password","Nové heslo:",15)
			->addRule(Form::FILLED,"Zadejte nové heslo");

		if ($this->minLength) {
			$this["password"]->addRule(Form::MIN_LENGTH, "Heslo musí mít aspoň %d znaků", $this->minLength);
		}

        $this->addPassword('passwordCheck',"Znovu nové heslo:",15)
          ->addRule(Form::FILLED,"Zadejte heslo k ověření")
          ->addRule(Form::EQUAL,"Hesla musejí být schodná",$this["password"]);   
		$this->addSubmit("send","Změnit heslo")
			->setAttribute("class","btn btn-primary");
	}


	/**
	 * Change password
	 * @form
	 */
	public function process(ChangePasswordForm $form)
	{
		$values = $form->values;

		$user = $this->presenter->user;
		$record = $this->userModel->item(array("id" => $user->id, "password", sha1($values["oldPassword"])));

		if (count($record)) {
			if ($values["password"] == $values["passwordCheck"] AND strlen($values["password"]) >= $this->minLength) {
				$this->userModel->update(array(
					"password" => sha1($values["password"])
				), $user->id);

				$this->flashMessage("Heslo bylo úspěšně změněno.", "flash-success");
			}
			else {
				$this->flashMessage("Hesla se neshodují, nebo je příliš krátké.","flash-error");
			}
		}
		else {
			$this->flashMessage("Zadali jste chybně současné heslo.","flash-error");
		}

		$this->redirect("this");
	}
	
}