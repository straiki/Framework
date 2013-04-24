<?php

namespace Schmutzka\Components;

use Schmutzka\Forms\Form,
	Schmutzka\Structures\Tree;

class TreeCodebookControl extends \Schmutzka\Application\UI\Control
{

	/** @persistent */
	public $id;

	/** @persistent */
	public $ord = "rank";


	/** @var string */
	private $table;

	/** @var string */
	private $model;

	/** @var array */
	private $itemList;


	public function __construct($table, $model)
    {
        parent::__construct();

		$this->table = $table;
		$this->model = $model;
		$this->itemList = $this->model->getItemList();
	}


	/**
	 * Delete record
	 * @param int
	 */
	public function handleDelete($id)
	{
		if ($this->model->item(array("parent_id" => $id))) {
			$this->flashMessage("This record cannot be deleted","error");
		}
		else {
			$this->model->delete($id);
			$this->flashMessage("Deleted.","success");
		}

		$this->redirect("this", array("id" => NULL));
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

		$this["treeCodebookForm"]->setDefaults($this->model->item($id));
		$this->template->editing = TRUE;
	}


	/**
	 * Render default
	 */
	public function render($id = NULL)
	{
		if ($id) {
			$this->template->editing = TRUE;
		}

		$this["treeCodebookForm"]["parent_id"]->setItems($this->itemList);

		$this->template->itemList = $this->model->getItemList();
		$this->template->itemResult = $this->model->getListOrdered($this->ord);

		$tree = new Tree($this->template->itemResult);
		$this->template->structure = $tree->structure;

		$this->template->render();
	}


	/********************* component *********************/


	/**
	 * TreeCodebook form
	 */
	protected function createComponentTreeCodebookForm()
	{
		$form = new Form;
		$form->addSelect("parent_id", "Parent value")
			->setPrompt("None");

		$form->addText("name", "Value name")
			->addRule(Form::FILLED,"Mandatory");

		$form->addText("rank", "Rank")
			->setAttribute("class","span1")
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER,"Integer only");

		$form->addSubmit("send", "Save");

		return $form;
	}


	/**
	 * Form sent
	 */
	public function treeCodebookFormSent(Form $form)
	{
		$values = $form->values;

		try {
			$this->model->upsert($values, $this->id);
			$this->flashMessage("Saved.","success");
		}
		catch (\PDOException $e) {
			$this->flashMessage("This combination already exists.","error");
		}

		$this->redirect("this", array("id" => NULL));
	}

}