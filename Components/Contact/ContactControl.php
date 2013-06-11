<?php

namespace Components;

use Nette;
use Nette\Mail\Message;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Control;

/**
 * @method setSubjectText(bool)
 * @method getSubjectText()
 * @method setIncludeParams(bool)
 * @method getIncludeParams()
 * @method setShowEmail(bool)
 * @method getShowEmail()
 * @method setMailTo(string|array)
 * @method getMailTo()
 * @method setLogSender(array)
 * @method getLogSender()
 */
class ContactControl extends Control
{
	/** @inject @var Nette\Http\Request */
	public $httpRequest;

	/** @inject @var Schmutzka\Security\User*/
	public $user;

	/** @inject @var Nette\Mail\IMailer */
	public $mailer;

	/** @var array */
	private $logSender = array("login");

	/** @var string|array */
	private $mailTo;

	/** @var bool */
	private $showEmail = TRUE;

	/** @var string */
	private $subjectText = "Kontaktní formulář";

	/** @var bool */
	private $includeParams = FALSE;


	protected function createComponentForm()
	{
		$form = new Form;

		if ($this->showEmail) {
			$form->addText("email", "Váš email:")
				->addRule(Form::FILLED, "Zadejte Váš email")
				->addRule(Form::EMAIL, "Email nemá správný formát");
		}

		$form->addTextarea("text", "Zpráva:")
			->addRule(Form::FILLED, "Napište Váš dotaz");

		$form->addAntispam();
		$form->addSubmit("send", "Odeslat")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;
		$domain = $this->httpRequest->url->host;

		$text = "Dobrý den,\n\nze stránky " .
			$domain .
			" Vám byla zaslána následující zpráva:\n\n" .
			$values["text"];

		if ($this->includeParams) {
			$text .= "\n\nVeškeré parametry:\n";
			foreach ($form->components as $key => $component) {
				if ($key != "submit") {
					$text .= $component->caption . " " . $component->value . "\n";
				}
			}
		}

		/*
		if (!isset($values["email"])) {
			$values["email"] = "no-reply@" . $domain;
		}
		*/

		$message = new Message;
		$message->setFrom($values["email"])
			->setSubject(rtrim($domain . " - " . $this->subjectText, " - "));

		$from = "";
		if ($this->logSender) {
			if ($this->user->loggedIn) {
				$name = "";
				foreach ($this->logSender as $key) {
					if (isset($this->user->identity->{$key})) {
						$name .= $this->user->identity->{$key} . " ";
					}
				}

				$message->setFrom($this->user->email, trim($name));
				$from = "Od: " . trim($name) . ", ". $this->user->email . "\n\n";

			} else {
				$key = array_shift($this->logSender);
				$email = $values[$key];
				$from = "Od: " . $email;

				if ($key = array_shift($this->logSender)) {
					$name = trim($values[$key]);
					$from .= ", " . $name;
				}

				$from .= "\n\n";
				$message->setFrom($email, $name);
			}
		}

		if (is_array($this->mailTo)) {
			foreach ($this->mailTo as $value) {
				$message->addTo($value);
			}

		} else {
			$message->addTo($this->mailTo);
		}

		$message->setBody($from . $text);
		$this->mailer->send($message);

		$this->presenter->flashMessage("Zpráva byla úspěšně odeslána.", "success");
		$this->presenter->redirect("this");
	}


	public function render()
	{
		parent::useTemplate();
		$this->template->render();
	}

}
