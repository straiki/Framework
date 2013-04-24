<?php

namespace Schmutzka\Forms;

use Kdyby;
use Schmutzka;
use Schmutzka\Application\UI\Form;

class ModuleForm extends Form
{
	/** @persistent */
	public $id;

	/** @var string main model name */
	protected $mainModelName;

	/** @var Models\* */
	protected $mainModel;

	/** @var string */
	protected $onEditRedirect = "default";

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @var bool */
	protected $nullId = "id";

	/** @var bool */
	protected $idName = "id";


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->idName && isset($presenter->{$this->idName})) {
			$this->{$this->idName} = $presenter->{$this->idName};

			$key[$this->idName] = $this->{$this->idName};

			$defaults = $this->{$this->mainModelName}->item($key);
			$this->setDefaults($defaults);

			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);
		}
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
			$this->presenter->redirect("default", array("id" => NULL));
		}

		$values = $form->values;
		$values = $this->preProcess($values);

		// process all dynamics
		foreach ($values as $key => $value) {
			if ($form[$key] instanceof Kdyby\Replicator\Container) {
				foreach ($value as $key2 => $value2) {
					$this->{$this->mainModelName}->update($value2, $key2);
				}
				unset($values[$key]);
			}
		}

		if ($values) {
			if ($this->id) {
				$this->{$this->mainModelName}->update($values, $this->id);

			} else {
				$this->{$this->mainModelName}->insert($values);
			}
		}

		$this->presenter->flashMessage("Uloženo.", "success");

		if ($this->nullId) {
			$this->presenter->redirect($this->onEditRedirect, array($this->nullId => NULL));

		} else {
			$this->presenter->redirect($this->onEditRedirect);
		}
	}

}
