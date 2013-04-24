<?php

namespace PageModule\Forms;

use Schmutzka\Application\UI\Form;
# use Schmutzka\Forms\Form;
use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Schmutzka\Utils\Filer;
use Schmutzka;

class PageForm extends Form
{
	/** @persistent */
	public $id;

	/** @var string @persistent */
	public $type;

	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageContent */
	public $pageContentModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Security\User */
	public $user;


	public function build()
    {
    	$params = $this->paramService->getModuleParams("page");

		parent::build();

		$this->addGroup("");
		$this->addText("title", "Název stránky:")
			->addRule(Form::FILLED, "Povinné");

		if ($params["show_in_sliderbox"]) {
			$this->addCheckBox("page_show_in_sliderbox", "Zobrazit ve SliderBoxu");
		}

		$this->addGroup("Publikování");

		if (isset($params["publish_datetime"]) && $params["publish_datetime"]) {
			$this->addDateTimePicker("publish_datetime", "Čas publikování:");
		}

		if ($params["publish_state"]) {
			$array = array(
				"concept" => "Koncept",
				"pending" => "Čekající na schválení",
				"public" => "Publikován"
			);
			$this->addSelect("publish_state", "Stav publikování:", $array);
		}

		if ($params["access_to_roles"]) {
			$roles = $this->paramService->cmsSetup->modules->user->roles;
			$this->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", (array) $roles)
				->setAttribute("data-placeholder","Zde můžete omezit zobrazení pouze pro určité uživatele")
				->setAttribute("class","chosen width400");
		}

		if ($params["custom_url"]) {
			$this->addText("url", "Url adresa:")
				->setOption("description","Bude automaticky vygenerována z názvu.");
		}

		// $this->addUpload("image", "Obrázek:"); - 1 možnost narhát, 2 vybrat z galeire - 2DO při modulu galerie

		$this->addGroup("Obsah")
			->setOption("description", Html::el('div')->setHtml("[page:5:Odkaz na stránku s id = 5], [article:5:Odkaz na článek s id = 5]"));

		if ($params["perex_short"]) {
			$this->addTextArea("perex_short", "Perex (kratší):")
				->setOption("description", "Krátké shrnutí stránky zobrazující se např. pod nadpisem.")
				->setAttribute("class", "tinymce");
		}

		if ($params["perex_long"]) {
			$this->addTextArea("perex_long", "Perex (delší):")
				->setOption("description", "Shrnutí obsahu zobrazující se např. ve výpisu článků.")
				->setAttribute("class", "tinymce");
		}

		$this->addTextarea("content","Obsah:", NULL, 30)
			->setAttribute("class","tinymce");


		if ($params["attachment_gallery"] || $params["attachment_files"]) {
			$this->addGroup("Přílohy");

			if ($params["attachment_gallery"]) {
				$galleryList = $this->galleryModel->fetchPairs("id", "name");
				$this->addSelect("gallery_id", "Připojená galerie", $galleryList)
					->setPrompt($galleryList ? "Vyberte" : "Zatím neexistuje žádná fotogalerie");
			}

			if ($params["attachment_files"]) { // typy?
				$this->addUpload("attachment_1", "Příloha 1:");
				$this->addUpload("attachment_2", "Příloha 2:");
				$this->addUpload("attachment_3", "Příloha 3:");
			}
		}

		$this->addSubmit();

		return $this;
	}


	public function attached($presenter)
	{
		parent::attached($presenter);
		$params = $this->paramService->getModuleParams("page");
		if ($this->id = $presenter->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->pageModel->item($this->id);
			if ($params["access_to_roles"]) {
				$defaults["access_to_roles"] = unserialize($defaults["access_to_roles"]);
			}

			// $this->setDefaults($defaults);
		}
	}


	/**
	 * Process form
	 */
	public function process(Form $form)
	{
		$params = $this->paramService->getModuleParams("page");
		if ($this->id && $form["cancel"]->isSubmittedBy()) {
			$this->redirect("default", array("id" => NULL));
		}

		$values = $form->values;
		$values["url"] = $this->getUrl($values["title"]);
		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;

		dd($values);

		// upload attachments
		$attachments = array();
		if ($params["attachment_files"]) {
			$attachments[] = $values["attachment_1"];
			$attachments[] = $values["attachment_2"];
			$attachments[] = $values["attachment_3"];
			unset($values["attachment_1"], $values["attachment_2"], $values["attachment_3"]);
		}

		if ($params["access_to_roles"]) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		if ($this->id) {
			$this->pageModel->update($values, $this->id);

		} else {
			$values["created"] = $values["edited"];
			$id = $this->pageModel->insert($values);
			$this->id = $id;
		}

		if ($params["content_history"]) {
			$array = array(
				"content" => $values["content"],
				"page_id" => $this->id,
				"user_id" => $this->user->id,
				"edited" => new Nette\DateTime
			);
			$this->pageContentModel->insert($array);
		}

		if ($params["attachment_files"]) {
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
