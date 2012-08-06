<?php

namespace Components;

use Schmutzka\Application\UI\Control,
	Schmutzka\Forms\Form,
	Nette\Mail\Message;

class RemindPasswordControl extends Control
{
	/** @var \Nette\DI\Container  */
	private $context;

	/** @var Models\User */
	private $userModel;

	/** @var string */
	public $company = "OurCompany.com";

	/** @var string */
	public $from = "no-reply@ourCompany.com";

	/** @var string */
	public $emailTemplatePath = NULL;

	/** @var string */
	public $subject = "Žádost o připomenutí hesla";

	/** @var string */
	public $subjectStep2 = "Resetování hesla";

	/** @var bool */
	public $confirmFirst = FALSE;


	public function __construct(\Nette\DI\Container $context)
	{
		parent::__construct();
		$this->context = $context;
		$this->userModel = $this->context->models->user;
	}


	/**
	 * Remind password form
	 */
	protected function createComponentRemindPasswordForm()
	{
		$form = new Form;

		$form->addText("email","Váš email:")
			->addRule(Form::FILLED,"Vyplňte email.")
			->addRule(Form::EMAIL, "Email nemá správný formát.");
		$form->addSubmit("send","Zaslat nové heslo");

		$form->onSuccess[] = callback($this, "remindPasswordFormSent");

		return $form;
	}


	/**
	 * Process remind form
	 * @param form
	 */
	public function remindPasswordFormSent(Form $form)
	{
		// translate values
		$this->subject = $this->translate($this->subject);

		$values = $form->values;
		$record = $this->userModel->item(array("email" => $values["email"]));

		if ($record) {

			// #1 - perform changes
			if ($this->confirmFirst === FALSE) { // reset
				$password = "V".substr(uniqid(),6,7);
				$this->userModel->update(array("password" => sha1($password)), array("email" => $values["email"]));

			} else { // ask first
				$remindHash = sha1("V".substr(uniqid(),6,7));
				$this->userModel->update(array("remindHash" => sha1($remindHash)), array("email" => $values["email"]));
			}


			// #2 - create email with template
			$mail = new Message;
			$mail->setFrom($this->from)
				->addTo($values["email"])
				->setSubject($this->company." | ".$this->subject);

			if ($this->emailTemplatePath) { // ifset, use one
				$filePath = $this->context->parameters["appDir"] . "/" . $this->emailTemplatePath;

			}	else {
				$filePath = ($this->confirmFirst === FALSE ? __DIR__ . "/templates/resetPassword.latte" : __DIR__ . "/templates/remindPasswordRequest.latte");
			}
			$template = $this->createTemplate()->setFile($filePath);


			// #3 - pass params
			$template->company = $this->company;

			if ($this->confirmFirst === FALSE) { // reset
				$template->password = $password;
				$this->flashMessage("Nové heslo bylo nastaveno. Zkontrolujte Vaši emailovou schránku.","flash-success");
			}
			else { // ask first
				$template->remindHash = $remindHash;
				$this->flashMessage("Na Váš email byla odeslána zpráva k ověření.","flash-success");
			}


			// #4 - connect and send!
			$mail->setHtmlBody($template)
				->send();

		} else {
			$this->flashMessage("Tento uživatel neexistuje.","flash-error");
		}

		$this->redirect("this");
	}


	public function render()
	{
		$this->template->render();
	}


	/* **************************** "Confirm first" hash authorization **************************** */
 

	/**
	 * Authorize by hash
	 * @param string
	 */
	public function handleRemindPassword($hash)
	{
		// translate
		$this->subjectStep2 = $this->translate($this->subjectStep2);

		$record = $this->userModel->item(array("remindHash" => $hash));


		if ($record) {

			// #1 - perform changes
			$newPassword = substr(sha1(time()),0,6);
			$array = array(
				"password" => sha1($newPassword),
				"remindHash" => NULL
			);
			$record->update($array);


			// #2 - create email with template
			$mail = new Message;
			$mail->setFrom($this->from)
				->addTo($record["email"])
				->setSubject($this->company." | ".$this->subject);
			
			if ($this->emailTemplatePath) { // ifset, use one
				$filePath = $this->context->parameters["appDir"] . "/" . $this->emailTemplatePath;

			}	else {
				$filePath = (__DIR__ . "/templates/resetPassword.latte");
			}

			$template = $this->parent->createTemplate()->setFile($filePath);


			// #3 - pass params, connect and send!
			$template->company = $this->company;
			$template->password = $newPassword;

			$mail->setHtmlBody($template);
			$mail->send();

			$this->flashMessage("Nové heslo bylo nastaveno. Zkontrolujte Vaši emailovou schránku.","flash-success");

		} else {
			$this->flashMessage("Tato žádost již není platná.","flash-error");
		}

		$this->redirect("this");
	}
	
}