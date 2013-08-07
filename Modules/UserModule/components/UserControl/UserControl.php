<?php

namespace UserModule\Components;

use Nette;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class UserControl extends Control
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Security\UserManager */
	public $userManager;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addGroup('');
		$form->addText('login', 'Celé jméno:')
			->addRule(Form::FILLED, 'Zadejte Vaše jméno')
			->setAttribute('autocomplete', 'off');

		$form->addText('email', 'Email:')
			->addRule(Form::FILLED, 'Zadejte email')
			->setAttribute('autocomplete', 'off');

		$form->addSelect('role', 'Role:', (array) $this->moduleParams->roles)
			->addRule(Form::FILLED, 'Vyberte roli')
			->setDefaultValue('user');

		$form->addPassword('password', $this->paramService->form->password->label);
		if ($this->id == NULL) {
			$form['password']->addRule(Form::FILLED, $this->paramService->form->password->ruleFilled)
				->addRule(Form::MIN_LENGTH, $this->paramService->form->password->length, 5);

		} else {
			$form['password']->setOption('description', 'Zadejte nové heslo pro jeho změnu.');
		}

		$form->addGroup('');
		$form->addSubmit('send', 'Přidat')
			->setAttribute('class', 'btn btn-success');

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;

		if ($this->id) {
			$this->userManager->update($values, $this->id);

		} else {
			$user = $this->userManager->register($values);
			$this->id = $user['id'];
		}

		$this->presenter->flashMessage('Uloženo.', 'success');
		$this->presenter->redirect('edit', array(
			'id' => $this->id
		));
	}

}
