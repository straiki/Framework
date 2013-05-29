<?php

namespace GalleryModule\Forms;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Utils\Filer;

class GalleryForm extends Form
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;

	/** @inject @var Schmutzka\Security\User */
	public $user;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @var filepath */
	private $folder = "upload/gallery/";

	/** @var array */
	private $moduleParams;


	public function attached($presenter)
	{
		$this->moduleParams = $presenter->moduleParams;
		$this->id = $presenter->id;
		parent::attached($presenter);
	}


	public function build()
    {
		parent::build();

		$this->addText("name","Název fotogalerie:")
			->addRule(Form::FILLED, "Povinné");

		if ($this->moduleParams["access_to_roles"]) {
			$roles = $this->paramService->cmsSetup->modules->user->roles;
			$this->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", (array) $roles)
				->setAttribute("data-placeholder","Zde můžete omezit zobrazení pouze pro určité uživatele")
				->setAttribute("class","chosen width400");
		}

		$this->addMultipleFileUpload("files", "Obrázky:")
			->addRule("\MultipleFileUpload::validateFilled", "Vyberte aspoň jeden soubor")
			->addRule("\MultipleFileUpload::validateFileSize", "Max. velikost všech odeslaných souborů je 20 MB!", 20 * 1024 * 1024);

		$this->addTextarea("description","Popis:", NULL, 3)
			->setAttribute("class", "span8");

		$this->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		if ($this->id) {
			$this->addSubmit("cancel", "Zrušit")
				->setValidationScope(FALSE);

			$defaults = $this->galleryModel->item($this->id);
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

		$gallery["name"] = $values["name"];
		$gallery["description"] = $values["description"];
		$gallery["edited"] = new Nette\DateTime;
		$gallery["user_id"] = $this->user->id;

		if ($this->moduleParams["access_to_roles"]) {
			$gallery["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		if ($this->id) {
			if (count($values["files"])) {
				$fileCount = $this->galleryModel->fetchSingle("file_count", $this->id);
				$gallery["file_count"] = $fileCount + count($values["files"]);
				$this->loadFilesToGallery($values["files"], $this->id);
			}
			$this->galleryModel->update($gallery, $this->id);

		} else {
			$gallery["file_count"] = count($values["files"]);
			$gallery["created"] = $gallery["edited"];
			$this->id = $this->galleryModel->insert($gallery);
			$this->loadFilesToGallery($values["files"], $this->id);
		}

		$this->presenter->flashMessage("Uloženo.", "success");
		$this->presenter->redirect("edit", array(
			"id" => $this->id
		));
	}


	/**
	 * Load files to gallery
	 * @param array
	 * @param int
	 */
	private function loadFilesToGallery($files, $id)
	{
		$galleryDir = WWW_DIR . "/upload/gallery/" . $id;
		if (!is_dir($galleryDir)) {
			mkdir($galleryDir, 0777);
			mkdir($galleryDir . "/thumb/", 0777);
		}

		foreach ($files as $file) {
			$name = md5($file->getName()).(Strings::webalize($file->getName(), "."));
			if ($file->isImage()) {
				$image = $file->toImage();

				// 1. base image
				if ($image->width > $this->moduleParams["image_width"] || $image->height > $this->moduleParams["image_height"]) {
					$image->resize($this->moduleParams["image_width"], $this->moduleParams["image_height"]);
				}
				$image->save($galleryDir . "/" . $name);

				// 2. thumbnail image
				$image->resize(NULL, $this->moduleParams["image_thumb_height"]);
				$image->save($galleryDir . "/thumb/" . $name);

				// 3. custom resize
				if ($this->moduleParams["resize_to"]) {
					foreach ($this->moduleParams["resize_to"] as $type) {
						Filer::resizeToSubfolder($file, $galleryDir . "/", $type["width"], $type["height"],$name);
					}
				}
			}

			$galleryFile = array(
				"gallery_id" => $id,
				"name" => $name,
				"size" => $file->getSize(),
			);
			// add info?

			$this->galleryFileModel->insert($galleryFile);
		}
	}

}
