<?php

namespace Schmutzka\Forms;

use Nette;
use Schmutzka;
use Schmutzka\Utils\Password;
use Schmutzka\Application\UI\Form;

class ChangePasswordForm extends Form
{
	/** @var int */
	public $minLength = 5;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;


	public function build()
	{
		parent::build();

		$this->addPassword("oldPassword","Staré heslo:")
			->addRule(Form::FILLED, "Zadejte staré heslo");
		$this->addPassword("password","Nové heslo:")
			->addRule(Form::FILLED, "Zadejte nové heslo")
			->addRule(Form::MIN_LENGTH, "Heslo musí mít aspoň %d znaků", $this->minLength);
		$this->addPassword('passwordCheck',"Znovu nové heslo:")
			->addRule(Form::FILLED,"Zadejte heslo k ověření")
			->addRule(Form::EQUAL,"Hesla musejí být schodná",$this["password"]);
		$this->addSubmit("send","Změnit heslo");
	}


	public function process($form)
	{
		$values = $form->values;

		$key = array(
			"id" => $this->user->id,
			"password" => Password::saltHash($values["oldPassword"], $this->paramService->salt)
		);

		if ($record = $this->userModel->item($key)) {
			$data["password"] = Password::saltHash($values["password"], $this->salt);
			$this->userModel->update($data, $this->user->id);

			$this->flashMessage("Heslo bylo úspěšně změněno.", "success");

		} else {
			$this->flashMessage("Zadali jste chybně staré heslo.", "error");
		}

		$this->redirect("this");
	}

}
