<?php

namespace Components;

use Nette;
use Schmutzka;
use Schmutzka\Mail\Message;
use Schmutzka\Application\UI\Control;
use Schmutzka\Forms\Form;
use Schmutzka\Utils\Password;

class RegistrationControl extends Control
{
	/** @var string */
	public $authorizeRedirect = "this";

	/** @var subject */
	public $from;

	/** @var string */
	public $flashSuccess = "Registrace proběhla úspěšně.";

	/** @var string */
	public $loginAfter = TRUE; // use autologin?

	/** @var bool */
	public $detectLang = FALSE;

	/** @var bool */
	public $requireAuthorization = FALSE;

	/** @var bool */
	public $confirmationEmail = TRUE;

	/** @var Nette\Mail\IMailer */
	private $mailer;

	/** @var Schmutzka\Models\User  */
	private $userModel;

	/** @var Schmutzka\Security\User */
	private $user;

	/** @var Schmutzka\Config\ParamService */
	private $paramService;


	public function __construct(Schmutzka\Security\User $user, Schmutzka\Models\User $userModel, Schmutzka\Config\ParamService $paramService, Nette\Application\Application $application, Nette\Mail\IMailer $mailer)
	{
		$this->userModel = $userModel;
		$this->user = $user;
		$this->mailer = $mailer;
		$this->paramService = $paramService;

		parent::__construct($application->presenter, "registrationControl");
	}


	/**
	 * Registration form
	 */
	protected function createComponentRegistrationForm()
	{
		$form = new Form;

		$form->addText("login", "Váš login:")
			->addRule(Form::FILLED,"Zadejte login")
			->addRule(Form::PATTERN, "Login musí mít délku aspoň 5 znaků a smí obsahovat pouze znaky a-z, A-Z, 0-9, '_' a '-'.","[a-zA-Z0-9_-]{5,}")
			->addRule(function ($input) {
				return ! $this->userModel->item(array("login" => $input->value));
			}, "Zadaný login již existuje.");


		$form->addText("email", "Váš email:")
			->addRule(Form::FILLED, "Vyplňte email")
			->addRule(Form::EMAIL, "Email nemá správný formát")
			->addRule(function ($input) {
				return ! $this->userModel->item(array("email" => $input->value));
			}, "Zadaný email již existuje.");

		$form->addPassword("password", "Heslo:")
			->addRule(Form::FILLED,"Zadejte heslo")
			->addRule(Form::MIN_LENGTH,"Heslo musí mít aspoň %d znaků.", 6);

		$form->addPassword("password2", "Heslo znovu:")
			->addRule(Form::FILLED,"Povinné")
			->addRule(Form::EQUAL,"Hesla se neshodují.", $form["password"]);

		$form->addSubmit("send","Registrovat");

		return $form;
	}


	/**
	 * Process form
	 * @param Form
	 */
	public function processRegistrationForm(Form $form)
	{
		$rawValues = $values = $form->getValues();
		unset($values["conditions"], $values["password2"]);

		$values["password"] = Password::saltHash($values["password"], isset($this->paramService->salt) ? $this->paramService->salt : NULL);
		$values["created"] = new Nette\DateTime;

		/*if ($this->requireAuthorization) {
			$values["auth_hash"] = substr(sha1(time() . $values["email"]), -10);
		}*/

		if ($this->detectLang) {
			$values["lang"] = $this->translator->getLang();
		}

		// $this->userModel->insert($values);
		$values = $rawValues + $values;

		// what to do now?
		if ($this->requireAuthorization) {
			$values["auth_hash"] = substr(sha1(time() . $values["email"]), -10);
			$this->sendAuthorizationEmail($values);		
		}

		if ($this->loginAfter) {
			$this->user->login($values[$this->loginAfter], $rawValues["password"], $this->loginAfter);
			$this->getPresenter()->flashMessage("Byli jste úspěšně registrováni a přihlášeni.", "success");

		} else {
			$this->getPresenter()->flashMessage($this->flashSuccess, "success");
		}

		$this->redirect("this");
	}


	/* **************************** Authorization required **************************** */


	/**
	 * Send autorization email
	 * @param array
	 * @use short-route:

		$frontRouter[] = new Route("authorize/<registrationControl-hash>", array(
			"presenter" => "Homepage",
			"action" => "registration",
			"do" => "registrationControl-AuthorizeUser"
		));

	 */
	private function sendAuthorizationEmail($values)
	{
		$values["auth_url"] = $this->link("//AuthorizeUser!", array("hash" => $values["auth_hash"]));

		$message = new Nette\Mail\Message;
		$message->setFrom($this->from);
		$message->addTo($values["email"]);

		$template = $this->mailer->getCustomTemplate("AUTHORIZE_ACCOUNT", $values, TRUE);
		$message->setSubject($template["subject"]);
		$message->setHtmlBody($template["body"]);

		$this->mailer->send($message);
	}


	/**
	 * Authorization
	 * @param string
	 */
	public function handleAuthorizeUser($hash)
	{
		if ($user = $this->userModel->item(array("auth_hash" => $hash))) {
			$array = array(
				"auth" => 1,
				"auth_hash" => NULL
			);
			$this->userModel->update($array, $user["id"]);

			if ($this->confirmationEmail) {
				$this->sendSuccessEmail($user);
			}

			$this->getPresenter()->flashMessage("Váš účet byl aktivován. Nyní se můžete přihlásit.", "success");

		} else {
			$this->getPresenter()->flashMessage("Tento odkaz již není platný.", "error");
		}

		$this->getPresenter()->redirect($this->authorizeRedirect);
	}


	/**
	 * Send autorization email
	 * @param array
	 */
	private function sendSuccessEmail($values)
	{
		$message = new Nette\Mail\Message;
		$message->setFrom($this->from);
		$message->addTo($values["email"]);

		$template = $this->mailer->getCustomTemplate("REGISTRATION_SUCESSFULL", $values, TRUE);
		$message->setSubject($template["subject"]);
		$message->setHtmlBody($template["body"]);

		$this->mailer->send($message);
	}

}
