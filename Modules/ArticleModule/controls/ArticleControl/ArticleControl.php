<?php

namespace ArticleModule\Controls;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Schmutzka;
use Schmutzka\Application\UI\Module\Control;
use Schmutzka\Application\UI\Form;
use Schmutzka\Utils\Filer;

class ArticleControl extends Control
{
	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleContent */
	public $articleContentModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;

	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;


	public function createComponentForm()
	{
		$form = new Form;

		$form->addGroup("");
		$form->addText("title", "Nadpis článku:")
			->addRule(Form::FILLED, "Zadejte nadpis článku");

		if ($this->moduleParams->categories) {
			if ($this->moduleParams->categories_multi) {
				$form->addMultiSelect("article_category_id", "Kategorie:")
					->setAttribute("data-placeholder", "Vyberte jednu či více kategorií")
					->setAttribute("class", "chosen width400");

			} else {
				$form->addSelect("article_category_id", "Kategorie:")
					->setPrompt("Vyberte kategorii");
			}

			$categoryList = $this->articleCategoryModel->fetchPairs("id", "name");
			$form["article_category_id"]->setItems($categoryList);
		}

		if ($this->moduleParams->show_in_sliderbox) {
			$form->addCheckBox("article_show_in_sliderbox", "Zobrazit ve SliderBoxu");
		}

		$form->addGroup("Publikování");
		if ($this->moduleParams->custom_author_name) {
			$form->addText("custom_author_name", "Jméno autora (přepíše editora článku):");
		}

		if ($this->moduleParams->publish_datetime) {
			$form->addDateTimePicker("publish_datetime", "Čas publikování:")
				->setDefaultValue(new Nette\DateTime)
				->addRule(Form::FILLED, "Zadejte čas publikování");
		}

		if ($this->moduleParams->publish_state) {
			$publishTypes = (array) $this->moduleParams->publish_types;
			$form->addSelect("publish_state", "Stav publikování:", $publishTypes);
		}

		if ($this->moduleParams->access_to_roles) {
			$roles = (array) $this->paramService->cmsSetup->modules->user->roles;
			$form->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", $roles)
				->setAttribute("data-placeholder", "Zde můžete omezit zobrazení pouze pro určité uživatele")
				->setAttribute("class", "chosen width400");
		}

		if ($this->moduleParams->custom_url) {
			$form->addText("url", "Url adresa:")
				->setOption("description", "Bude automaticky vygenerována z názvu.")
				->setAttribute("class", "span6");
		}

 		$form->addGroup("Obsah")
			->setOption("description", Html::el('div')->setHtml("[page:5:Odkaz na stránku s id = 5], [article:5:Odkaz na článek s id = 5]"));

		if ($this->moduleParams->perex_short) {
			$form->addTextarea("perex_short", "Perex (kratší):")
				->setOption("description", "Krátké shrnutí stránky zobrazující se např. pod nadpisem.")
				->setAttribute("class", "ckeditor");
		}

		if ($this->moduleParams->perex_long) {
			$form->addTextarea("perex_long", "Perex (delší):")
				->setOption("description", "Shrnutí obsahu zobrazující se např. ve výpisu článků.")
				->setAttribute("class", "ckeditor");
		}

		$form->addTextarea("content", "Obsah:", NULL, 30)
			->setAttribute("class", "ckeditor");

		if ($this->moduleParams->attachment_gallery || $this->moduleParams->attachment_files) {
			$form->addGroup("Přílohy");

			if ($this->moduleParams->attachment_gallery) {
				$galleryList = $this->galleryModel->fetchPairs("id", "name");
				$form->addSelect("gallery_id", "Připojená galerie", $galleryList)
					->setPrompt($galleryList ? "Vyberte" : "Zatím neexistuje žádná fotogalerie");
			}

			if ($this->moduleParams->attachment_files) { // typy?
				$form->addUpload("attachment_1", "Příloha 1:");
				$form->addUpload("attachment_2", "Příloha 2:");
				$form->addUpload("attachment_3", "Příloha 3:");
			}
		}

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
			if ($this->moduleParams->categories && $this->moduleParams->categories_multi) {
				$categoryKey = "article_category_id";
				$defaults[$categoryKey] = $this->articleInCategoryModel->fetchPairs($categoryKey, $categoryKey, array(
					"article_id" => $this->id
				));

				if ($this->moduleParams->qr) {
					$defaults["qr"] = $this->qrModel->fetchSingle("id", array("article_id" => $this->id));
				}
			}

			if ($this->moduleParams->access_to_roles) {
				$defaults["access_to_roles"] = unserialize($defaults["access_to_roles"]);
			}

			$this["form"]->setDefaults($defaults);
		}
	}


	public function processForm($form)
	{
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->redirect("default", array("id" => NULL));
		}

		$values = $form->values;
		$values["url"] = $this->getUrl($values["title"]);
		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		if (!isset($values["publish_datetime"])) {
			$values["publish_datetime"] = new Nette\DateTime;
		}

		// upload attachments
		$attachments = array();
		if ($this->moduleParams->attachment_files) {
			$attachments[] = $values["attachment_1"];
			$attachments[] = $values["attachment_2"];
			$attachments[] = $values["attachment_3"];
			unset($values["attachment_1"], $values["attachment_2"], $values["attachment_3"]);
		}

		// multi categories
		if ($this->moduleParams->categories && $this->moduleParams->categories_multi) {
			$articleInCategory = $values["article_category_id"];
			unset($values["article_category_id"]);
		}

		if ($this->moduleParams->access_to_roles) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		if ($this->moduleParams->qr) {
			if ($values["qr"]) {
				$this->qrModel->update(array("article_id" => $this->id), $values["qr"]);
			}
			unset($values["qr"]);
		}

		if ($this->id) {
			$this->articleModel->update($values, $this->id);

		} else {
			$values["created"] = $values["edited"];
			$id = $this->articleModel->insert($values);
			$this->id = $id;
		}

		// multi categories
		if ($this->moduleParams->categories && $this->moduleParams->categories_multi) {
			$this->articleInCategoryModel->saveCategoryToArticle($this->id, $articleInCategory);
		}

		if ($this->moduleParams->content_history) {
			$array = array(
				"content" => $values["content"],
				"article_id" => $this->id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);
			$this->articleContentModel->insert($array);
		}

		if ($this->moduleParams->attachment_files) {
			foreach ($attachments as $file) {
				if ($file->isOk()) {
					$array["name_origin"] = $file->getName();
					$array["suffix"] = Filer::extension($file->getName());
					$array["name"] = Filer::moveFile($file, "/data/file/", TRUE, FALSE, FALSE, TRUE);
					$array["key_id"] = $this->id;
					$array["user_id"] = $this->user->id;
					$array["created"] = new Nette\DateTime;
					$array["type"] = "article_attachment";
					$this->fileModel->insert($array);
				}
			}
		}

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect("edit", array("id" => $this->id));
	}


	/********************** helpers **********************/


	/**
	 * Get unique slug
	 * @param string
	 */
	private function getUrl($name)
	{
		$url = $originUrl = Strings::webalize($name);
		$i = 1;

		while ($item = $this->articleModel->item(array("url" => $url))) {
			if ($item["id"] == $this->id) {
				return $url;
			}

			$url = $originUrl . "-". $i;
			$i++;
		}

		return $url;
	}

}
