<?php

namespace Schmutzka\Forms;

class StaticCodebookForm extends Form
{

	/** @var string */
	public $flashText = "Údaje uloženy.";


	/** @var \Models\Codebook */
	private $model;


	public function __construct($model)
	{
		parent::__construct();
		$this->model = $model;
	}


	public function build()
	{
		parent::build();

		$this->addSubmit("submit", "Uložit");

		$defaults = $this->model->all()->fetchPairs("name", "value");
		$this->setDefaults($defaults);
	}


	public function process(StaticCodebookForm $form)
	{
		$values = $form->values;
		foreach ($values as $key => $name) {
			$this->model->upsert(
				array("name" => $key, "value" => $name),
				array("name" => $key)
			);
		}

		$this->flashMessage($this->flashText, "success");
		$this->redirect("this");
	}

}