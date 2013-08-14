<?php

namespace Components;

use Schmutzka\Application\UI\Control;
use Schmutzka\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Utils\Strings;


class RemindPasswordControl extends Control
{
	/** @var string */
	public $from = 'no-reply@ourCompany.com';

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Nette\Mail\IMailer */
	public $mailer;

	/** @inject @var Schmutzka\Security\UserManager */
	public $userManager;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText('email', 'Váš email:')
			->addRule(Form::FILLED, 'Zadejte email')
			->addRule(Form::EMAIL, 'Opravte formát emailu');

		$form->addSubmit('send', 'Zaslat nové heslo')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;

		if ($record = $this->userModel->item(array('email' => $values['email']))) {
			$message = new Message;
			$message->setFrom($this->from)
				->addTo($values['email']);

			$values['new_password'] = $password = Strings::random(10);
			$this->userManager->updatePasswordForUser(array('email' => $values['email']), $password);

			$template = $this->mailer->getCustomTemplate('REMIND_PASSWORD', $values, TRUE);

			$message->setSubject($template['subject']);
			$message->setHtmlBody($template['body']);
			$this->mailer->send($message);

			$this->presenter->flashMessage('Nové heslo bylo nastaveno. Zkontrolujte Vaši emailovou schránku.', 'success');

		} else {
			$this->presenter->flashMessage('Tento uživatel neexistuje.', 'error');
		}

		$this->presenter->redirect('this');
	}


	protected function renderAdmin()
	{
		$form = $this['form'];

		$form->id = 'recoverform';
		$form['email']->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'Zadejte Váš email');
		$form['send']->setAttribute('class', 'btn btn-success');
	}

}
