<?php

namespace Components;

use Schmutzka\Application\UI\Control,
	Schmutzka\Forms\Form,
	Schmutzka\Utils\Password,
	Nette\Mail\Message;

class MailingListControl extends Control
{

	/** @var \Context */
	private $container;


	/** @var string */
	public $emailFrom = "no-reply@domain.com";

	/** @var string */
	public $subject = "Novinky";

	/** @var string */
	public $malingListTemplate = "email.latte";


	public function __construct($container)
    {
        parent::__construct();
		$this->container = $container;
    }


	/********************* subscribe *********************/


	protected function createComponentSubscribeForm()
	{
		$form = new Form;
		$form->addText("email", "Váš email:")
			->addRule(Form::FILLED,"Zadejte Váš email")
			->addRule(Form::EMAIL,"Email nemá správný formát");
		$form->addSubmit("send", "Odebírat novinky");

		return $form;
	}

	
	/**
	 * Add email to subscription
	 * @form
	 */
	public function subscribeFormSent(Form $form)
	{
		$values = $form->values;

		$mailCheck = $this->models->mailingList->count(array(
				"email" => $values["email"]
		));

		if ($mailCheck) {
			$this->flashMessage("Tento email je již přidán.","flash-error");
		}
		else {
			$regtime = date("Y-m-d H:i:s");
			$item = array(
				"regtime" => $regtime,
				"email" => $values["email"],
				"hash" => Password::blend($values["email"], $regtime)
			);

			$this->models->mailingList->insert($item);
			$this->flashMessage("Váš email byl přidán.","flash-success");
		}

		$this->redirect("this");
	}


	public function render()
	{
		$this->template->render();
	}


	/********************* send newsletter *********************/


	protected function createComponentSendForm() 
	{ 
		$form = new Form; 
		$form->addTextarea("message")
			->addRule(Form::FILLED,"Zadejte zprávu");
		$form->addSubmit("send","Poslat"); 

		return $form; 
	}


	/**
	 * Send message to all subscribed
	 */
	public function sendFormSent(Form $form)
	{
		$values = $form->values;
		$emailList = $this->models->mailingList->fetchPairs("hash", "email");

		foreach ($emailList as $hash => $email) {

			$mail = new Message;
			$mail->addTo($email)
				->setFrom($this->emailFrom)
				->setSubject($this->subject);

			$template = $this->createTemplate();
			$template->message = $values["message"];
			$template->sitePath = "http://" . $this->context->httpRequest->url->scriptPath;
			$template->removeHash = $hash;

			if ($this->malingListTemplate == "email.latte") { // not changed
				$template->setFile(__DIR__ . "/" . $this->malingListTemplate);
			}
			else {
				$template->setFile($this->malingListTemplate);
			}

			$mail->setHtmlBody($template);
			$mail->send();
		}
			
		$this->flashMessage("Počet odeslaných zpráv: ". count($emailList), "flash-success");
		$this->redirect("this");
	}


	public function renderSend()
	{
		$this->template->setFile(dirname(__FILE__) . "/MailingListControlSend.latte"); // automatic?
		$this->template->render();
	}


	/********************* unsubscribe *********************/


	/**
	 * Find and unsubscribe user
	 * @param string
	 */
	public function handleUnsubscribe($hash)
	{
		if ($this->models->mailingList->delete(array("hash" => $hash))) {
			$this->flashMessage("Byli jste úspěšně odhlášeni.","flash-success");
		}
		else {
			$this->flashMessage("Zadali jste chybnou adresu, nebo byl tento email již odhlášen.","flash-error");
		}
		
		$this->parent->redirect("Homepage:default");
	}

}