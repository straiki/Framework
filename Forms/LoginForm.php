<?php

namespace Schmutzka\Forms;

use Nette;
use Schmutzka;

class LoginForm extends Form
{
	/** @var string */
	public $flashContent = "Byli jste úspěšně přihlášeni.";

	/** @var string */
	public $loginColumn = "email";

	/** @var array */
	public $onLoginSuccess = array();

	/** @var bool */
	public $permalogin = FALSE;

	/** @var array */
	public $onLoginError = array();

	/** @var Schmutzka\Security\User */	
	private $user;

	/** @var Nette\Http\SessionSection */
	private $mySession;



	final function inject(Schmutzka\Security\User $user, Nette\Http\Session $session)
	{
		$this->user = $user;
		$this->mySession = $session->getSection("mySession");
	}


	public function build()
	{
		parent::build();

		if ($this->loginColumn == "login") {
			$this->addText("login","Přihlašovací jméno:")
				->addRule(Form::FILLED,"Zadejte přihlašovací jméno")
				->addRule(~Form::EMAIL, "Login nemá správný formát");

		} elseif ($this->loginColumn == "email") {
			$this->addText("login","Přihlašovací email:")
				->addRule(Form::FILLED,"Zadejte přihlašovací email")
				->addRule(Form::EMAIL, "Email nemá správný formát");
		}

		$this->addPassword("password","Přihlašovací heslo:")
			->addRule(Form::FILLED,"Zadejte heslo");

		if ($this->permalogin) {
			$this->addCheckbox("permalogin", "Zapamatovat")
				->setDefaultValue(TRUE);
		}

		$this->addSubmit("send","Přihlásit se");

	}
	

	public function process($form)
	{
		try {
			$values = $form->values;

			if ($this->permalogin && $values["permalogin"]) {
				$this->user->setExpiration("+ 14 days", FALSE);

			} else {
				$this->user->setExpiration("+ 6 hours", TRUE);
			}

			$this->user->login($values["login"], $values["password"]);

			if ($this->onLoginSuccess) {
				$this->onLoginSuccess($this->user);
			}

			$this->presenter->flashMessage($this->flashContent, "success");
			$this->presenter->restoreRequest($this->mySession->backlink);
			$this->presenter->redirect("Homepage:default");

		} catch (\Nette\Security\AuthenticationException $e) {

			if ($this->onLoginError) {
				$this->onLoginError($values);
			}

			$this->presenter->flashMessage($e->getMessage(), "error"); 
		}
	}

}
