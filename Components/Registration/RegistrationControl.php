<?php

namespace Components;

use Nette;
use Schmutzka;
use Schmutzka\Application\UI\Control;
use Schmutzka\Application\UI\Form;

class RegistrationControl extends Control
{
	/** @var string */
	public $from;

	/** @var string */
	public $loginAfter = TRUE;

	/** @var int */
	public $passwordMinLenth = 6;

	/** @var bool */
	public $detectLang = FALSE;

	/** @var bool */
	public $logDateCreated = TRUE;

	/** @var bool */
	public $requireAuthorization = FALSE;

	/** @var string */
	public $flashSuccess = "Byli jste úspěšně registrováni.";

	/** @var string */
	public $onAuthorizeRedirect = "this";

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Nette\Localization\ITranslator */
	public $translator;

	/** @inject @var Nette\Mail\IMailer */
	public $mailer;


	protected function createComponentRegistrationForm()
	{
		$form = new Form;

		$form->addText("login", "Váš login:")
			->addRule(Form::FILLED,"Zadejte login")
			->addRule(Form::PATTERN, "Login musí mít délku aspoň 5 znaků a smí obsahovat pouze znaky a-z, A-Z, 0-9, '_' a '-'.","[a-zA-Z0-9_-]{5,}")
			->addRule(function ($input) use ($userModel) {
				return ! $userModel->item(array("login" => $input->value));
			}, "Zadaný login již existuje.");

		$form->addText("email", "Váš email:")
			->addRule(Form::FILLED, "Vyplňte email")
			->addRule(Form::EMAIL, "Email nemá správný formát")
			->addRule(function ($input) use ($userModel) {
				return ! $userModel->item(array("email" => $input->value));
			}, "Zadaný email již existuje.");

		$form->addPassword("password", "Heslo:")
			->addRule(Form::FILLED, "Zadejte heslo")
			->addRule(Form::MIN_LENGTH, "Heslo musí mít aspoň délku %d znaků.", $this->passwordMinLenth);

		$form->addPassword("password2", "Heslo znovu:")
			->addRule(Form::FILLED, "Zadejte heslo znovu pro kontrolu")
			->addRule(Form::EQUAL, "Hesla se neshodují.", $form["password"]);

		$form->addSubmit("send", "Registrovat")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	/**
	 * Process registration form
	 * @param form
	 */
	public function processRegistrationForm(Form $form)
	{
		$rawValues = $values = $form->values;

		unset($values["password2"]);
		$values["password"] = sha1($values["password"]);
		$values["created"] = new Nette\DateTime;

		// set autorization hash
		if ($this->requireAuthorization) {
			$values["auth_hash"] = substr(sha1($values["email"]) . sha1(time()), 20, 40);
		}

		if ($this->detectLang) {
			$values["lang"] = $this->parent->presenter->lang;
		}

		$this->userModel->insert($values);
		$values = $rawValues + $values;

		// what to do now?
		if ($this->requireAuthorization) {
			$this->sendAuthorizationEmail($values);

		} elseif ($this->loginAfter) {
			$this->user->login($values[$this->loginAfter], $rawValues["password"], $this->loginAfter);
		}

		$this->presenter->flashMessage($this->flashSuccess, "success");
		$this->presenter->redirect("this");
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

		$this->getPresenter()->redirect($this->onAuthorizeRedirect);
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