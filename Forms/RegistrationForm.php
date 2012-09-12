<?php

namespace Schmutzka\Forms;

use Nette\Mail\Message;

class RegistrationForm extends Form
{
	/** @var \Nette\DI\Container */
	private $context;

	/** @var dir */
	private $appDir;

	/** @var string */
	public $company = "OurCompany";

	/** @var string */
	public $mailFrom = "no-reply@ourCompany.com";

	/** @var emailPath */
	public $mailTemplatePath = "templates/emails/informUser.latte";

	/** @var subject */
	public $subject = "Registrační údaje";

	/** @var bool */
	public $informUser = TRUE;

	/** @var string */
	public $loginAfter = "email";

	/** @var int */
	public $passwordMinLenth = 6;


	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $context)
	{
		parent::__construct();
		$this->context = $context;
	}


	public function build()
	{
		parent::build();

		$this->addText("login","Váš login:")
			->addRule(Form::FILLED,"Zadejte login")
			->addRule(callback($this, "isLoginAvailable"), "Zadaný login již existuje.");
		$this->addEmail("email","Váš email:")
			->addRule(Form::FILLED,"Vyplňte email")
			->addRule(callback($this, "isEmailAvailable"), "Zadaný email již existuje.");

		$this->addPassword("password","Heslo:")
			->addRule(Form::FILLED,"Zadejte heslo")
			->addRule(Form::MIN_LENGTH,"Heslo musí mít aspoň %d znaků.", $this->passwordMinLenth);

		$this->addPassword("password2","Heslo znovu:",20)
			->addRule(Form::FILLED,"Povinné")
			->addRule(Form::EQUAL,"Hesla se neshodují. Zadejte je znovu, prosím.", $this["password"]);

		$this->addSubmit("send","Registrovat");
	}


	/**
	 * Process form
	 * @form
	 */
	public function process(Form $form)
	{
		$rawValues = $values = $form->values;

		unset($values["password2"]);
		$values["password"] = sha1($values["password"]);

		$this->userTable->insert($values);

		if ($this->informUser) {
			$this->informUser($rawValues);		
		}

		if ($this->loginAfter) {
			$this->context->user->login($values[$this->loginAfter], $rawValues["password"], $this->loginAfter); // login user
			$this->flashMessage("Byli jste úspěšně registrováni a přihlášeni.", "flash-success");
		}
		else {
			$this->flashMessage("Byli jste úspěšně registrováni.", "flash-success");
		}

		$this->redirect("this");
	}



	/**
	 * Inform about account creation
	 */
	private function informUser($values)
	{
		$mail = new Message;
		$mail->setFrom($this->mailFrom)
			->addTo($values["email"])
			->setSubject($this->company." | ".$this->subject);

		$template = $this->createTemplate(); 
		$template->setFile($this->appDir.$this->mailTemplatePath);

		$template->company = $this->company;
		if (isset($values["login"])) {
			$template->login = $values["login"];
		}
		if (isset($values["email"])) {
			$template->email = $values["email"];
		}

		$mail->setHtmlBody($template);
		$mail->send();
	}


	/**
	 * Check if email is free
	 * @param control
	 * @return bool
	 */
	public function isEmailAvailable($control)
	{	
		$email = $control->value;
		return $this->models->user->isFree(array("email", $email));
	}


	/**
	 * Check if login is free
	 * @param control
	 * @return bool
	 */
	public function isLoginAvailable($control)
	{	
		$login = $control->value;
		return $this->models->user->isFree(array("login", $login));
	}

}