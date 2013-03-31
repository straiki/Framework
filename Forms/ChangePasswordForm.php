<?php

namespace Schmutzka\Forms;

use Nette;
use Models;
use Schmutzka\Utils\Password;
use Schmutzka;

class ChangePasswordForm extends Form
{
	/** @var int */
	public $minLength = 5;

	/** @var Schmutzka\Models\User */
	private $userModel;

	/** @var Nette\Security\User */
	private $user;

	/** @var string */
	private $salt;


	/**
	 * @param Schmutzka\Models\User
	 * @param Nette\Security\User
	 * @param Schmutzka\Services\ParamService
	 */
	public function __construct(Schmutzka\Models\User $userModel, Nette\Security\User $user, Schmutzka\Config\ParamService $paramService)
	{
		parent::__construct();
		$this->userModel = $userModel;
		$this->user = $user;
		$this->salt = (isset($paramService->params["salt"])) ? $paramService->params["salt"] : NULL;
	}


	public function build()
	{
		parent::build();

		$this->addPassword("oldPassword","Staré heslo:")
			->addRule(Form::FILLED,"Zadejte staré heslo");
		$this->addPassword("password","Nové heslo:")
			->addRule(Form::FILLED,"Zadejte nové heslo");

		if ($this->minLength) {
			$this["password"]->addRule(Form::MIN_LENGTH, "Heslo musí mít aspoň %d znaků", $this->minLength);
		}

		$this->addPassword('passwordCheck',"Znovu nové heslo:")
			->addRule(Form::FILLED,"Zadejte heslo k ověření")
			->addRule(Form::EQUAL,"Hesla musejí být schodná",$this["password"]);   
		$this->addSubmit("send","Změnit heslo")
			->setAttribute("class","btn btn-primary");
	}


	public function process(ChangePasswordForm $form)
	{
		$values = $form->values;
		$record = $this->userModel->item(array(
			"id" => $this->user->id,
			"password" => Password::saltHash($values["oldPassword"], $this->salt)
		));
	
		if (count($record)) {
			$array["password"] = Password::saltHash($values["password"], $this->salt);
			$this->userModel->update($array, $this->user->id);

			$this->flashMessage("Heslo bylo úspěšně změněno.", "flash-success");

		} else {
			$this->flashMessage("Zadali jste chybně staré heslo.","flash-error");
		}

		$this->redirect("this");
	}
	
}