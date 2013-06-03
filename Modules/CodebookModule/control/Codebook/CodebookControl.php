<?php

namespace Schmutzka\Components;

use Schmutzka\Application\UI\Control,
	Schmutzka\Application\UI\Form,
	Schmutzka\Templates\MyHelpers,
	Nette\Utils\Html;

class CodebookControl extends Control
{
	/** @persistent */
	public $id;

	/** @var string */
	public $tableName = "codebook";

	/** @var bool */
	public $activeDisplay = TRUE;

	/** @var bool */
	public $activeRank = TRUE;

	/** @var string */
	public $whereUsed = NULL;

	/** @var string */
	public $withCount = FALSE;

	/** @var string */
	public $activeConvert = FALSE;

	/** @var int */
	public $nextRank = 10;


	/** @var string */
	private $codeType;

	/** @var string */
	private $model;

	/** @var array */
	private $yesNo = array(
		1 => "yes",
		0 => "no"
	);


	public function __construct($codeType, $model)
    {
        parent::__construct();
		$this->codeType = $codeType;
		$this->model = $model;
		$this->model->checkExistance($this->tableName); // check table existance
   }


	/********************* edit/delete *********************/

	/**
	 * Delete record
	 * @param int
	 */
	public function handleDelete($id)
	{
		$this->model->delete($id);
		$this->flashMessage("Deleted.","flash-success");

		if ($this->isAjax()) {
			$this->invalidateControl("codebook");
		}
		else {
			$this->redirect("this", array("id" => NULL));
		}
	}


	/**
	 * Edit record
	 * @param int
	 */
	public function handleEdit($id)
	{
		if (!$id) {
			$this->redirect("this");
		}
		$this->id = $id;

		$this["codebookForm"]->setDefaults($this->model->item($id));

		if($this->isAjax()) {
			$this->invalidateControl("codebook");
		}
	}


	/********************* component *********************/


	/**
	 * Codebook form
	 */
	protected function createComponentCodebookForm()
	{
		$form = new Form;
		$form->addText("value", "Value name")
			->addRule(Form::FILLED,"Mandatory");

		if ($this->activeDisplay) {
			$form->addSelect("display", "Display", $this->yesNo)
				->setDefaultValue(1);
		}

		if ($this->activeRank) {
			$form->addText("rank", "rank", 3)
				->setDefaultValue($this->model->getNextRank($this->codeType)); // predicted value
		}

		$form->addSubmit("send", "Save");

		return $form;
	}


	/**
	 * Sent codebook form
	 * @form
	 */
	public function codebookFormSent(Form $form)
	{
		$values = $form->values;
		$values["type"] = $this->codeType;

		$this->model->upsert($values, $this->id);
		$this->flashMessage("Saved.","flash-success");


		if($this->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->invalidateControl("codebook");
		}
		else {
			$this->redirect("this", array("id" => NULL));
		}
	}


	/********************* render *********************/


	/**
	 * Setup template values
	 */
	private function setupValues()
	{
		$this->template->codeList = $this->model->getCodesByType($this->codeType, $this->withCount, $this->whereUsed); // FIX!
		$this->template->codeType = $this->codeType;
		$this->template->yesNo = $this->yesNo;

		// settings
		$this->template->activeDisplay = $this->activeDisplay;
		$this->template->activeRank = $this->activeRank;

		// using table
		$this->template->whereUsed = $this->whereUsed;
		$this->template->withCount = $this->withCount;
		$this->template->activeConvert = $this->activeConvert;
	}


	/**
	 * Render default
	 */
	public function render()
	{
		$this->checkValues();
		$this->setupValues();

		$this->template->render();
	}


	/**
	 * Render sedibar
	 */
	public function renderSide()
	{
		$this->checkValues();
		$this->setupValues();

		$this->template->render();
	}


	/********************* convert *********************/


	/**
	 * Move records
	 * @param int
	 */
	public function handleConvertItem($id)
	{
		$options = $this->model->getCodeListByType($this->codeType);
		unset($options[$id]);

		if(!count($options)) { // kontrola počtu položek
			$this->flashMessage("Neexistuje žádná alternativní položka. Musíte ji nejdříve vytvořit.","flash-error");
		}
		else {
			$this->template->showConverForm = TRUE;
			$this["convertForm"]["newItem"]->setItems($options)
				->setPrompt("Vyberte");
		}

		if($this->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->invalidateControl("codebook");
		}
		else {
			// $this->redirect("this", array("id" => NULL));
		}
	}


	/** @broken
	 * Convert form
	 */
	protected function createComponentConvertForm()
	{
		$form = new Form;
		$form->addSelect("newItem","Nová položka:")
			->addRule(Form::FILLED,"Vyberte novou možnost.");

		$form->addSubmit("send","Nastavit");

		return $form;
	}



	/** @broken
	 * Convert
	 * @form
	 */
	public function convertFormSent(Form $form)
	{
		$values = $form->values;

		$this->model->convert($columnName, $this->item["id"], $values->newItem);

		$this->flashMessage("Úspěšně změněno.","pos");
		if($this->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->invalidateControl("codebook");
		}
		else {
			$this->redirect("this", array("id" => NULL));
		}
	}


	/********************* check values *********************/


	/**
	 * Checks variables
	 */
	private function checkValues()
	{
		if (isset($this->whereUsed)) {
			if (is_array($this->whereUsed)) {
				if (!isset($this->whereUsed["table"])) {
					throw \Exception("Specify table in $whereUsed");
				}
				if (!isset($this->whereUsed["column"])) {
					throw \Exception("Specify column in $whereUsed");
				}
				$whereUsedOk = TRUE;
			}
			else {
				throw \Exception("$whereUsed is not an array.");
			}
		}

		if ($this->withCount == TRUE AND !isset($whereUsedOk)) {
			throw \Exception("Set $whereUsed first.");
		}

		if ($this->activeConvert == TRUE AND !$this->withCount == TRUE) {
			throw \Exception("Enable $withCount first.");
		}
	}

}