<?php

namespace Schmutzka\Forms;

/**
 * Change password form
 */
class ChangePasswordForm extends Form
{
	/** @var context */
	private $context;

	/** @var int */
	private $minLength = 5;

	/** @var string */
	public $tableName = "user";


	/**
	 * Build the form
	 */
	public function __construct(\SystemContainer $context)
	{
		parent::__construct();

		$this->context = $context;
		if(isset($context->params["passwordMinLength"])) { 
			$this->minLength = $context->params["passwordMinLength"];
		}

		$this->addPassword("oldPassword","Současné heslo:",15)
			->addRule(Form::FILLED,"Zadejte současné heslo");
		$this->addPassword("password","Nové heslo:",15)
			->addRule(Form::FILLED,"Zadejte nové heslo");
		if($this->minLength) {
			$this["password"]->addRule(Form::MIN_LENGTH,"Heslo musí mít aspoň %d znaků",$this->minLength);
		}
        $this->addPassword('passwordCheck',"Znovu nové heslo:",15)
          ->addRule(Form::FILLED,"Zadejte heslo k ověření")
          ->addRule(Form::EQUAL,"Hesla musejí být schodná",$this["password"]);   
		$this->addSubmit("send","Změnit heslo")
			->setAttribute("class","btn btn-primary");

		$this->onSuccess[] = callback($this,"process");
	}


	/**
	 * Změní heslo 
	 * @form
	 */
	public function process(ChangePasswordForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();

		$user = $this->getPresenter()->user;
		$record = $this->context->database->{$this->tableName}($user->id)->where("password", sha1($values->oldPassword));

		if($record->count("*")) { // password fits
			if($values->password == $values->passwordCheck AND strlen($values->password) >= $this->minLength) { // change the password
				$record->update(array(
					"password" => sha1($values->password)
				));
				$presenter->flashMessage("Heslo bylo úspěšně změněno.", "pos");
			}
			else {
				$presenter->flashMessage("Hesla se neshodují, nebo je příliš krátké.","neg");
			}
		}
		else { // wront password
			$presenter->flashMessage("Zadali jste chybně současné heslo.","neg");
		}

		$presenter->redirect("this");
	}
	
}