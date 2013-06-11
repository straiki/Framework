<?php

namespace GalleryModule\Components;

use Nette;
use Nette\Image;
use Nette\Utils\Finder;
use Schmutzka\Utils\Filer;
use Schmutzka\Application\UI\Module\Control;
use UploadHandler;

class UploadControl extends Control
{
	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;


	public function handleUpload()
	{
		// 0. capture fie upload result
		ob_start();
		$upload_handler = new UploadHandler();
		$jsonData = ob_get_clean();

		// magic here
		$file = json_decode($jsonData)->files[0];

		/** output data example:
		stdClass (6)
			name => "dnb_typography-1920x1080.jpg" (28)
			size => 366296
			url => "http://local.peloton.cz/files/dnb_typography-1920x1080.jpg" (58)
			thumbnail_url => "http://local.peloton.cz/files/thumbnail/dnb_typography-1920x1080.jpg" (68)
			delete_url => "http://local.peloton.cz/?file=dnb_typography-1920x1080.jpg" (58)
			delete_type => "DELETE" (6)
		*/

		$image = Nette\Image::fromFile($file->url);

		// 1. save, resize and save
		$uniqueName = Filer::getUniqueName($this->galleryDir, $file->name);

		if (!is_dir($this->galleryDir)) {
			mkdir($this->galleryDir, 0777);
		}

		foreach ($this->moduleParams->size_versions as $type => $dimensions) {
			if ($type === "natural") {
				$image->resize($dimensions["width"], $dimensions["height"], Image::SHRINK_ONLY | Image::EXACT);
				$image->save($this->galleryDir . "/" . $uniqueName);

			} else {
				Filer::resizeToSubfolder($image, $this->galleryDir, $dimensions["width"], $dimensions["height"], $uniqueName);
			}
		}

		// 2. save to db
		$data = array(
			"gallery_id" => $this->id,
			"name" => $uniqueName,
			"name_orig" => $file->name,
		);
		$this->galleryFileModel->insert($data);

		// 3. cleanup file
		unlink(WWW_DIR . "/files/" . $file->name);
		unlink(WWW_DIR . "/files/thumbnail/" . $file->name);
	}


	public function handleSort()
	{
		$data = explode(",", $_POST["data"]); // @todo ask Honza for value data
		$i = 1;
		foreach ($data as $item) {
			$this->galleryFileModel->update(array("rank" => $i), $item);
			$i++;
		}
	}


	/**
	 * @param int
	 */
	public function handleDeleteFile($fileId)
	{
		if ($galleryFile = $this->galleryFileModel->item($fileId)) {
			foreach (Finder::findFiles($galleryFile["name"])->from($this->galleryDir) as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}

			$this->galleryFileModel->delete($fileId);
			$galleryItem = $this->galleryModel->item($this->id);

			$this->presenter->flashMessage("Záznam byl úspěšně smazán.","success");

		} else {
			$this->presenter->flashMessage("Tento záznam neexistuje.", "error");
		}

		$this->presenter->redirect("this", array("fileId" => NULL));
	}


	public function render()
	{
		parent::useTemplate();
		$key = array(
			"gallery_id" => $this->id
		);
		$this->template->galleryThumbDir = $this->getGalleryDir(FALSE) . "w80_h80/";
		$this->template->galleryFiles = $this->galleryFileModel->fetchAll($key)->order("rank, id");
		$this->template->render();
	}


	/********************** helpers **********************/


	/**
	 * @param bool
	 */
	public function getGalleryDir($absolute = TRUE)
	{
		return ($absolute ? WWW_DIR : "") . "/upload/gallery/" . $this->id . "/";
	}

}
