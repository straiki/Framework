<?php

namespace EventModule\Forms;

use Schmutzka\Forms\Form;
use Models;
use Nette;
use Schmutzka;
use Nette\Utils\Strings;
use Schmutzka\Utils\Filer;


class EventCategoryForm extends Form
{
	/** @persistent */
	public $id;

	/** @var Models\EventCategory */
	private $eventCategoryModel;

	/** @param Schmutzka\Services\ParamService */
    private $paramService;


	/**
	 * @param Models\EventCategory
	 * @param Schmutzka\Services\ParamService
	 * @param int
	 */
	public function __construct(Models\EventCategory $eventCategoryModel, Schmutzka\Services\ParamService $paramService, $id) 
	{ 
		parent::__construct(); 
		$this->eventCategoryModel = $eventCategoryModel;
		$this->paramService = $paramService;
		$this->id = $id;
	}


	/**
	 * Build form
	 */
	public function build()
    {
		parent::build();

		$this->addText("name", "Název kategorie:")
			->addRule(Form::FILLED, "Povinné");

		if ($this->paramService->params["cmsParams"]["event_module_enable_expiration"]) {
			$this->addCheckbox("use_expiration", "Povolit expiraci");
		}

		$this->addSubmit();

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->eventCategoryModel->item($this->id);
			$this->setDefaults($defaults);
		}
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

		if ($this->id) {
			$this->eventCategoryModel->update($values, $this->id);

		} else {
			$this->eventCategoryModel->insert($values);
		}

		$this->flashMessage("Uloženo.","flash-success");
		$this->redirect("default", array("id" => NULL));
	}

}