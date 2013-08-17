<?php

namespace Components;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Control;
use Schmutzka\Security\UserManager;


class ChangePasswordControl extends Control
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addPassword('oldPassword', 'Staré heslo:')
			->addRule(Form::FILLED, 'Zadejte staré heslo');
		$form->addPassword('password', 'Nové heslo:')
			->addRule(Form::FILLED, 'Zadejte nové heslo')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít aspoň %d znaků', 5);
		$form->addPassword('passwordCheck', 'Znovu nové heslo:')
			->addRule(Form::FILLED, 'Zadejte heslo k ověření')
			->addRule(Form::EQUAL, 'Hesla musejí být shodná', $form['password']);
		$form->addSubmit('send', 'Změnit heslo')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;
		$userData = $this->userModel->item($this->user->id);
		$oldPass = UserManager::calculateHash($values['oldPassword'], $userData['salt']);

		if ($oldPass != $userData['password']) {
			$this->presenter->flashMessage('Zadali jste chybně staré heslo.', 'error');

		} else {
			$data['password'] = UserManager::calculateHash($values['password'], $userData['salt']);
			$this->userModel->update($data, $this->user->id);
			$this->presenter->flashMessage('Heslo bylo úspěšně změněno.', 'success');
		}

		$this->presenter->redirect('this');
	}


	protected function renderAdmin()
	{
		$form = $this['form'];
		$form['oldPassword']->setAttribute('class', 'form-control');
		$form['password']->setAttribute('class', 'form-control');
		$form['passwordCheck']->setAttribute('class', 'form-control');
	}

}
