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

		$form->addGroup("Publikování");
		if (isset($this->moduleParams->publish_datetime) && $this->moduleParams->publish_datetime) {
			$form->addDateTimePicker("publish_datetime", "Čas publikování:");
		}

		if ($this->moduleParams->publish_state) {
			$publishTypes = (array) $this->moduleParams->publish_types;
			$form->addSelect("publish_state", "Stav publikování:", $publishTypes);
		}

		if ($this->moduleParams->access_to_roles) {
			$roles = $this->paramService->cmsSetup->modules->user->roles;
			$form->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", (array) $roles)
				->setAttribute("data-placeholder", "Zde můžete omezit zobrazení pouze pro určité uživatele")
				->setAttribute("class", "chosen width400");
		}

		if ($this->moduleParams->custom_url) {
			$form->addText("url", "Url adresa:")
				->setOption("description", "Bude automaticky vygenerována z názvu.")
				->setAttribute("class", "span6");
		}

		if ($this->moduleParams->uid) {
			$form->addText("uid", "UID:");
		}


		$form->addGroup("Obsah")
			->setOption("description",
				Html::el('div')->setHtml("[page:5:Odkaz na stránku s id = 5], [article:5:Odkaz na článek s id = 5]")
			);

		if ($this->moduleParams->perex_short) {
			$form->addTextArea("perex_short", "Perex (kratší):")
				->setOption("description", "Krátké shrnutí stránky zobrazující se např. pod nadpisem.")
				->setAttribute("class", "ckeditor");
		}

		if ($this->moduleParams->perex_long) {
			$form->addTextArea("perex_long", "Perex (delší):")
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

		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->id = $presenter->id) {
			$defaults = $this->pageModel->item($this->id);
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

		// upload attachments
		$attachments = array();
		if ($this->moduleParams->attachment_files) {
			$attachments[] = $values["attachment_1"];
			$attachments[] = $values["attachment_2"];
			$attachments[] = $values["attachment_3"];
			unset($values["attachment_1"], $values["attachment_2"], $values["attachment_3"]);
		}

		if ($this->moduleParams->access_to_roles) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		if ($this->id) {
			$this->pageModel->update($values, $this->id);

		} else {
			$values["created"] = $values["edited"];
			$id = $this->pageModel->insert($values);
			$this->id = $id;
		}

		if ($this->moduleParams->content_history) {
			$array = array(
				"content" => $values["content"],
				"page_id" => $this->id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);
			$this->pageContentModel->insert($array);
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
					$array["type"] = "page_attachment";
					$this->fileModel->insert($array);
				}
			}
		}

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect("edit", array("id" => $this->id));
	}


	public function render()
	{
		parent::useTemplate();

		if ($this->id) {
			if ($this->moduleParams->content_history) {
				$this->template->contentHistory = $this->pageContentModel->fetchAll(array("page_id" => $this->id))
					->select("user.login login, page_content.*")->order("edited DESC");
			}

			if ($this->moduleParams->attachment_files) {
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
