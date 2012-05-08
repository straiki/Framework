<?php

namespace Schmutzka\Forms;

use Nette\Mail\Message;

class FeedbackForm extends Form
{
	/** @var \User */
	private $user;


	/** @var string */
	public $to;

	/** @var string */
	public $subject = "Feedback reply";

	/** @var bool */
	public $logSender = TRUE;
	
	/** @var string */
	public $flashContent = "Váš feedback byl odeslán. Děkujeme.";


	public function __construct($user)
	{
		parent::__construct();
		$this->user = $user;
	}


	public function build()
	{
		parent::build();

		$this->addTextarea("message", "Zpráva pro nás", 68, 10)
			->addRule(Form::FILLED,"Napište nám Vaše trápení")
			->setAttribute("class","feedbackTextarea");
		$this->addSubmit("submit","Odeslat");

		return $this;
	}
	

	public function process($form)
	{	
		$values = $form->values;

		$mail = new Message;
		$mail->addTo($this->to)
			->setSubject($this->subject);

		if ($this->logSender) {
			if ($this->user->loggedIn) {
				$email = (isset($this->user->identity->email) ? $this->user->identity->email : NULL);
				$login = (isset($this->user->identity->login) ? $this->user->identity->login : NULL);
			};
			$mail->setFrom($email, $login);
		}

		$mail->setBody($values["message"])
			->send();

		$this->flashMessage($this->flashContent,"flash-success");
		$this->redirect("default");
	}


}