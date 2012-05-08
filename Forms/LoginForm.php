<?php

namespace Schmutzka\Forms;

class LoginForm extends Form
{

	/** @var \Nette\Security\User */	
	private $user;

	/** @var \Nette\Http\SessionSection */
	private $appSession;
	
	/** @var bool */
	private $permalogin = FALSE;


	/** @var string */
	public $flashContent = "Byli jste úspěšně přihlášeni.";

	/** @var array */
	public $onLoginSuccess = array();

	/** @var array */
	public $onLoginError = array();


	public function __construct(\Nette\Security\User $user, \Nette\Http\Session $session)
	{
		parent::__construct();
		$this->user = $user;
		$this->appSession = $session->getSection("appSession");
	}





	public function build()
	{
		parent::build();

		$this->addText("login","Přihlašovací jméno:",15)
			->addRule(Form::FILLED,"Zadejte přihlašovací jméno");

		$this->addPassword("password","Přihlašovací heslo:",15)
			->addRule(Form::FILLED,"Zadejte heslo");

		if ($this->permalogin) {
			$this->addCheckbox("permalogin", "Zapamatovat")
				->setDefaultValue(TRUE);
		}

		$this->addSubmit("send","Přihlásit se");

	}
	

	public function process(LoginForm $form)
	{
		try {
			$values = $form->values;

			if ($this->permalogin AND $values["permalogin"]) {
				$this->user->setExpiration("+ 14 days", FALSE);
			}
			else {
				$this->user->setExpiration("+ 6 hours", TRUE);
			}

			$this->user->login($values["login"], $values["password"]); // this will call Schmutzka\Security\Authenticator.php - check it's code

			if ($this->onLoginSuccess) {
				$this->onLoginSuccess($this->user);
			}

			$this->flashMessage($this->flashContent);
			$this->presenter->restoreRequest($this->appSession->backlink);
			$this->redirect("Homepage:default");
		} catch (\Nette\Security\AuthenticationException $e) { // incorrect user/password

			if ($this->onLoginError) {
				$this->onLoginError($values);
			}

			$this->flashMessage($e->getMessage(),"flash-error"); 
		}
	}


	/**
	 * Permalogin setter
	 */
	public function enablePermalogin()
	{
		$this->permalogin = TRUE;
	}

}