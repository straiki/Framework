<?php

/**
 * Usefull to joint 2 tables of relation 1:m
 * @types: 1 - multiselect (2 - addDynamic, not ready yet)
 */

namespace Schmutzka\Forms;

use Schmutzka\Application\UI\Form;

class InsertUpdateJointForm extends  Form
{
	/** @persistent */
	public $id;

	/** @persistent */
	public $columnName;

	/** @var string */
	public $flashText = "Záznam byl úspěšně uložen.";

	/** @var string */
	public $redirect = "this";

	/** @var string */
	public $updateCaption = "Uložit";

	/** @var bool */
	public $nullId = TRUE;

	/** @var Models\* */
	private $model;


	/**
	 * @param Models\*
	 * @param array
	 * @param string
	 */
	public function __construct($model, $id, $columnName)
	{
		parent::__construct();
		$this->model = $model;
		$this->id = $id;
		$this->columnName = $columnName;
	}


	public function build()
	{
		parent::build();
		$this->addSubmit("send");

		if ($this->id) {
			$this["send"]->caption = $this->updateCaption;

			$defaults = $this->model->fetchPairs($this->columnName, $this->columnName, $this->id);
			$this[$this->columnName]->setDefaultValue($defaults);

		} else {
			$this["send"]->caption = $this->insertCaption;
		}
	}


	public function process($form)
	{
		$values = $form->values;
		$this->model->delete($this->id); // clear previous - only if not attached anything! fix later if required

		$items = array_pop($values);

		$array = $this->id;
		foreach ($items as $value) {
			$array[$this->columnName] = $value;
			$this->model->insert($array);
		}

		$this->flashMessage($this->flashText, "flash-success");
		$this->redirect($this->redirect);
	}

}
