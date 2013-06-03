<?php

namespace PageModule\Controls;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;
use Schmutzka\Utils\Filer;

class PageControl extends Control
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageContent */
	public $pageContentModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;

	/** @var array */
	private $attachments = array();


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

		if ($this->moduleParams->show_in_sliderbox) {
			$form->addCheckBox("page_show_in_sliderbox", "Zobrazit ve SliderBoxu");
		}

		if ($this->moduleParams->publishState || $this->moduleParams->accessToRoles) {
			$form->addGroup("Publikování");
			if ($this->moduleParams->publishState) {
				$publishTypes = (array) $this->moduleParams->publishTypes;
				$form->addSelect("publish_state", "Stav publikování:", $publishTypes);
			}

			if ($this->moduleParams->accessToRoles) {
				$roles = $this->paramService->cmsSetup->modules->user->roles;
				$form->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", (array) $roles)
					->setAttribute("data-placeholder", "Zde můžete omezit zobrazení pouze pro určité uživatele")
					->setAttribute("class", "chosen width400");
			}
		}

		if ($this->moduleParams->customUrl) {
			$form->addText("slug", "Url adresa:")
				->setOption("description", "Bude automaticky vygenerována z názvu.")
				->setAttribute("class", "span6");
		}

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

		$form->addTextarea("content", "Obsah:")
			->setAttribute("class", "ckeditor");

		if ($this->moduleParams->attachmentGallery || $this->moduleParams->attachment_files) {
			$form->addGroup("Přílohy");

			if ($this->moduleParams->attachmentGallery) {
				$galleryList = $this->galleryModel->fetchPairs("id", "name");
				$form->addSelect("gallery_id", "Připojená galerie", $galleryList)
					->setPrompt($galleryList ? "Vyberte" : "Zatím neexistuje žádná fotogalerie");
			}

			if ($this->moduleParams->attachmentFiles) {
				$form->addUpload("attachment_1", "Příloha 1:");
				$form->addUpload("attachment_2", "Příloha 2:");
				$form->addUpload("attachment_3", "Příloha 3:");
			}
		}

		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function preDefaults($defaults)
	{
		if ($this->moduleParams->accessToRoles) {
			$defaults["access_to_roles"] = unserialize($defaults["access_to_roles"]);
		}

		return $defaults;
	}


	public function preProcessValues($values)
	{
		if ((isset($values["slug"]) && $values["slug"] == NULL) || !isset($values["slug"])) {
			$values["slug"] = $this->getUrl($values["title"]);
		}

		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		if ($this->id == NULL) {
			$values["created"] = $values["edited"];
		}

		if ($this->moduleParams->attachmentFiles) {
			$this->attachments = array();
			$this->attachments[] = $values["attachment_1"];
			$this->attachments[] = $values["attachment_2"];
			$this->attachments[] = $values["attachment_3"];
			unset($values["attachment_1"], $values["attachment_2"], $values["attachment_3"]);
		}

		if ($this->moduleParams->accessToRoles) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

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

		if ($this->moduleParams->attachmentFiles) {
			foreach ($this->attachments as $file) {
				if ($file->isOk()) {
					$array["name_origin"] = $file->getName();
					$array["suffix"] = Filer::extension($file->getName());
					$array["name"] = Filer::moveFile($file, "/data/file/", TRUE, FALSE, FALSE, TRUE);
					$array["key_id"] = $this->id;
					$array["user_id"] = $this->user->id;
					$array["created"] = new Nette\DateTime;
					$array["type"] = "page_attachment";
					$this->fileModel->insert($array);
				}
			}
		}
	}


	public function render()
	{
		parent::useTemplate();

		if ($this->id) {
			if ($this->moduleParams->contentHistory) {
				$this->template->contentHistory = $this->pageContentModel->fetchAll(array("page_id" => $this->id))
					->select("user.login login, page_content.*")->order("edited DESC");
			}

			if ($this->moduleParams->attachmentFiles) {
				$this->template->attachmentFiles = $this->fileModel->fetchByType("page_attachment", $this->id);
			}
		}

		$this->template->render();
	}


	/********************** attachments **********************/


	/**
	 * Delete attachment
	 * @param int
	 */
	public function handleDeleteAttachment($attachmentId)
	{
		$filePath = WWW_DIR . $this->fileModel->fetchSingle("name", $attachmentId);
		if (file_exists($filePath)) {
			unlink($filePath);
		}
		$this->deleteHelper($this->fileModel, $attachmentId, FALSE);
		$this->redirect("this");
	}


	/**
	 * Open attachment
	 * @param int
	 */
	public function handleOpenAttachment($attachmentId)
	{
		$file = $this->fileModel->item($attachmentId);
		$filePath = WWW_DIR . $file["name"];
		Filer::downloadAs($filePath, $file["name_origin"]);
	}


	/**
	 * Load content version
	 * @param int
	 */
	public function handleLoadContentVersion($versionId)
	{
		$this["form"]["content"]->setValue($this->pageContentModel->fetchSingle("content", $versionId));
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

		while ($item = $this->pageModel->item(array("url" => $url))) {
			if ($item["id"] == $this->id) {
				return $url;
			}

			$url = $originUrl . "-". $i;
			$i++;
		}

		return $url;
	}

}
