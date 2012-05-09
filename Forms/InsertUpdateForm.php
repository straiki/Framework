<?php

namespace Schmutzka\Forms;

use Schmutzka\Forms\Form;

class InsertUpdateForm extends  Form
{
	/** @ar object */
	private $model;


	/** @persistent */
	public $id;

	/** @var string */
	public $flashText = "Záznam byl úspěšně uložen.";

	/** @var string */
	public $redirectInsert = "default";

	/** @var string */
	public $redirectUpdate = "default";

	/** @var array */
	public $onValues;


	public function __construct($model, $id)
	{
		parent::__construct();
		$this->model = $model;
		$this->id = $id;
	}


	public function build()
	{
		parent::build();
		$this->addSubmit();

		if ($this->id) {
			$this->setDefaults($this->model->item($this->id));
		}
	}


	public function process($form)
	{
		$values = $form->values;

		if ($this->onValues) { // callback() doesn't return any values
			$values = $this->parent->{$this->onValues}($values);
		}

		if ($this->id) {
			$this->model->update($values, $this->id);
			$this->flashMessage($this->flashText, "flash-success");
			$this->redirect($this->redirectUpdate);
		}
		else {
			$this->model->insert($values);
			$this->flashMessage($this->flashText, "flash-success");
			$this->redirect($this->redirectInsert);
		}
	}

}