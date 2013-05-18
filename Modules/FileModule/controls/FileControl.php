<?php

namespace FileModule\Controls;

use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Control;
use Schmutzka\Utils\Filer;
use Nette;

class FileControl extends Control
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;


	public function createComponentSingleForm()
	{
		$form = new Form;
		$form->addText("name", "Jméno:")
			->addRule(Form::FILLED, "Zadejte jméno");
		$form->addUpload("file", "Soubor:");

		if ($this->moduleParams->attachToUser) {
			$this->addSelect("user_id", "Patří k uživateli:", $this->userModel->fetchPairs("id", "name", array("role" => "user")))
				->setPrompt("Vyberte")
				->addRule(Form::FILLED, "Vyberte uživatele");
		}

		$this->addTextarea("note", "Poznámka:");
		$this->addTextarea("last_report_included", "Poslední zahrnuté hlášení:");
		$this->addSubmit("send", "Uložit");
	}


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->id = $presenter->id) {
			$defaults = $this->fileModel->item($this->id);
			$this["form"]->setDefaults($defaults);

		} else {
			$this["form"]->addRule(Form::FILLED, "Vyberte soubor");
		}
	}


	public function processSingleForm($form)
	{
		$values = $form->values;
		$file = $values["file"];

		if ($file->isOk()) {
			$values["orig_name"] = $file->getName();
			$values["local_name"] = Filer::moveFile($file, "/data/upload_files/", TRUE, FALSE, FALSE, TRUE);
			$values["created"] = new Nette\DateTime;
		}
		unset($values["file"]);

		if ($this->id) {
			$this->fileModel->update($values, $this->id);

		} else {
			$this->fileModel->insert($values);
		}

		$this->presenter->flashMessage("Uloženo", "success");
		$this->presenter->redirect("this");
	}


	public function renderSingle()
	{
		parent::useTemplate("single");
		$this->template->render();
	}

}
