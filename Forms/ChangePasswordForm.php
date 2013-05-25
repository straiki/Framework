<?php

namespace Schmutzka\Forms;

use Nette;
use Schmutzka;
use Schmutzka\Security\UserManager;
use Schmutzka\Application\UI\Form;

class ChangePasswordForm extends Form
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;


	public function build()
	{
		parent::build();

		$this->addPassword("oldPassword", "Staré heslo:")
			->addRule(Form::FILLED, "Zadejte staré heslo");
		$this->addPassword("password", "Nové heslo:")
			->addRule(Form::FILLED, "Zadejte nové heslo")
			->addRule(Form::MIN_LENGTH, "Heslo musí mít aspoň %d znaků", 5);
		$this->addPassword('passwordCheck', "Znovu nové heslo:")
			->addRule(Form::FILLED, "Zadejte heslo k ověření")
			->addRule(Form::EQUAL, "Hesla musejí být schodná",$this["password"]);
		$this->addSubmit("send", "Změnit heslo")
			->setAttribute("class", "btn btn-primary");
	}


	public function process($form)
	{
		$values = $form->values;
		$userData = $this->userModel->item($this->user->id);
		$oldPass = UserManager::calculateHash($values["oldPassword"], $userData["salt"]);

		if ($oldPass != $userData["password"]) {
			$this->presenter->flashMessage("Zadali jste chybně staré heslo.", "error");

		} else {
			$data["password"] = UserManager::calculateHash($values["password"], $userData["salt"]);
			$this->userModel->update($data, $this->user->id);
			$this->presenter->flashMessage("Heslo bylo úspěšně změněno.", "success");
		}

		$this->presenter->redirect("this");
	}

}
