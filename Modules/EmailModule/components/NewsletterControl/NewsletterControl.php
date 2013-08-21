<?php

namespace EmailModule\Components;

use Nette\Utils\Html;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class NewsletterControl extends Control
{
	/** @var string */
	public $from;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Models\EmailLog  */
	public $emailLogModel;

	/** @inject @var Nette\Mail\IMailer */
	public $mailer;

	/** @var array */
	private $typeArray = array(
		'spec_mail' => 'Konkrétní adresy',
		'user_group' => 'Skupině uživatelů'
	);


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addGroup();
		$form->addSelect('type', 'Kam poslat:', $this->typeArray)
			->setPrompt('Vyberte')
			->addCondition(Form::EQUAL, 'spec_mail')
				->toggle('spec_mail')
			->endCondition()
			->addCondition(Form::EQUAL, 'user_group')
				->toggle('user_group');

		// A. email list
		$form->addToggleGroup('spec_mail');
		$form->addTextarea('email_list', 'Adresáti:')
			->setAttribute('class', 'email_list')
			->addConditionOn($form['type'], Form::EQUAL, 'spec_mail')
				->addRule(Form::FILLED, 'Povinné');

		// B. user group
		$form->addToggleGroup('user_group');
		$userGroups = $this->userModel->fetchPairs('role', 'role');
		$form->addSelect('user_group', 'Skupina uživatelů:', $userGroups)
			->setPrompt('Vyberte')
			->addConditionOn($form['type'], Form::EQUAL, 'user_group')
				->addRule(Form::FILLED, 'Povinné');

		$form->addGroup();
		$form->addText('name', 'Interní označení:');
		$form->addText('subject', 'Předmět emailu:')
			->addRule(Form::FILLED, 'Povinné');
		$form->addTextarea('content', 'Obsah emailu:')
			->setAttribute('class', 'ckeditor');

		$form->addGroup();
		$form->addSubmit('send', 'Odeslat')
			->setAttribute('class', 'btn btn-primary confirm');

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;
		if ($values['type'] == 'spec_mail') {
			$emailList = $this->getEmailsFromString($values['email_list']);

		} elseif ($values['type'] == 'user_group') {
			$emailList = $this->userModel->where('role', $values['user_group']->fetchPairs('email', 'email'));
		}

		if (!$emailList) {
			$this->flashMessage('Nebyly nalezeny žádné emaily.', 'error');
			$this->redirect('this');
		}

		foreach ($emailList as $email) {
			$message = new Nette\Mail\Message;
			$message->setFrom($this->from);
			$message->addTo($email);
			$message->setSubject($values['subject']);
			$message->setHtmlBody($values['content']);

			$this->mailer->send($message);
		}

		// log!
		$values['email_list'] = serialize($emailList);
		$values['sent'] = new Nette\DateTime;
		$values['user_id'] = $this->user->id;
		$this->emailLogModel->insert($values);

		$this->presenter->flashMessage('Odesláno.', 'success');
		$this->presenter->redirect('default', array('id' => NULL));
	}


	/**
	 * @param  string
	 * @return array
	 */
	private function getEmailsFromString($string)
	{
		$pattern = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/';
   		preg_match_all($pattern, $string, $matches);

   		return $matches;
	}

}
