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
 * @method setPermalogin(bool)
 * @method getPermalogin()
 */
class LoginControl extends Control
{
	/** @var array */
	public $onLoginSuccess = array();

	/** @var array */
	public $onLoginError = array();

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Nette\Http\Session */
	public $session;

	/** @var string */
	private $forgotLink = NULL;

	/** @var string */
	private $loginColumn = "email";

	/** @var bool */
	private $permalogin = FALSE;


	protected function createComponentForm()
	{
		$form = new Form;

		$formLabels = $this->paramService->form;
		$customLabels = $formLabels->{$this->loginColumn};

		$form->addText("login", $customLabels->label)
			->addRule(Form::FILLED, $customLabels->ruleFilled);

		if ($this->loginColumn == "email") {
			$form["login"]->addRule(Form::EMAIL, $customLabels->ruleFormat);
		}

		$form->addPassword("password", $formLabels->password->label)
			->addRule(Form::FILLED, $formLabels->password->ruleFilled);

		if ($this->permalogin) {
			$form->addCheckbox("permalogin", $formLabels->permalogin->label)
				->setDefaultValue(TRUE);
		}

		$form->addSubmit("send", $formLabels->send->login)
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function processForm($form)
	{
		try {
			$values = $form->values;

			if ($this->permalogin && $values["permalogin"]) {
				$this->user->setExpiration("+ 14 days", FALSE);

			} else {
				$this->user->setExpiration("+ 6 hours", TRUE);
			}

			$this->user->login($values["login"], $values["password"]);

			if ($this->onLoginSuccess) {
				$this->onLoginSuccess($this->user);
			}

			if ($this->paramService->flashes->onLogin) {
				$this->presenter->flashMessage($this->paramService->flashes->onLogin, "success");
			}

			$sectionKey = substr(sha1($this->paramService->wwwDir), 6);
			$baseSession = $this->session->getSection("baseSession_" . $sectionKey);
			$this->presenter->restoreRequest($baseSession->backlink); // @todo refactor to absolute param - standart!
			$this->presenter->redirect("Homepage:default");

		} catch (Nette\Security\AuthenticationException $e) {
			if ($this->onLoginError) {
				$this->onLoginError($values);
			}

			$this->presenter->flashMessage($e->getMessage(), "error");
		}
	}


	public function render()
	{
		parent::useTemplate();
		if ($this->forgotLink) {
			$this->template->forgotLink = $this->forgotLink;
		}

		$this->template->render();
	}


	public function renderAdmin()
	{
		parent::useTemplate("admin");
		$this->template->render();
	}

}
