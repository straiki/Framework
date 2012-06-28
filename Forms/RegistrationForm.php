<?php

namespace Schmutzka\Forms;

use Nette\Mail\Message;

class RegistrationForm extends Form
{
	/** @var \SystemContainer */
	private $context;

	/** @var \NotORM_Result */
	private $userTable;

	/** @var dir */
	private $appDir;


	/** @var int */
	public $passwordMinLength = 6;

	/** @var string */
	public $mailCompany = "OurCompany";

	/** @var string */
	public $mailFrom = "no-reply@ourCompany.com";

	/** @var emailPath */
	public $mailTemplatePath = "templates/emails/informUser.latte";

	/** @var subject */
	public $mailSubject = "Registrační údaje";

	/** @var Callback */
	public $onProcessValues;

	/** @var bool */
	public $informUser;

	/** @var string - experimental */
	public $loginAfter = NULL;



	/**
	* @param \Container
	 * @param int
	 * @param bool
	 * @param string
	 */
	public function __construct(\SystemContainer $context)
	{
		parent::__construct();

		$this->context = $context;
		$this->userTable = $context->database->user;
		$this->appDir = $context->params["appDir"]."/";
	}


	public function build()
	{
		parent::build();

		$this->addText("login","Login:")
			->addRule(Form::FILLED,"Mandatory");
		$this->addEmail("email","Your email:")
			->addRule(Form::FILLED,"Mandatory");

		$this->addPassword("password","Password:")
			->addRule(Form::FILLED,"Mandatory")
			->addRule(Form::MIN_LENGTH,"Password has to be min %d chars long.", $this->passwordMinLength);

		$this->addPassword("password2","Password again:",20)
			->addRule(Form::FILLED,"Mandatory")
			->addRule(Form::EQUAL,"Passwords don't match.", $this["password"]);


		$this->addSubmit("send","Register");
	}


	/**
	 * Process form
	 * @form
	 */
	public function process(Form $form)
	{
		$values = $form->values;

		// 1. item's uniqueness
		if (isset($values["email"])) {
			$emailCheck = $this->userTable->where("email", $values["email"])->count("*");
			if($emailCheck) {
				$this->flashMessage("This email is already used.","flash-error");
				$this->redirect("this");
			}
		}

		if (isset($values["login"])) {
			$loginCheck = $this->userTable->where("login", $values["login"])->count("*");
			if($loginCheck) {
				$this->flashMessage("This email is already used.","flash-error");
				$this->redirect("this");
			}
		}

		// everything ok
		$rawValues = $values;

		unset($values["password2"]);
		$values["password"] = sha1($values["password"]);


		if ($this->onProcessValues) {
			$values = $this->onProcessValues->invokeArgs(array($values));
		}
		else {
			$this->userTable->insert($values);
		}

		// email user
		if ($this->informUser) {
			$this->informUser($rawValues);		
		}

		// login after
		if($this->loginAfter) {
			$this->context->user->login($values[$this->loginAfter], $rawValues["password"], $this->loginAfter); // login user
			$this->flashMessage("You were successfully registred and logged in.","flash-success");
		}
		else {
			$this->flashMessage("You were successfully registred.","flash-success");
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
			->setSubject($this->mailCompany." | ".$this->mailSubject);

		$template = $this->presenter->createTemplate(); 
		$template->setFile($this->appDir.$this->mailTemplatePath);

		// values
		$template->company = $this->mailCompany;
		if(isset($values["login"])) {
			$template->login = $values["login"];
		}
		if(isset($values["email"])) {
			$template->email = $values["email"];
		}

		$mail->setHtmlBody($template);
		$mail->send();
	}
}