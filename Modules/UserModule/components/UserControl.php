<?php

namespace UserModule\Controls;

use Nette;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\ModuleControl;

class UserControl extends ModuleControl
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Security\UserManager */
	public $userManager;


	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->id = $presenter->id;
	}


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText("login", "Celé jméno:")
			->addRule(Form::FILLED, "Zadejte Vaše jméno")
			->setAttribute("autocomplete", "off");

		$form->addText("email", "Email:")
			->addRule(Form::FILLED, "Zadejte email")
			->setAttribute("autocomplete", "off");

		$form->addSelect("role", "Role:", (array) $this->moduleParams->roles)
			->addRule(Form::FILLED, "Vyberte roli")
			->setDefaultValue("user");

		$form->addPassword("password", $this->paramService->form->password->label);
		if ($this->id == NULL) {
			$form["password"]->addRule(Form::FILLED, $this->paramService->form->password->ruleFilled)
				->addRule(Form::MIN_LENGTH, $this->paramService->form->password->length, 5);

		} else {
			$form["password"]->setOption("description", "Zadejte nové heslo pro jeho změnu.");
		}

		$form->addPassword("password2", $this->paramService->form->passwordAgain->label);
		if ($this->id == NULL) {
			$form["password2"]->addRule(Form::FILLED, $this->paramService->form->password->ruleFilled)
				->addRule(Form::MIN_LENGTH, $this->paramService->form->password->length, 5);
		}

		$form->addSubmit("send", "Přidat")
			->setAttribute("class", "btn btn-success");

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;

		try {
			if ($this->id) {
				$this->userManager->update($values, $this->id);

			} else {
				$user = $this->userManager->register($values);
				$this->id = $user["id"];
			}

			$this->presenter->flashMessage("Uloženo.", "success");
			$this->presenter->redirect("edit", array(
				"id" => $this->id
			));

		} catch (\Exception $e) {
			$this->presenter->flashMessage($e->getMessage(), "error");
		}
	}

}
