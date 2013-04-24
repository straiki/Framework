<?php

namespace EmailModule\Forms;

use Schmutzka\Forms\Form;
use Models;
use Nette;
use Nette\Utils\Html;
use Schmutzka\Utils\Pregi;
use Schmutzka;

class NewsletterForm extends Form
{
	/** @var string */
	public $from;

	/** @var Models\User */
	private $userModel;

	/** @var Models\NewsletterLog  */
	private $newsletterLogModel;

	/** @var Nette\Mail\IMailer */
	private $mailer;

	/** @var Schmutzka\Security\User */
	private $user;


	/**
	 * @param Models\User
	 * @param Models\NewsletterLog
	 * @param Nette\Mail\IMailer 
	 * @param Schmutzka\Security\User
	 */
	public function __construct(Models\User $userModel, Models\NewsletterLog $newsletterLogModel, Nette\Mail\IMailer $mailer, Schmutzka\Security\User $user) 
	{ 
		parent::__construct(); 
		$this->userModel = $userModel;
		$this->newsletterLogModel = $newsletterLogModel;
		$this->mailer = $mailer;
		$this->user = $user;
	}


	/**
	 * Build form
	 */
	public function build()
    {
		parent::build();

		$this->addGroup(""); 
		
		$typeArray = array( // duplicate shit?
			"spec_mail" => "Konkrétní adresy",
			"user_group" => "Skupině uživatelů"
		);
		$this->addSelect("type", "Kam poslat:", $typeArray)
			->setPrompt("Vyberte")
			->addCondition(Form::EQUAL, "spec_mail")
				->toggle("spec_mail")
			->addCondition(Form::EQUAL, "user_group")
				->toggle("user_group");

		// A. email list
		$this->addGroup("")->setOption('container', Html::el('fieldset')->id("spec_mail")->style("display:none")); 
		$this->addTextarea("email_list", "Adresáti:")
			->setAttribute("class","email_list")
			->addConditionOn($this["type"], Form::EQUAL, "spec_mail")
				->addRule(Form::FILLED, "Povinné");

		// B. user group
		$this->addGroup("")->setOption('container', Html::el('fieldset')->id("user_group")->style("display:none")); 
		$userGroups = $this->userModel->fetchPairs("role", "role");
		unset($userGroups["superadmin"]);
		$this->addSelect("user_group","Skupina uživatelů:", $userGroups)
			->setPrompt("Vyberte")
			->addConditionOn($this["type"], Form::EQUAL, "user_group")
				->addRule(Form::FILLED, "Povinné");

		$this->addGroup(""); 
		$this->addText("name", "Interní označení:");
		$this->addText("subject", "Předmět emailu:")
			->addRule(Form::FILLED, "Povinné");
		$this->addTextarea("content", "Obsah emailu:")
			->setAttribute("class","tinymce");

		$this->addGroup(); 
		$this->addSubmit("send", "Odeslat")
			->setAttribute("class","btn btn-primary btn-large confirm");
	}


	/**
	 * Process form
	 */
	public function process(Form $form)
	{
		$values = $form->values;
		if ($values["type"] == "spec_mail") {
			$emailList = Pregi::extractEmails($values["email_list"]);

		} elseif ($values["type"] == "user_group") {
			$emailList = $this->userModel->where("role", $values["user_group"]->fetchPairs("email", "email"));
		}

		if (!$emailList) {
			$this->flashMessage("Nebyly nalezeny žádné emaily.","flash-error");
			$this->redirect("this");
		}


		foreach ($emailList as $email) {
			$message = new Nette\Mail\Message;
			$message->setFrom($this->from);
			$message->addTo($email);
			$message->setSubject($values["subject"]);
			$message->setHtmlBody($values["content"]);

			$this->mailer->send($message);
		}

		// log!
		$values["email_list"] = serialize($emailList);
		$values["sent"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;
		$this->newsletterLogModel->insert($values);

		$this->flashMessage("Odesláno.", "flash-success");
		$this->redirect("default", array("id" => NULL));
	}

}