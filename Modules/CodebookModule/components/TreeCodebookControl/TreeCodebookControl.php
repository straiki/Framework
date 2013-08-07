<?php

namespace CodebookModule\Components;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class TreeCodebookControl extends Control
{
	/** @inject @var Schmutzka\Models\TreeCodebook */
	public $treeCodebookModel;

	/** @persistent @var string */
	public $ord = 'rank';

	/** @var array */
	private $itemList;


	public function __construct()
    {
		$this->itemList = $this->model->getItemList();
	}


	/**
	 * Delete record
	 * @param int
	 */
	public function handleDelete($id)
	{
		if ($this->model->item(array('parent_id' => $id))) {
			$this->flashMessage('This record cannot be deleted','error');
		}
		else {
			$this->model->delete($id);
			$this->flashMessage('Deleted.','success');
		}

		$this->redirect('this', array('id' => NULL));
	}


	/**
	 * Edit record
	 * @param int
	 */
	public function handleEdit($id)
	{
		$this->itemList = $this->model->getItemList();
		unset($this->itemList[$id]);
		// 2DO: exclude all children

		$this['treeCodebookForm']->setDefaults($this->model->item($id));
		$this->template->editing = TRUE;
	}


	/**
	 * @param  int
	 */
	public function renderDefault($id = NULL)
	{
		if ($id) {
			$this->template->editing = TRUE;
		}

		$this['treeCodebookForm']['parent_id']->setItems($this->itemList);

		$this->template->itemList = $this->model->getItemList();
		$this->template->itemResult = $this->model->getListOrdered($this->ord);

		$tree = new Tree($this->template->itemResult);
		$this->template->structure = $tree->structure;
	}


	/********************* component *********************/


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addSelect('parent_id', 'Parent value')
			->setPrompt('None');

		$form->addText('name', 'Value name')
			->addRule(Form::FILLED,'Mandatory');

		$form->addText('rank', 'Rank')
			->setAttribute('class','span1')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER,
					'Integer only');

		$form->addSubmit('send', 'Save');

		return $form;
	}


	public function processForm(Form $form)
	{
		$values = $form->values;

		try {
			$this->model->upsert($values, $this->id);
			$this->flashMessage('Saved.','success');
		}
		catch (\PDOException $e) {
			$this->flashMessage('This combination already exists.','error');
		}

		$this->redirect('this', array('id' => NULL));
	}

}
