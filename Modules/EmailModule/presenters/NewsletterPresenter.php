<?php

namespace EmailModule;

use Nette;

class NewsletterPresenter extends \AdminModule\BasePresenter
{
	/** @inject @var Nette\Mail\IMailer */
	public $mailer;


	/**
	 * Newsletter form
	 */
	public function createComponentNewsletterForm()
	{
		$form = new Forms\NewsletterForm($this->models->user, $this->models->newsletterLog, $this->mailer, $this->user);
		$form->from = 'info@techambition.com';
		return $form;
	}


	/**
	 * Newsletter grid
	 */
	public function createComponentNewsletterLogGrid()
	{
		return new Grids\NewsletterLogGrid($this->models->newsletterLog, $this->models->user);
	}

}