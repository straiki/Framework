<?php

namespace Schmutzka\Application\UI;

use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Control;

class ModuleControl extends Control
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->id = $presenter->id) {
			$this["form"]["send"]->caption = "Uložit";
			$this["form"]["send"]
				->setAttribute("class", "btn btn-primary");

			$this["form"]->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);
			$this["form"]->setDefaults($this->model->item($this->id));
		}
	}


	public function processForm($form)
	{
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->redirect("default", array("id" => NULL));
		}

		$values = $form->values;

		if ($this->id) {
			$this->model->update($values, $this->id);

		} else {
			$this->id = $this->model->insert($values);
		}

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

}
