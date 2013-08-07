<?php

namespace Schmutzka\Forms;

use Schmutzka\Application\UI\Form;

class InsertUpdateForm extends  Form
{
	/** @persistent */
	public $id;

	/** @var string */
	public $flashText = 'Záznam byl úspěšně uložen.';

	/** @var string */
	public $redirectInsert = 'this';

	/** @var string */
	public $redirectUpdate = 'this';

	/** @var string */
	public $insertCaption = 'Přidat';

	/** @var string */
	public $updateCaption = 'Uložit';

	/** @var bool */
	public $nullId = TRUE;


	/** @persistent */
	private $userId;

	/** @var object */
	private $model;


	public function __construct($model, $id, $userId = NULL)
	{
		parent::__construct();
		$this->model = $model;
		$this->id = $id;
		$this->userId = $userId;
	}


	public function build()
	{
		parent::build();
		$this->addSubmit('send');

		if ($this->id) {
			$this['send']->caption = $this->updateCaption;
			$this->setDefaults($this->model->item($this->id));

		} else {
			$this['send']->caption = $this->insertCaption;
		}
	}


	public function process($form)
	{
		$values = $form->values;

		if ($this->userId) {
			$values['user_id'] = $this->userId;
		}

		if ($this->id) {
			$this->model->update($values, $this->id);
			$this->flashMessage($this->flashText, 'success');

			if ($this->nullId) {
				$this->id = NULL;
			}
			$this->redirect($this->redirectUpdate, array('id' => $this->id));

		} else {
			$this->model->insert($values);
			$this->flashMessage($this->flashText, 'success');
			$this->redirect($this->redirectInsert);
		}
	}

}