<?php

namespace EventModule\Forms;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Forms\ModuleForm;
use Schmutzka\Appliaction\UI\Form;
use Schmutzka\Utils\Filer;

class EventForm extends ModuleForm
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\Event */
	public $eventModel;

	/** @inject @var Schmutzka\Models\EventCategory */
	public $eventCategoryModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @var filepath */
	private $folder = "upload/event/";


	public function build()
    {
		parent::build();

		$this->addText("title","Název akce:")
			->addRule(Form::FILLED, "Povinné");

		if ($categoryList = $this->eventCategoryModel->fetchPairs("id", "name")) {
			$this->addSelect("event_category_id","Kategorie:", $categoryList)
				->setPrompt("Vyberte")
				->addRule(Form::FILLED, "Povinné");
		}

		$this->addDatepicker("date","Datum akce:")
			->addRule(Form::FILLED, "Povinné")
			->addRule(Form::DATE, "Čas nemá správný formát");

		$this->addText("time","Čas akce:")
			->addCondition(Form::FILLED)
				->addRule(Form::TIME, "Čas nemá správný formát");

		$this->addUpload("image", "Obrázek:");

		$this->addTextarea("content","Obsah:")
			->addRule(Form::FILLED, "Povinné")
			->setAttribute("class","tinymce");

		if ($this->paramService->params["cmsParams"]["event_module_enable_gallery_link"] && $galleryList = $this->galleryModel->fetchPairs("id", "name")) {
			$this->addSelect("gallery_id", "Propojit s galerií:", $galleryList)
				->setPrompt("Bez galerie");
		}

		if ($this->paramService->params["cmsParams"]["event_module_enable_calendar"]) {
			$this->addCheckbox("display_in_calendar", "Zobrazit v kalendáři")
			->setDefaultValue(1);
		}

		if ($this->paramService->params["cmsParams"]["event_module_enable_news"]) {
			$this->addCheckbox("is_news", "Je aktualita");
		}

		if ($this->paramService->params["cmsParams"]["event_module_enable_link"]) {
			$this->addText("link", "Odkaz (více):")
			->addCondition(Form::FILLED)
				->addRule(Form::URL, "Adresa nemá správný formát");
		}

		$this->addSubmit();

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->eventModel->item($this->id);
			$this->setDefaults($defaults);
		}
	}


	/**
	 * Process form
	 */
	public function process(Form $form)
	{
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->redirect("default", array("id" => NULL));
		}

		$values = $form->values;

		if (!$values["time"]) {
			$values["time"] = NULL;
		}

		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		$file = $values["image"];
		if ($file && $suffix = Filer::checkImage($file)) {

			$image = $file->toImage();
			$image->resize(110, 110, Nette\Image::EXACT);

			$values["image"] = $this->folder . Strings::webalize($file->getName()) . "." . $suffix;
			$image->save(WWW_DIR . "/" . $values["image"]);

		} else {
			unset($values["image"]);
		}

		if ($this->id) {
			$this->eventModel->update($values, $this->id);

		} else {
			$values["created"] = $values["edited"];
			$this->eventModel->insert($values);
		}

		$this->flashMessage("Uloženo.", "success");
		$this->redirect("default", array("id" => NULL));
	}

}