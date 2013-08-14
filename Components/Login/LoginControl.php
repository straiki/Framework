<?php

namespace Components;

use Nette;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Control;


/**
 * @method setForgotLink(string)
 * @method getForgotLink()
 * @method setLoginColumn(string)
 * @method getLoginColumn()
 */
class LoginControl extends Control
{
	/** @persistent @var string */
	public $backlink;

	/** @var array */
	public $onLoginSuccess = array();

	/** @var array */
	public $onLoginError = array();

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Nette\Http\Session */
	public $session;

	/** @var string */
	private $forgotLink = NULL;

	/** @var string */
	private $loginColumn = 'email';


	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->backlink = $presenter->backlink;
	}


	protected function createComponentForm()
	{
		$form = new Form;

		$formLabels = $this->paramService->form;
		$customLabels = $formLabels->{$this->loginColumn};

		$form->addText('login', $customLabels->label)
			->addRule(Form::FILLED, $customLabels->ruleFilled);

		if ($this->loginColumn == 'email') {
			$form['login']->addRule(Form::EMAIL, $customLabels->ruleFormat);
		}

		$form->addPassword('password', $formLabels->password->label)
			->addRule(Form::FILLED, $formLabels->password->ruleFilled);

		$form->addSubmit('send', $formLabels->send->login)
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function processForm($form)
	{
		try {
			$values = $form->values;
			$this->user->setExpiration('+ 14 days', FALSE);
			$this->user->login($values['login'], $values['password']);

			if ($this->onLoginSuccess) {
				$this->onLoginSuccess($this->user);
			}

			if ($this->paramService->flashes->onLogin) {
				$this->presenter->flashMessage($this->paramService->flashes->onLogin, 'success');
			}

			$this->presenter->restoreRequest($this->backlink);
			$this->presenter->redirect('Homepage:default');

		} catch (Nette\Security\AuthenticationException $e) {
			if ($this->onLoginError) {
				$this->onLoginError($values);
			}

			$this->presenter->flashMessage($e->getMessage(), 'error');
		}
	}


	protected function renderDefault()
	{
		if ($this->forgotLink) {
			$this->template->forgotLink = $this->forgotLink;
		}
	}


	protected function renderAdmin()
	{
		$form = $this['form'];

		$form->id = 'loginform';
		$form['login']->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'Email');
		$form['password']->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'Password');
		$form['send']->setAttribute('class', 'btn btn-success');
	}

}
