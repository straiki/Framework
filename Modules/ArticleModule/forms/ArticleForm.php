<?php

namespace ArticleModule\Forms;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Forms\ModuleForm;
use Schmutzka\Utils\Filer;

class ArticleForm extends ModuleForm
{
	/** @persistent */
	public $id;

	/** @var string @persistent */
	public $type;

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


	public function build()
	{
		parent::build();

		$this->addGroup("");
		$this->addText("title", "Název stránky:")
			->addRule(Form::FILLED, "Povinné");

		if ($this->moduleParams["categories"]) {
			if ($this->moduleParams["categories_multi"]) {
				$this->addMultiSelect("article_category_id", "Kategorie:")
					->setAttribute("data-placeholder","Vyberte jednu či více kategorií")
					->setAttribute("class","chosen width400");

			} else {
				$this->addSelect("article_category_id", "Kategorie:")
					->setPrompt("Vyberte kategorii");
			}

			$categoryList = $this->ArticleCategoryModel->fetchPairs("id", "name");
			$this["article_category_id"]->setItems($categoryList);
		}

		if ($this->moduleParams["show_in_sliderbox"]) {
			$this->addCheckBox("article_show_in_sliderbox", "Zobrazit ve SliderBoxu");
		}

		$this->addGroup("Publikování");

		if (isset($this->moduleParams["publish_datetime"]) && $this->moduleParams["publish_datetime"]) {
			$this->addDateTimePicker("publish_datetime", "Čas publikování:");
		}

		if ($this->moduleParams["publish_state"]) {
			$array = array(
				"concept" => "Koncept",
				"pending" => "Čekající na schválení",
				"public" => "Publikován"
			);
			$this->addSelect("publish_state", "Stav publikování:", $array);
		}

		if ($this->moduleParams["access_to_roles"]) {
			$roles = $this->paramService->cmsSetup->modules->user->roles;
			$this->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", (array) $roles)
				->setAttribute("data-placeholder","Zde můžete omezit zobrazení pouze pro určité uživatele")
				->setAttribute("class","chosen width400");
		}

		if ($this->moduleParams["custom_url"]) {
			$this->addText("url", "Url adresa:")
				->setOption("description","Bude automaticky vygenerována z názvu.");
		}

		// $this->addUpload("image", "Obrázek:"); - 1 možnost narhát, 2 vybrat z galeire - 2DO při modulu galerie

		$this->addGroup("Obsah")
			->setOption("description", Html::el('div')->setHtml("[page:5:Odkaz na stránku s id = 5], [article:5:Odkaz na článek s id = 5]"));

		if ($this->moduleParams["perex_short"]) {
			$this->addTextarea("perex_short", "Perex (kratší):")
				->setOption("description", "Krátké shrnutí stránky zobrazující se např. pod nadpisem.")
				->setAttribute("class", "tinymce");
		}

		if ($this->moduleParams["perex_long"]) {
			$this->addTextarea("perex_long", "Perex (delší):")
				->setOption("description", "Shrnutí obsahu zobrazující se např. ve výpisu článků.")
				->setAttribute("class", "tinymce");
		}

		$this->addTextarea("content","Obsah:", NULL, 30)
			->setAttribute("class", "tinymce");


		if ($this->moduleParams["attachment_gallery"] || $this->moduleParams["attachment_files"]) {
			$this->addGroup("Přílohy");

			if ($this->moduleParams["attachment_gallery"]) {
				$galleryList = $this->galleryModel->fetchPairs("id", "name");
				$this->addSelect("gallery_id", "Připojená galerie", $galleryList)
					->setPrompt($galleryList ? "Vyberte" : "Zatím neexistuje žádná fotogalerie");
			}

			if ($this->moduleParams["attachment_files"]) { // typy?
				$this->addUpload("attachment_1", "Příloha 1:");
				$this->addUpload("attachment_2", "Příloha 2:");
				$this->addUpload("attachment_3", "Příloha 3:");
			}
		}

		$this->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $this;
	}


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->id = $presenter->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->articleModel->item($this->id);
			if ($this->moduleParams["categories"] && $this->moduleParams["categories_multi"]) {
				$categoryKey = "article_category_id";
				$defaults[$categoryKey] = $this->ArticleInCategoryModel->fetchPairs($categoryKey, $categoryKey, array("article_id" => $this->id));
			}

			if ($this->moduleParams["access_to_roles"]) {
				$defaults["access_to_roles"] = unserialize($defaults["access_to_roles"]);
			}

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
		$values["url"] = $this->getUrl($values["title"]);
		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		// upload attachments
		$attachments = array();
		if ($this->moduleParams["attachment_files"]) {
			$attachments[] = $values["attachment_1"];
			$attachments[] = $values["attachment_2"];
			$attachments[] = $values["attachment_3"];
			unset($values["attachment_1"], $values["attachment_2"], $values["attachment_3"]);
		}

		// multi categories
		if ($this->moduleParams["categories"] && $this->moduleParams["categories_multi"]) {
			$articleInCategory = $values["article_category_id"];
			unset($values["article_category_id"]);
		}

		if ($this->moduleParams["access_to_roles"]) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		if ($this->id) {
			$this->articleModel->update($values, $this->id);

		} else {
			$values["created"] = $values["edited"];
			$id = $this->articleModel->insert($values);
			$this->id = $id;
		}

		// multi categories
		if ($this->moduleParams["categories"] && $this->moduleParams["categories_multi"]) {
			$this->articleInCategoryModel->saveCategoryToArticle($this->id, $articleInCategory);
		}

		if ($this->moduleParams["content_history"]) {
			$array = array(
				"content" => $values["content"],
				"article_id" => $this->id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);
			$this->ArticleContentModel->insert($array);
		}

		if ($this->moduleParams["attachment_files"]) {
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

		$this->presenter->flashMessage("Uloženo.","success");
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
