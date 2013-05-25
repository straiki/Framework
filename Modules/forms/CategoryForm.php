<?php

namespace Forms;

use Schmutzka;
use Schmutzka\Forms\Form;
use Nette;

class CategoryForm extends Form
{
	/** @persistent */
	public $id;

	/** @var Models\* */
	private $categoryModel;


	/**
	 * @param Models\*
	 * @param int
	 */
	public function __construct($categoryModel, $id) 
	{ 
		parent::__construct(); 
		$this->categoryModel = $categoryModel;
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

		$this->addSubmit();

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->categoryModel->item($this->id);
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
			$this->categoryModel->update($values, $this->id);

		} else {
			$this->categoryModel->insert($values);
		}

		$this->flashMessage("Uloženo.","flash-success");
		$this->redirect("default", array("id" => NULL));
	}

}