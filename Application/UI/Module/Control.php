<?php

namespace Schmutzka\Application\UI\Module;

use Schmutzka;

class Control extends Schmutzka\Application\UI\Control
{
	/** @persistent @var int */
	public $id;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;


	public function attached($presenter)
	{
		parent::attached($presenter);
		if (($this->id || (property_exists($presenter, "id") && $this->id = $presenter->id)) && isset($this["form"])) {
			$this["form"]["send"]->caption = "Uložit";
			$this["form"]["send"]
				->setAttribute("class", "btn btn-primary");

			$this["form"]->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->model->item($this->id);
			$defaults = $this->preProcessDefaults($defaults);
			$this["form"]->setDefaults($defaults);
		}
	}


	public function processForm($form)
	{
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->presenter->redirect("default", array("id" => NULL));
		}

		$values = $form->values;
		$values = $this->preProcessValues($values);

		// process all dynamics
		foreach ($values as $key => $value) {
			if (isset($form[$key]) && $form[$key] instanceof Kdyby\Replicator\Container) {
				foreach ($value as $key2 => $value2) {
					$this->model->update($value2, $key2);
				}
				unset($values[$key]);
			}
		}

		if ($this->id) {
			$this->model->update($values, $this->id);

		} else {
			$this->id = $this->model->insert($values);
		}

		$this->postProcessValues($values, $this->id);

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect("edit", array("id" => $this->id));
	}


	public function render()
	{
		parent::useTemplate();
		$this->template->render();
	}


	/********************** helpers **********************/


	/**
	 * @return  Nette\ArrayHash
	 */
	public function getModuleParams()
	{
		return $this->paramService->getModuleParams($this->presenter->module);
	}


	/**
	 * @return  *\Model\*
	 */
	public function getModel()
	{
		$className = $this->getReflection()->getName();
		$classNameParts = explode("\\", $className);
		$modelName = lcfirst(substr(array_pop($classNameParts), 0, -7)) . "Model";

		return $this->{$modelName};
	}


	/********************** process helpers **********************/


	/**
	 * @param   array
	 * @return  array
	 */
	public function preProcessDefaults($defaults)
	{
		return $defaults;
	}


	/**
	 * @param   array
	 * @return  array
	 */
	public function preProcessValues($values)
	{
		return $values;
	}


	/**
	 * @param   array
	 * @param   int
	 */
	public function postProcessValues($values, $id)
	{
	}

}
