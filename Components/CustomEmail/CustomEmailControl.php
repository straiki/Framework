<?php

namespace Components;

use Schmutzka\Forms\Form;

class CustomEmailControl extends \Schmutzka\Application\UI\Control
{
	/** @var \Models\CustomEmail */
	private $model;

	/** @var array */
	private $langVersions = array();

	/** @var \Nette\DI\Container */
	public $context;

	/** @var string */
	public $codename;

	/** @var array */
	public $availableValues = array();


	public function __construct(\Nette\DI\Container $context)
	{
		parent::__construct();
		$this->context = $context;
		$this->model = $this->context->models->customEmail;
	}


	/**
	 * Email form
	 */
	protected function createComponentEmailForm()
	{
		$form = new Form;

		$form->addText("from_email", "From (email, name): *")
			->addRule(Form::FILLED, "Mandatory")
			->addRule(Form::EMAIL, "Email has not correct format");
		$form->addText("from_name", "Od");

		$form->addText("subject", "Subject: *")
			->setAttribute("class","span4")
			->addRule(Form::FILLED, "Mandatory");
		$form->addTextarea("body", "Email text:");

		foreach ($this->langVersions as $lang) {
			$form->addText("subject_" . $lang, "Subject ($lang):")
				->setAttribute("class","span4");
			$form->addTextarea("body_" . $lang, "Email text ($lang):");
		}

		$form->addSubmit("send", "Save");

		return $form;
	}


	/**	
	 * Process email
	 */
	public function emailFormSent(Form $form)
	{
		$values = $form->values;

		$this->model->update($values, array("codename" => $this->codename));
		$this->flashMessage("Uloženo.","flash-success");

		$this->redirect("this");
	}


	public function render()
	{
		if (empty($this->codename)) {
			throw new \Exception('$codename is not defined.');
		}	

		$item = $this->model->item(array("codename" => $this->codename));
		$this->template->item = $item;
		$this["emailForm"]->setDefaults($item);

		$this->template->langVersions = $this->langVersions;
		$this->template->availableValues = $this->availableValues;
		$this->template->subject = $this->model->fetchSingle("subject", array("codename" => $this->codename));
		$this->template->render();
	}


	/**
	 * Add lang version
	 * @param string
	 */
	public function addLangVersion($lang)
	{
		$this->langVersions[] = $lang;
	}

}