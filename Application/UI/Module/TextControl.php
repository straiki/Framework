<?php

namespace Schmutzka\Application\UI\Module;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Module\Control;
use Schmutzka\Application\UI\Form;
use Schmutzka\Utils\Filer;

abstract class TextControl extends Control
{
	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;

	/** @var array */
	protected $attachments = array();

	/** @var string (article|page) */
	protected $type;


	/********************** form parts **********************/


	/**
	 * @param Nette\Application\UI\Form
	 */
	protected function addFormPerexShort(Form $form)
	{
		if ($this->moduleParams->perexShort) {
			$form->addTextarea("perex_short", "Perex (kratší):")
				->setAttribute("class", "ckeditor");
		}
	}


	/**
	 * @param Nette\Application\UI\Form
	 */
	protected function addFormPerexLong(Form $form)
	{
		if ($this->moduleParams->perexLong) {
			$form->addTextarea("perex_short", "Perex (delší):")
				->setAttribute("class", "ckeditor");
		}
	}


	/**
	 * @param Nette\Application\UI\Form
	 */
	protected function addFormContent(Form $form)
	{
		$form->addTextarea("content", "Obsah:")
			->setAttribute("class", "ckeditor");
	}


	/**
	 * @param Nette\Application\UI\Form
	 */
	protected function addFormCustomUrl(Form $form)
	{
		if ($this->moduleParams->customUrl) {
			$form->addText("url", "Url adresa:")
				->setOption("description", "Bude automaticky vygenerována z názvu")
				->setAttribute("class", "span6");
		}
	}


	/**
	 * @param Nette\Application\UI\Form
	 */
	protected function addFormAttachments($form)
	{
		if ($this->moduleParams->attachmentGallery || $this->moduleParams->attachmentFiles) {
			$form->addGroup("Přílohy");

			if ($this->moduleParams->attachmentGallery) {
				$galleryList = $this->galleryModel->fetchPairs("id", "name");
				$form->addSelect("gallery_id", "Připojená galerie", $galleryList)
					->setPrompt($galleryList ? "Vyberte" : "Zatím neexistuje žádná fotogalerie");
			}

			if ($this->moduleParams->attachmentFiles) { // typy?
				$form->addUpload("attachment_1", "Příloha 1:");
				$form->addUpload("attachment_2", "Příloha 2:");
				$form->addUpload("attachment_3", "Příloha 3:");
			}
		}
	}


	/********************** process form **********************/


	/**
	 * @param  array
	 * @return array
	 */
	public function preProcessValues($values)
	{
		if ((isset($values["url"]) && $values["url"] == NULL) || !isset($values["url"])) {
			$values["url"] = $this->getUniqueUrl($values["title"]);
		}
		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		$values = $this->preProcessValuesStashAttachments($values);

		if ($this->id == NULL) {
			$values["created"] = $values["edited"];
		}

		return $values;
	}


	/**
	 * @param  array
	 * @param  id
	 */
	public function postProcessValues($values, $id)
	{
		$this->postProcessValuesSaveContentHistory($values, $id);
		$this->postProcessValuesSaveAttachments($values, $id);
	}


	/**
	 * @param  array
	 * @param  int
	 */
	private function postProcessValuesSaveContentHistory($values, $id)
	{
		if ($this->moduleParams->contentHistory) {
			$array = array(
				"content" => $values["content"],
				$this->type . "_id" => $id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);

			$this->{$this->type . "ContentModel"}->insert($array);
		}
	}


	/********************** attachments **********************/


	/**
	 * @param  array
	 * @return array
	 */
	protected function preProcessValuesStashAttachments($values)
	{
		if ($this->moduleParams->attachmentFiles) {
			for ($i = 1; $i <= 3; $i++) {
				if ($values["attachment_$i"]) {
					$this->attachments[] = $values["attachment_$i"];
				}

				unset($values["attachment_$i"]);
			}
		}

		return $values;
	}


	/**
	 * @param  int
	 * @param  string
	 */
	private function postProcessValuesSaveAttachments($id)
	{
		if ($this->moduleParams->attachmentFiles) {
			foreach ($this->attachments as $file) {
				if ($file->isOk()) {
					$data = array(
						"name_origin" => $file->getName(),
						"suffix" => Filer::extension($file->getName()),
						"name" => Filer::moveFile($file, "/data/file/", TRUE, FALSE, FALSE, TRUE),
						$this->type . "_id" => $id,
						"user_id" => $this->user->id,
						"created" => new Nette\DateTime,
					);

					$this->fileModel->insert($data);
				}
			}
		}
	}


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
		$this["form"]["content"]->setValue($this->{$this->type . "ContentModel"}->fetchSingle("content", $versionId));
	}



	/********************** render **********************/


	/**
	 * @param  string
	 */
	protected function loadTemplateValues()
	{
		if ($this->id) {
			if ($this->moduleParams->attachmentFiles) {
				$this->template->attachmentFiles = $this->fileModel->fetchByType($this->type, $this->id);
			}

			if ($this->moduleParams->contentHistory) {
				$this->template->contentHistory = $this->{$this->type . "ContentModel"}->fetchAll(array($this->type . "_id" => $this->id))
					->select("user.login login, " . $this->type . "_content.*")
					->order("edited DESC");
			}
		}
	}


	/********************** helpers **********************/


	/**
	 * @param string
	 */
	private function getUniqueUrl($name)
	{
		$url = $originUrl = Strings::webalize($name);
		$i = 1;

		while ($item = $this->{$this->type . "Model"}->item(array("url" => $url))) {
			if ($item["id"] == $this->id) {
				return $url;
			}

			$url = $originUrl . "-". $i;
			$i++;
		}

		return $url;
	}

}
