<?php

namespace Components;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Control;
use Schmutzka\Application\UI\Form;
use Schmutzka\Mail\Message;
use Schmutzka\Security\UserManager;

class RegistrationControl extends Control
{
	/** @var string */
	public $onAuthorizeRedirect = "this";

	/** @var subject */
	public $from;

	/** @var string */
	public $loginAfter = "email";

	/** @var bool */
	public $detectLang = FALSE;

	/** @var bool */
	public $requireAuthorization = FALSE;

	/** @var bool */
	public $confirmationEmail = TRUE;

	/** @inject @var Nette\Mail\IMailer */
	public $mailer;

	/** @inject @var Schmutzka\Models\User  */
	public $userModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\ParamService */
	public $paramService;


	protected function createComponentForm()
	{
		$userModel = $this->userModel;

		$form = new Form;
		$form->addText("login", $this->paramService->form->login->label)
			->addRule(Form::FILLED, $this->paramService->form->login->ruleFilled)
			->addRule(function ($input) use ($userModel) {
				return ! $userModel->item(array("login" => $input->value));
			}, $this->paramService->form->login->alreadyExists);

		$form->addText("email", $this->paramService->form->email->label)
			->addRule(Form::FILLED, $this->paramService->form->email->ruleFilled)
			->addRule(Form::EMAIL, $this->paramService->form->email->ruleFormat)
			->addRule(function ($input) use ($userModel) {
				return ! $userModel->item(array("email" => $input->value));
			}, $this->paramService->form->email->alreadyExists);

		$form->addPassword("password", $this->paramService->form->password->label)
			->addRule(Form::FILLED, $this->paramService->form->password->ruleFilled)
			->addRule(Form::MIN_LENGTH, $this->paramService->form->password->length, 5);

		$form->addSubmit("send", $this->paramService->form->send->register)
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function processForm(Form $form)
	{
		$rawValues = $values = $form->getValues();
		unset($values["conditions"]);

		$values["salt"] = Strings::random(22);
		$values["password"] = UserManager::calculateHash($values["password"], $values["salt"]);
		$values["created"] = new Nette\DateTime;

		if ($this->requireAuthorization) {
			$values["auth_hash"] = substr(sha1(time() . $values["email"]), -10);
		}

		if ($this->detectLang) {
			$values["lang"] = $this->translator->getLang();
		}

		$this->userModel->insert($values);
		$values = $rawValues + $values;

		// what to do now?
		if ($this->requireAuthorization) {
			$values["auth_hash"] = substr(sha1(time() . $values["email"]), -10);
			$this->sendAuthorizationEmail($values);
		}

		if ($this->loginAfter) {
			$this->user->login($values[$this->loginAfter], $rawValues["password"]);
			$this->presenter->flashMessage($this->paramService->registration->onSuccessAndLogin, "success");

		} else {
			$this->presenter->flashMessage($this->paramService->registration->onSuccess, "success");
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
		$values["auth_url"] = $this->link("//AuthorizeUser!", array(
			"hash" => $values["auth_hash"]
		));

		$message = new Message;
		$message->setFrom($this->from);
		$message->addTo($values["email"]);

		$template = $this->mailer->getCustomTemplate("AUTHORIZE_ACCOUNT", $values, TRUE);
		$message->setSubject($template["subject"]);
		$message->setHtmlBody($template["body"]);

		$this->mailer->send($message);
	}


	/**
	 * @param string
	 */
	public function handleAuthorizeUser($hash)
	{
		if ($user = $this->userModel->item(array(
			"auth_hash" => $hash
		))) {
			$array = array(
				"auth" => 1,
				"auth_hash" => NULL
			);
			$this->userModel->update($array, $user["id"]);

			if ($this->confirmationEmail) {
				$this->sendSuccessEmail($user);
			}

			$this->presenter->flashMessage($this->paramService->registration->onAuthSuccess, "success");

		} else {
			$this->presenter->flashMessage($this->paramService->registration->onAuthError, "error");
		}

		$this->presenter->redirect($this->onAuthorizeRedirect);
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


	public function render()
	{
		parent::useTemplate();
		$this->template->render();
	}

}
