<?php

namespace Components;

use Schmutzka\Application\UI\Control;
use Schmutzka\Application\UI\Form;
use Nette\Mail\Message;

class RemindPasswordControl extends Control
{
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

	/** @inject @var Models\User */
	public $userModel;

	/** @inject @var Nette\Mail\Mailer */
	public $mailer;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;


	/**
	 * Remind password form
	 */
	protected function createComponentRemindPasswordForm()
	{
		$form = new Form;
		$form->addText("email","Váš email:")
			->addRule(Form::FILLED, "Vyplňte email.")
			->addRule(Form::EMAIL, "Opravte formát emailu.");
		$form->addSubmit("send","Zaslat nové heslo");

		return $form;
	}


	/**
	 * Process remind form
	 */
	public function remindPasswordFormSent(Form $form)
	{
		$values = $form->values;
		$this->subject = $this->translate($this->subject);

		if ($record = $this->userModel->item(array("email" => $values["email"]))) {

			$message = new Message;
			$message->setFrom($this->from)
				->addTo($values["email"]);

			if ($this->confirmFirst) { // A. send confirm first
				$remind = Strings::random(6);
				$remindHashed = Password::saltHash($remind, $this->salt);
				$values["remind"] = $remind;

				$this->userModel->update(array(
					"remindHash" => $remindHashed,
				), array(
					"email" => $values["email"]
				));
	
				$template = $this->mailer->getCustomTemplate("REMIND_PASSWORD_CONFIRM", $values, TRUE);
				$this->getPresenter()->flassMessage("Na Váš email byla odeslána zpráva k ověření.","flash-success");

			} else { // B. reset
				$password = Strings::random(14);
				$passwordHashed = Password::saltHash($password, $this->salt);
				$values["new_password"] = $password;

				$this->userModel->update(array(
					"password" => $passwordHashed,
				), array(
					"email" => $values["email"]
				));

				$template = $this->mailer->getCustomTemplate("REMIND_PASSWORD", $values, TRUE);
				$this->getPresenter()->flashMessage("Nové heslo bylo nastaveno. Zkontrolujte Vaši emailovou schránku.","flash-success");
			}

			$message->setSubject($template["subject"]);
			$message->setHtmlBody($template["body"]);
			$this->mailer->send($message);

		} else {
			$this->getPresenter()->flashMessage("Tento uživatel neexistuje.","flash-error t");
		}

		$this->redirect("this");
	}


	public function render()
	{
		parent::useTemplate();
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

			$this->flashMessage("Nové heslo bylo nastaveno. Zkontrolujte Vaši emailovou schránku.","success");

		} else {
			$this->flashMessage("Tato žádost již není platná.","error");
		}

		$this->redirect("this");
	}

}