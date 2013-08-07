<?php

namespace CodebookModule\Components;

use Nette\Utils\Html;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;
use Schmutzka\Templates\MyHelpers;


/**
 * @method setActiveDisplay(bool)
 * @method setActiveRank(bool)
 * @method setWhereUsed(bool)
 * @method setDisplayCount(bool)
 * @method setActiveConvert(bool)
 */
class CodebookControl extends Control
{
	/** @var bool */
	private $activeDisplay = TRUE;

	/** @var bool */
	private $activeRank = TRUE;

	/** @var bool */
	private $whereUsed = FALSE;

	/** @var bool */
	private $displayCount = FALSE;

	/** @var bool */
	private $activeConvert = FALSE;

	/** @var  bool */
	private $codeType;

	/** @var string */
	private $model;


	public function __construct($codeType, $model)
    {
        parent::__construct();
		$this->codeType = $codeType;
		$this->model = $model;
   }


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText('value', 'Název hodnoty')
			->addRule(Form::FILLED, 'Zadejte název hodnoty');

		if ($this->activeDisplay) {
			$form->addCheckbox('display', 'Zobrazit');
		}

		if ($this->activeRank) {
			$form->addText('rank', 'rank', 3)
				->setDefaultValue($this->model->getNextRank($this->codeType)); // predicted value
		}

		$form->addSubmit('send', 'Save');

		return $form;
	}


	/**
	 * Sent codebook form
	 * @form
	 */
	public function codebookFormSent(Form $form)
	{
		$values = $form->values;
		$values['type'] = $this->codeType;

		$this->model->upsert($values, $this->id);
		$this->flashMessage('Saved.','flash-success');


		if($this->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->invalidateControl('codebook');
		}
		else {
			$this->redirect('this', array('id' => NULL));
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

		// settings
		$this->template->activeDisplay = $this->activeDisplay;
		$this->template->activeRank = $this->activeRank;

		// using table
		$this->template->whereUsed = $this->whereUsed;
		$this->template->withCount = $this->withCount;
		$this->template->activeConvert = $this->activeConvert;
	}


	public function renderDefault()
	{
		$this->checkValues();
		$this->setupValues();
	}


	public function renderSide()
	{
		$this->checkValues();
		$this->setupValues();
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
			$this->flashMessage('Neexistuje žádná alternativní položka. Musíte ji nejdříve vytvořit.','flash-error');
		}
		else {
			$this->template->showConverForm = TRUE;
			$this['convertForm']['newItem']->setItems($options)
				->setPrompt('Vyberte');
		}

		if($this->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->invalidateControl('codebook');
		}
		else {
			// $this->redirect('this', array('id' => NULL));
		}
	}


	/********************** convert form **********************/


	protected function createComponentConvertForm()
	{
		$form = new Form;
		$form->addSelect('newItem', 'Nová položka:')
			->addRule(Form::FILLED, 'Vyberte novou možnost.');

		$form->addSubmit('send','Nastavit');

		return $form;
	}


	public function convertFormSent(Form $form)
	{
		$values = $form->values;

		$this->model->convert($columnName, $this->item['id'], $values->newItem);

		$this->flashMessage('Úspěšně změněno.','pos');
		if($this->isAjax()) {
			$form->setValues(array(), TRUE);
			$this->invalidateControl('codebook');
		}
		else {
			$this->redirect('this', array('id' => NULL));
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
				if (!isset($this->whereUsed['table'])) {
					throw \Exception('Specify table in $whereUsed');
				}
				if (!isset($this->whereUsed['column'])) {
					throw \Exception('Specify column in $whereUsed');
				}
				$whereUsedOk = TRUE;
			}
			else {
				throw \Exception('$whereUsed is not an array.');
			}
		}

		if ($this->withCount == TRUE AND !isset($whereUsedOk)) {
			throw \Exception('Set $whereUsed first.');
		}

		if ($this->activeConvert == TRUE AND !$this->withCount == TRUE) {
			throw \Exception('Enable $withCount first.');
		}
	}

}
