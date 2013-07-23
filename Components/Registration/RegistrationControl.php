<?php

namespace Components;

use Nette;
use Nette\DateTime;
use Nette\Mail\Message;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Control;
use Schmutzka\Application\UI\Form;
use Schmutzka\Security\UserManager;


/**
 * @method setFrom(string)
 * @method getFrom()
 * @method setLoginAfter(bool)
 * @method getLoginAfter()
 * @method setSendSuccessEmail(bool)
 * @method getSendSuccessEmail()
 */
class RegistrationControl extends Control
{
	/** @inject @var Nette\Mail\IMailer */
	public $mailer;

	/** @inject @var Schmutzka\Models\User  */
	public $userModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @var string */
	private $from;

	/** @var bool */
	private $loginAfter = TRUE;

	/** @var bool */
	private $sendSuccessEmail = FALSE;


	protected function createComponentForm()
	{
		$userModel = $this->userModel;

		$form = new Form;
		$form->addText('login', $this->paramService->form->login->label)
			->addRule(Form::FILLED, $this->paramService->form->login->ruleFilled)
			->addRule(function ($input) use ($userModel) {
				return ! $userModel->item(array('login' => $input->value));
			}, $this->paramService->form->login->alreadyExists);

		$form->addText('email', $this->paramService->form->email->label)
			->addRule(Form::FILLED, $this->paramService->form->email->ruleFilled)
			->addRule(Form::EMAIL, $this->paramService->form->email->ruleFormat)
			->addRule(function ($input) use ($userModel) {
				return ! $userModel->item(array('email' => $input->value));
			}, $this->paramService->form->email->alreadyExists);

		$form->addPassword('password', $this->paramService->form->password->label)
			->addRule(Form::FILLED, $this->paramService->form->password->ruleFilled)
			->addRule(Form::MIN_LENGTH, $this->paramService->form->password->length, 5);

		$form->addSubmit('send', $this->paramService->form->send->register)
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function processForm($form)
	{
		$rawValues = $values = $form->getValues();
		unset($values['conditions']);

		$values['salt'] = Strings::random(22);
		$values['password'] = UserManager::calculateHash($values['password'], $values['salt']);
		$values['created'] = new DateTime;

		$this->userModel->insert($values);
		$values = $rawValues + $values;

		if ($this->sendSuccessEmail) {
			$this->sendSuccessEmail($values);
		}

		if ($this->loginAfter) {
			$this->user->login($values['email'], $rawValues['password']);
			$this->presenter->flashMessage($this->paramService->registration->onSuccessAndLogin, 'success');

		} else {
			$this->presenter->flashMessage($this->paramService->registration->onSuccess, 'success');
		}

		$this->redirect('this');
	}


	/**
	 * @param array
	 */
	private function sendSuccessEmail($values)
	{
		$message = new Message;
		$message->setFrom($this->from);
		$message->addTo($values['email']);

		$template = $this->mailer->getCustomTemplate('REGISTRATION_SUCESSFULL', $values, TRUE);
		$message->setSubject($template['subject']);
		$message->setHtmlBody($template['body']);

		$this->mailer->send($message);
	}

}
