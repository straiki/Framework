<?php

namespace Schmutzka\Forms;

class StaticCodebookForm extends Form
{
	/** @inject @var Schmutzka\Models\Codebook */
	public $codebookModel;


	public function build()
	{
		parent::build();
		$this->addSubmit("submit", "Uložit");
		$defaults = $this->codebookModel->fetchAll()->fetchPairs("name", "value");
		$this->setDefaults($defaults);
	}


	public function process($form)
	{
		$values = $form->values;
		foreach ($values as $key => $name) {
			$this->codebookModel->upsert(
				array("name" => $key, "value" => $name),
				array("name" => $key)
			);
		}

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect("this");
	}

}
