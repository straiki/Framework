<?php

namespace Schmutzka\Forms;

use Nette\Mail\Message;

/**
 * Remind password form
 */
class RemindPasswordForm extends Form
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
	public $mailTemplatePath = "emails/remindPasswordRequest.latte";

	/** @var subject */
	public $subject = "Žádost o připomenutí hesla";

	/** @var type, 1 => reset, (2DO) 2 => remind + reset + make public */
	private $type = 1;


	/**
	 * Build the form
	 */
	public function __construct(\SystemContainer $context)
	{
		parent::__construct();

		$this->context = $context;
		$this->userTable = $context->database->user;
		$this->appDir = $context->parameters["appDir"]."/";

		$this->addText("email","Váš email:",25,60)
			->setType("email")
			->addRule(Form::FILLED,"Vyplňte email")
			->addRule(Form::EMAIL,"Zadejte správný formát emailu");
		$this->addSubmit("send","Zaslat nové heslo");
		$this->onSuccess[] = callback($this, "process");
	}


	/**
	 * Process form
	 * @form
	 */
	public function process(Form $form)
	{
		$values = $form->values;
		$record = $this->userTable->where("email", $values->email);

		if($record->count("*")) { // user exists

			if($this->type == 1) { // reset

				$password = "V".substr(uniqid(),6,7);
				$record->update(array(
					"password" => sha1($password) // set new password
				));

				// email the user
				$mail = new Message;
				$mail->setFrom($this->mailFrom)
					->addTo($values->email)
					->setSubject($this->company." | ".$this->subject);
		
				$template = new \Nette\Templating\FileTemplate($this->appDir.$this->mailTemplatePath);
				$template->registerFilter(new \Nette\Latte\Engine);
				$template->password = $password;
				$template->company = $this->company;
				$mail->setHtmlBody($template);
				$mail->send();

				$this->getPresenter()->flashMessage("Nové heslo bylo nastaveno. Zkontrolujte Vaši emailovou schránku.","pos");
				$this->getPresenter()->redirect("this");

			}
			/*
			elseif($type == 2) { // set remind hash...

			$remindHash = sha1(sha1($values["email"]).time());
			$record->update(array( 
				"remindHash" => $remindHash // set remind hash
			));

			// email the user
			$mail = new Message;
			$mail
				->setFrom($this->mailFrom)
				->addTo($values->email)
				->setSubject($this->company." | ".$this->subject);
	
			$template = new \Nette\Templating\FileTemplate($this->appDir.$this->mailTemplatePath);
			$template->registerFilter(new \Nette\Latte\Engine);
			$template->hash = $remindHash;
			$mail->setHtmlBody($template);
			$mail->send();
			*/
		}
		else { // user doesn't exists
			$this->getPresenter()->flashMessage("Tento email není registrovaný.","neg");
			$this->getPresenter()->redirect("this");
		}
	}
	
}