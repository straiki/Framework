<?php

namespace EventModule\Forms;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Utils\Filer;


class EventCategoryForm extends ModuleForm
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\EventCategory */
	public $eventCategoryModel;


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

		$this->flashMessage("Uloženo.", "success");
		$this->redirect("default", array("id" => NULL));
	}

}