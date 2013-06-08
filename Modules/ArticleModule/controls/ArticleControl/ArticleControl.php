<?php

namespace ArticleModule\Controls;

use Nette;
use Nette\Utils\Html;
use Schmutzka;
use Schmutzka\Application\UI\Module\TextControl;
use Schmutzka\Application\UI\Form;

class ArticleControl extends TextControl
{
	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleContent */
	public $articleContentModel;

	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;

	/** @var array */
	private $articleInCategory;


	public function createComponentForm()
	{
		$form = new Form;
		$form->addGroup("");
		$form->addText("title", "Nadpis článku:")
			->addRule(Form::FILLED, "Zadejte nadpis článku");

		if ($this->moduleParams->categories) {
			if ($this->moduleParams->categoriesMulti) {
				$form->addMultiSelect("article_category_id", "Kategorie:")
					->setAttribute("data-placeholder", "Vyberte jednu či více kategorií")
					->setAttribute("class", "chosen width400")
					->addRule(Form::FILLED, "Vyberte aspoň jednu kategorii");

			} else {
				$form->addSelect("article_category_id", "Kategorie:")
					->setPrompt("Vyberte kategorii")
					->addRule(Form::FILLED, "Vyberte kategorii");
			}

			$categoryList = $this->articleCategoryModel->fetchPairs("id", "name");
			$form["article_category_id"]->setItems($categoryList);
		}

		if ($this->moduleParams->showInSliderbox) {
			$form->addCheckBox("article_show_in_sliderbox", "Zobrazit ve SliderBoxu");
		}

		if ($this->moduleParams->customAuthorName || $this->moduleParams->publishState || $this->moduleParams->accessToRoles || $this->moduleParams->customUrl) {
			$form->addGroup("Publikování");
			if ($this->moduleParams->customAuthorName) {
				$form->addText("custom_author_name", "Jméno autora (přepíše editora článku):");
			}

			if ($this->moduleParams->publishDatetime) {
				$form->addDateTimePicker("publish_datetime", "Čas publikování:")
					->setDefaultValue(new Nette\DateTime)
					->addRule(Form::FILLED, "Zadejte čas publikování");
			}

			if ($this->moduleParams->publishState) {
				$publishTypes = (array) $this->moduleParams->publishTypes;
				$form->addSelect("publish_state", "Stav publikování:", $publishTypes);
			}

			if ($this->moduleParams->accessToRoles) {
				$roles = (array) $this->paramService->cmsSetup->modules->user->roles;
				$form->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", $roles)
					->setAttribute("data-placeholder", "Zde můžete omezit zobrazení pouze pro určité uživatele")
					->setAttribute("class", "chosen width400");
			}

			$form = $this->addFormCustomUrl($form);
		}

 		$form->addGroup("Obsah")
			->setOption("description", Html::el('div')->setHtml("[page:5:Odkaz na stránku s id = 5], [article:5:Odkaz na článek s id = 5]"));

		if ($this->moduleParams->perexShort) {
			$form->addTextarea("perex_short", "Perex (kratší):")
				->setOption("description", "Krátké shrnutí stránky zobrazující se např. pod nadpisem.")
				->setAttribute("class", "ckeditor");
		}

		if ($this->moduleParams->perexLong) {
			$form->addTextarea("perex_long", "Perex (delší):")
				->setOption("description", "Shrnutí obsahu zobrazující se např. ve výpisu článků.")
				->setAttribute("class", "ckeditor");
		}

		$form->addTextarea("content", "Obsah:")
			->setAttribute("class", "ckeditor");

		$form = $this->addFormAttachments($form);

		if ($this->moduleParams->qr) {
			$cond = array("article_id IS NULL OR article_id = ?" => $this->id);
			$qrList = $this->qrModel->fetchPairs("id", "alias", $cond);
			if ($qrList) {
				$form->addSelect("qr", "QR kód:", $qrList)
					->setPrompt("Vyberte");
			}
		}

		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->id = $presenter->id) {
			$defaults = $this->articleModel->item($this->id);
			if ($this->moduleParams->categories && $this->moduleParams->categoriesMulti) {
				$categoryKey = "article_category_id";
				$defaults[$categoryKey] = $this->articleInCategoryModel->fetchPairs($categoryKey, $categoryKey, array(
					"article_id" => $this->id
				));
			}

			if ($this->moduleParams->qr) {
				$defaults["qr"] = $this->qrModel->fetchSingle("id", array("article_id" => $this->id));
			}

			if ($this->moduleParams->accessToRoles) {
				$defaults["access_to_roles"] = unserialize($defaults["access_to_roles"]);
			}

			$this["form"]->setDefaults($defaults);
		}
	}

	public function preProcessValues($values)
	{
		$values = parent::preProcessValues($values);

		if ($this->moduleParams->categories && $this->moduleParams->categoriesMulti) {
			$this->articleInCategory = $values["article_category_id"];
			unset($values["article_category_id"]);
		}

		if ($this->moduleParams->accessToRoles) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		if ($this->moduleParams->qr) {
			if ($values["qr"]) {
				$this->qrModel->update(array("article_id" => $this->id), $values["qr"]);
			}
			unset($values["qr"]);
		}

		return $values;
	}


	public function postProcessValues($values, $id)
	{
		if ($this->moduleParams->categories && $this->moduleParams->categoriesMulti) {
			$this->articleInCategoryModel->saveCategoryToArticle($id, $this->articleInCategory);
		}

		if ($this->moduleParams->contentHistory) {
			$array = array(
				"content" => $values["content"],
				"article_id" => $id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);

			$this->articleContentModel->insert($array);
		}

		$this->postProcessFormSaveAttachments($this->id, "article");
	}


	public function render()
	{
		parent::useTemplate();
		$this->loadTemplateValues("page");
		$this->template->render();
	}

}
