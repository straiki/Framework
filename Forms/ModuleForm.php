<?php

namespace Schmutzka\Forms;

use Kdyby;
use Schmutzka;
use Schmutzka\Application\UI\Form;

class ModuleForm extends Form
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @var string main model name */
	protected $mainModelName;

	/** @var Models\* */
	protected $mainModel;

	/** @var string */
	protected $onEditRedirect = "default";

	/** @var bool */
	protected $nullId = "id";

	/** @var bool */
	protected $idName = "id";

	/** @var array */
	protected $moduleParams;


	public function attached($presenter)
	{
		if ($this->idName && isset($presenter->{$this->idName})) {
			$this->{$this->idName} = $presenter->{$this->idName};

			$key[$this->idName] = $this->{$this->idName};

			$defaults = $this->{$this->mainModelName}->item($key);
			$this->setDefaults($defaults);
		}

		$this->moduleParams = $presenter->moduleParams;

		parent::attached($presenter); // intentionaly, build() might use $moduleParams
	}


	/**
	 * Set defaults
	 */
	public function afterBuild()
	{
		$this->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);
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
	public function process($form)
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
