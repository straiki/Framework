<?php

namespace Schmutzka\Forms;

use Nette;
use Schmutzka\Mail\Message;

class ContactForm extends Form
{
	/** @var string */
	public $mailTo;

	/** @var string */
	public $siteFrom;

	/** @var bool */
	public $showEmail = TRUE;

	/** @var bool */
	public $showName = TRUE;

	/** @var string */
	public $flashText = "Zpráva byla úspěšně odeslána.";

	/** @var string */
	public $subjectText = "Kontaktní formulář";

	/** @var bool */
	public $includeParams = FALSE;

	/** @var string */
	public $redirectTo = "this";

	/** @var array */
	public $logSender = array("login");

	/** @var Nette\Security\User*/
	private $user;

	/** @var Nette\Mail\IMailer */
	private $mailer;


	/**
	 * @param Nette\Security\User
	 * @param Nette\Mail\IMailer
	 */
	public function __construct(Nette\Security\User $user, Nette\Mail\IMailer $mailer)
	{
		parent::__construct();
		$this->user = $user;
		$this->mailer = $mailer;
	}


	public function build()
	{
		if ($this->showName) {
			$this->addText("name", "Vaše jméno:")
				->addRule(Form::FILLED, "Zadejte Vaše jméno");
		}

		if ($this->showEmail) {
			$this->addText("email", "Váš email:")
				->addRule(Form::FILLED, "Zadejte Váš email")
				->addRule(Form::EMAIL, "Email nemá správný formát");
		}

		$this->addTextarea("text", "Zpráva:")
			->addRule(Form::FILLED, "Napište Váš dotaz");

		$this->addAntispam();
		$this->addSubmit("submit", "Odeslat");
	}
	

	public function process($form)
	{
		$values = $form->values;
		unset($values["antispam"]);

		// message
		$message = "Dobrý den,\n\nze stránky ".$this->siteFrom." Vám byla zaslána následující zpráva:\n\n"
			. $values["text"];

		// all params
		if ($this->includeParams) {
			$message .= "\n\nVeškeré parametry:\n";

			foreach ($form->components as $key => $component) {	
				if ($key != "submit") {
					$message .= $component->caption . " " . $component->value . "\n";
				}
			}
		}

		if (!isset($values["email"])) {
			$values["email"] = "no-reply@" . $this->siteFrom;
		}

		$subject = rtrim($this->siteFrom.  " - ". $this->subjectText, " - ");

		$message = new Message;
		$message->setFrom($values["email"])
			// ->setBody($message)
			->setSubject($subject);

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
		}
		else {
			$message->addTo($this->mailTo);
		}

		$message->setBody($from . $values["message"]);
		$this->mailer->send($message);
	
		$this->getPresenter()->flashMessage($this->flashText, "success");
		$this->getPresenter()->redirect($this->redirectTo);
	}

}
