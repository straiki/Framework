<?php

namespace Schmutzka\Components;

use Nette\Application\UI\Control,
	Schmutzka\Forms\Form,
	Schmutzka\Templates\MyHelpers,
	Nette\Utils\Html,
	NetteMAE as MAE;

class Codebook extends Control
{
	/** @persistent */
	public $id;

	/** @var string */
	public $codeType;

	/** @var string */
	private $context;

	/** @var string */
	private $model;

	/** @var array */
	private $yesNo = array(
		1 => "ano",
		0 => "ne"
	);

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
	

	public function __construct($container, $tableName = "codebook")
    {
        parent::__construct();

		$this->context = $container;
		$this->model = $this->context->modelLoader->codebookControl; // direct acces to this model

		// check table existance
		$this->model->checkExistance($tableName);
   }


	/**
	 * Sets settings
	 * @param array
	 */
	public function setup($settings) 
	{	
		foreach($settings as $key => $value) {
			$this->{$key} = $value;
		}
	}


	/**
	 * Delete record
	 * @param int
	 */
	public function handleDelete($id)
	{
		if(!$id) {
			$this->redirect("this");
		}
		$this->model->delete($id);
		$this->flashMessage("Záznam smazán.","flash-success");

		if($this->getPresenter()->isAjax()) {
			$this->getPresenter()->invalidateControl("codebook");
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
		if(!$id) {
			$this->redirect("this");
		}
		$this->id = $id;

		$this["codebookForm"]->setDefaults($this->model->item($id));

		if($this->getPresenter()->isAjax()) {
			$this->getPresenter()->invalidateControl("codebook");
		}
	}


	/**
	 * Codebook form
	 */
	protected function createComponentCodebookForm()
	{
		$form = new Form;
		$form->addText("value", "Název hodnoty")
			->addRule(Form::FILLED,"Zadejte název hodnoty.");

		if($this->activeDisplay) {
			$form->addSelect("display", "Zobrazovat", $this->yesNo)
				->setDefaultValue(1);
		}

		if($this->activeRank) {
			$form->addText("rank", "Pořadí", 3)
				->setDefaultValue($this->model->getNextRank($this->codeType)); // predicted value
		}

		$form->addSubmit("send", "Uložit");
		$form->onSuccess[] = callback($this, "codebookFormSent");

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
		unset($values["send"]);

		if($this->id) {
			$this->model->update($values, $this->id);
			$this->flashMessage("Položka uložena.","flash-success");
		}
		else {
			$this->model->insert($values);
			$this->flashMessage("Položka přidána.","flash-success");
		}

		if($this->getPresenter()->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->getPresenter()->invalidateControl("codebook");
		}
		else {
			$this->redirect("this", array("id" => NULL));
		}
	}


	/**
	 * Setup template values
	 */
	private function setupValues()
	{
		// $this->template = $this->context->application->presenter->createTemplate(); // bug: doesn't include inner components
		$this->template->registerFilter(new \Nette\Templating\Filters\Haml);
		$this->template->registerFilter(new \Nette\Latte\Engine);
		$helpers = new MyHelpers($this->context, $this->context->application->presenter);
		$this->template->registerHelperLoader(array($helpers, 'loader'));

		$this->template->codeList = $this->model->getCodesByType($this->codeType, $this->withCount, $this->whereUsed); // FIX!
		$this->template->codeType = $this->codeType;
		$this->template->yesNo = $this->yesNo;

		// settingss
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

		$this->template->setFile(dirname(__FILE__) . "/render.latte");	
		$this->template->render();
	}


	/**
	 * Render sedibar
	 */
	public function renderSide()
	{
		$this->checkValues();
		$this->setupValues();

		$this->template->setFile(dirname(__FILE__) . "/renderSide.latte");	
		$this->template->render();
	}


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

		if($this->getPresenter()->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->getPresenter()->invalidateControl("codebook");
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

		$form->addSubmit("send","Nastavit")->setAttribute("class", "btn primary");
		$form->onSuccess[] = callback("self", "convertFormSent");

		return $form;
	}



	/** @broken
	 * Convert
	 * @form
	 */
	public function convertFormSent(Form $form)
	{
		$values = $form->values;
		dd($values);


		$this->model->convert($columnName, $this->item["id"], $values->newItem);

		$this->flashMessage("Úspěšně změněno.","pos");
		if($this->getPresenter()->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->getPresenter()->invalidateControl("codebook");
		}
		else {
			$this->redirect("this", array("id" => NULL));
		}
	}



	/**
	 * Checks variables 
	 */
	private function checkValues()
	{
		if(!isset($this->codeType)) {
			throw MAE("Missing parameter $codeType");
		}

		if(isset($this->whereUsed)) {
			if(is_array($this->whereUsed)) {
				if(!isset($this->whereUsed["table"])) {
					throw MAE("Specify table in $whereUsed");
				}
				if(!isset($this->whereUsed["column"])) {
					throw MAE("Specify column in $whereUsed");
				}
				$whereUsedOk = TRUE;
			}
			else {
				throw MAE("$whereUsed is not an array.");
			}
		}

		if($this->withCount == TRUE AND !isset($whereUsedOk)) {
			throw MAE("Set $whereUsed first.");			
		}

		if($this->activeConvert == TRUE AND !$this->withCount == TRUE) {
			throw MAE("Enable $withCount first.");			
		}
	}

}