<?php

namespace Schmutzka\Forms;

use 
	Nette\Mail\Message;

/**
 * Registration form
 */
class RegistrationForm extends Form
{

	/** @var context */
	private $context;

	/** @var \NotORM_Result */
	private $userTable;

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
	private $informUser;

	/** @var bool - experimental */
	private $loginAfter = NULL;


	/**
	 * Build the form
	 * @param \Container
	 * @param int
	 * @param bool
	 * @param string
	 */
	public function __construct(\SystemContainer $context, $passwordMinLenth = 6, $informUser = TRUE, $loginAfter = "email")
	{
		parent::__construct();

		$this->context = $context;
		$this->userTable = $context->database->user;
		$this->appDir = $context->parameters["appDir"]."/";

		$this->loginAfter = $loginAfter;
		$this->informUser = $informUser;

		$this->addText("login","Váš login:")
			->addRule(Form::FILLED,"Zadejte login");
		$this->addEmail("email","Váš email:")
			->addRule(Form::FILLED,"Vyplňte email");

		$this->addPassword("password","Heslo:")
			->addRule(Form::FILLED,"Zadejte heslo")
			->addRule(Form::MIN_LENGTH,"Heslo musí mít aspoň %d znaků.", $passwordMinLenth);

		$this->addPassword("password2","Heslo znovu:",20)
			->addRule(Form::FILLED,"Povinné")
			->addRule(Form::EQUAL,"Hesla se neshodují. Zadejte je znovu, prosím.", $this["password"]);


		$this->addSubmit("send","Registrovat");
		$this->onSuccess[] = callback($this, "process");
	}


	/**
	 * Process form
	 * @form
	 */
	public function process(Form $form)
	{
		$values = $form->values;

		// 1. item's uniqueness
		if(isset($values["email"])) {
			$emailCheck = $this->userTable->where("email", $values["email"])->count("*");
			if($emailCheck) {
				$this->presenter->flashMessage("Tento email je již použit.","flash-error");
				$this->presenter->redirect("this");
			}
		}
		elseif(isset($values["login"])) {
			$loginCheck = $this->userTable->where("login", $values["login"])->count("*");
			if($loginCheck) {
				$this->presenter->flashMessage("Tento login je již použit.","flash-error");
				$this->presenter->redirect("this");
			}
		}

		// everything ok
		$rawValues = $values;

		unset($values["password2"]);
		$values["password"] = sha1($values["password"]);

		$this->userTable->insert($values);

		// email user
		if($this->informUser) {
			$this->informUser($rawValues);		
		}

		// login after
		if($this->loginAfter) {
			$this->context->user->login($values[$this->loginAfter], $rawValues["password"], $this->loginAfter); // login user
			$this->presenter->flashMessage("Byli jste úspěšně registrováni a přihlášeni.","flash-success");
		}
		else {
			$this->presenter->flashMessage("Byli jste úspěšně registrováni.","flash-success");
		}

		$this->presenter->redirect("this");
	}



	/** #1
	 * Inform about account creation
	 */
	private function informUser($values)
	{
		$mail = new Message;
		$mail->setFrom($this->mailFrom)
			->addTo($values["email"])
			->setSubject($this->company." | ".$this->subject);

		$template = $this->presenter->createTemplate(); 
		$template->setFile($this->appDir.$this->mailTemplatePath);

		// values
		$template->company = $this->company;
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