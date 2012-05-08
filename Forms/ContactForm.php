<?php

namespace Schmutzka\Forms;

use Nette\Mail\Message;

class ContactForm extends Form
{
	/** @var string */
	private $mailTo;

	/** @var string */
	private $siteFrom;

	/** @var bool */
	public $showEmail = TRUE;

	/** @var bool */
	public $showPhone = FALSE;

	/** @var bool */
	public $showName = TRUE;

	/** @var bool */
	public $showText = TRUE;

	/** @var bool */
	public $textRequired = TRUE;

	/** @var string */
	public $flashText = "Zpráva byla úspěšně odeslána.";

	/** @var string */
	public $subjectText = "Kontaktní formulář";

	/** @var bool */
	public $includeParams = FALSE;

	/** @var string */
	public $redirectTo = "this";


	public function __construct($siteFrom, $mailTo)
	{
		parent::__construct();
		$this->siteFrom = $siteFrom;
		$this->mailTo = $mailTo;
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

		if ($this->showPhone) {
			$this->addText("phone", "Váš telefon:")
				->addCondition(Form::FILLED)
					->addRule(Form::INTEGER, "Můžete zadat použít pouze čísla")
					->addRule(Form::MIN_LENGTH, "Číslo musí mít minimálně %d znaků.", 9);
		}

		if ($this->showText) {
			$this->addTextarea("text", "Zpráva:");
			if($this->textRequired) {
				$this["text"]->addRule(Form::FILLED, "Napište Váš dotaz");
			}
		}

		$this->addAntispam();

		$this->addSubmit("submit", "Odeslat");
	}
	

	public function process(ContactForm $form)
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
				if($key != "submit") {
					$message .= $component->caption . " " . $component->value . "\n";
				}
			}
		}

		if (!isset($values["email"])) {
			$values["email"] = "no-reply@" . $this->siteFrom;
		}

		$subject = rtrim($this->siteFrom.  " - ". $this->subjectText, " - ");

		$mail = new Message;
		$mail->setFrom($values["email"])
			->setBody($message)
			->setSubject($subject);


		if (is_array($this->mailTo)) {
			foreach ($this->mailTo as $value) {
				$mail->addTo($value);
			}
		}
		else {
			$mail->addTo($this->mailTo);
		}

		$mail->send();

		$this->flashMessage($this->flashText, "flash-success");
		$this->redirect($this->redirectTo);
	}

}