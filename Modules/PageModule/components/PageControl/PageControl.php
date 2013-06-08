<?php

namespace PageModule\Controls;

use Nette;
use Nette\Utils\Html;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\TextControl;

class PageControl extends TextControl
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageContent */
	public $pageContentModel;


	/**
	 * Render page on front
	 * @param  string $uid page uid
	 * @param  bool  $displayTitle display page title
	 */
	public function renderDisplay($uid, $displayTitle = TRUE)
	{
		parent::useTemplate("display");
		$this->template->page = $this->pageModel->item(array("uid" => $uid));
		$this->template->displayTitle = $displayTitle;
		$this->template->render();
	}


	/********************** add/edit **********************/


	public function createComponentForm()
	{
		$form = new Form;
		$form->addGroup("");
		$form->addText("title", "Název stránky:")
			->addRule(Form::FILLED, "Zadejte název stránky");

		$form = $this->addFormCustomUrl($form);

		if ($this->moduleParams->uid) {
			$form->addText("uid", "UID:");
		}

		if ($this->moduleParams->linkToPageArticle || $this->moduleParams->perexShort || $this->moduleParams->perexLong) {
			$form->addGroup("Obsah");
			if ($this->moduleParams->linkToPageArticle) {
				$form["obsah"]->setOption("description",
					Html::el('div')->setHtml("[page:5:Odkaz na stránku s id = 5], [article:5:Odkaz na článek s id = 5]")
				);
			}

			if ($this->moduleParams->perexShort) {
				$form->addTextArea("perex_short", "Perex (kratší):")
					->setOption("description", "Krátké shrnutí stránky zobrazující se např. pod nadpisem.")
					->setAttribute("class", "ckeditor");
			}

			if ($this->moduleParams->perexLong) {
				$form->addTextArea("perex_long", "Perex (delší):")
					->setOption("description", "Shrnutí obsahu zobrazující se např. ve výpisu článků.")
					->setAttribute("class", "ckeditor");
			}
		}


		$form->addTextarea("content", "Obsah:")
			->setAttribute("class", "ckeditor");

		$form = $this->addFormAttachments($form);

		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function preProcessValues($values)
	{
		if ((isset($values["url"]) && $values["url"] == NULL) || !isset($values["url"])) {
			$values["url"] = $this->getUniqueUrl($values["title"]);
		}

		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		if ($this->id == NULL) {
			$values["created"] = $values["edited"];
		}

		$values = $this->preProcessFormStashAttachments($values);

		return $values;
	}


	public function postProcessValues($values, $id)
	{
		if ($this->moduleParams->contentHistory) {
			$array = array(
				"content" => $values["content"],
				"page_id" => $id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);

			$this->pageContentModel->insert($array);
		}

		$this->postProcessFormSaveAttachments($this->id, "page");
	}


	public function render()
	{
		parent::useTemplate();
		$this->loadTemplateValues("page");
		$this->template->render();
	}

}
