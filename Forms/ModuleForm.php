<?php

namespace Schmutzka\Forms;

use Schmutzka;
use Schmutzka\Application\UI\Form;

class ModuleForm extends Form
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @var string */
	protected $mainModelName;

	/** @var string */
	protected $onEditRedirect = "default";

	/** @var bool */
	protected $nullId = "id";


	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->id = $presenter->id;
	}


	/**
	 * Set defaults
	 */
	public function afterBuild()
	{
		$this->addSubmit();

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")->setValidationScope(FALSE);
			$this->setDefaults($this->{$this->mainModelName}->item($this->id));
		}
	}


	/**
	 * Pre-process values
	 * @param array
	 */
	protected function preProcess($values)
	{
		return $values;
	}


	/**
	 * Process form
	 */
	public function process(Form $form)
	{
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->redirect("default", array("id" => NULL));
		}

		$values = $form->values;
		$values = $this->preProcess($values);

		if ($this->id) {
			$this->mainModel->update($values, $this->id);

		} else {
			$this->mainModel->insert($values);
		}

		$this->flashMessage("Uloženo.","flash-success");

		if ($this->nullId) {
			$this->redirect($this->onEditRedirect, array($this->nullId => NULL));

		} else {
			$this->redirect($this->onEditRedirect);
		}
	}

}