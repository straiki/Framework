<?php

namespace EmailModule\Components;

use Nette\Utils\Html;
use Schmutzka\Utils\Pregi;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;

class NewsletterControl extends Control
{
	/** @var string */
	public $from;

	/** @inject @var Models\User */
	public $userModel;

	/** @inject @var Models\NewsletterLog  */
	public $newsletterLogModel;

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
		$form->addGroup('');
		$form->addSelect('type', 'Kam poslat:', $this->typeArray)
			->setPrompt('Vyberte')
			->addCondition(Form::EQUAL, 'spec_mail')
				->toggle('spec_mail')
			->addCondition(Form::EQUAL, 'user_group')
				->toggle('user_group');

		// A. email list
		$form->addGroup('')->setOption('container', Html::el('fieldset')->id('spec_mail')->style('display:none'));
		$form->addTextarea('email_list', 'Adresáti:')
			->setAttribute('class', 'email_list')
			->addConditionOn($this['type'], Form::EQUAL, 'spec_mail')
				->addRule(Form::FILLED, 'Povinné');

		// B. user group
		$form->addGroup('')->setOption('container', Html::el('fieldset')->id('user_group')->style('display:none'));
		$userGroups = $this->userModel->fetchPairs('role', 'role');
		$form->addSelect('user_group', 'Skupina uživatelů:', $userGroups)
			->setPrompt('Vyberte')
			->addConditionOn($this['type'], Form::EQUAL, 'user_group')
				->addRule(Form::FILLED, 'Povinné');

		$form->addGroup('');
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
			$emailList = Pregi::extractEmails($values['email_list']);

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
		$this->newsletterLogModel->insert($values);

		$this->presenter->flashMessage('Odesláno.', 'success');
		$this->presenter->redirect('default', array('id' => NULL));
	}

}
