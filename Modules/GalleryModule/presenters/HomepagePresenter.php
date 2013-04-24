<?php

namespace GalleryModule;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;


	/** 
	 * Delete gallery file
	 * @param int 
	 */ 
	public function handleDeleteFile($fileId) 
	{ 
		if ($galleryFile = $this->galleryFileModel->item($fileId)) {
			$file = $this->dirs["system"] . $galleryFile["name"];
			if (file_exists($file)) {
				unlink($file);
			}
			$file = $this->dirs["systemThumb"] . $galleryFile["name"];
			if (file_exists($file)) {
				unlink($file);
			}

			$this->galleryFileModel->delete($fileId);
			$galleryItem = $this->galleryModel->item($this->id);

			$newFileCount = $galleryItem["file_count"]-1;
			if (!$newFileCount) {
				$this->galleryModel->delete($this->id);
				$this->flashMessage("Galerie byla úspěšně smazána.", "flash-success"); 
				$this->redirect("default", array("id" => NULL));

			} else {
				$this->galleryModel->update(array("file_count" => $newFileCount), $this->id);
				$this->flashMessage("Záznam byl úspěšně smazán.","flash-success"); 
			}

		} else { 
			$this->flashMessage("Tento záznam neexistuje.", "flash-error"); 
		} 

		$this->redirect("this", array("fileId" => NULL)); 
	}


	/**
	 * @param int
	 */
	public function renderEdit($id) 
	{ 
		$this->loadEditItem($this->galleryModel, $id);

		$this->template->dirThumb = $this->dirs["viewThumb"];
		$this->template->galleryFileList = $this->galleryFileModel->all(array("gallery_id" => $id));
	} 


	public function createComponentGalleryForm()
	{	
		return $this->context->createGalleryForm();
	}


	protected function createComponentGalleryGrid()
	{
		return new Grids\GalleryGrid($this->galleryModel);
	}


	/********************** helpers **********************/


	/**
	 * Get dir paths
	 */
	public function getDirs()
	{
		if ($this->id) {
			return array(
				"system" => WWW_DIR . "/upload/gallery/" . $this->id . "/",
				"systemThumb" => WWW_DIR . "/upload/gallery/" . $this->id . "/thumb/",
				"view" => $this->template->basePath . "/upload/gallery/" . $this->id . "/",
				"viewThumb" => $this->template->basePath . "/upload/gallery/" . $this->id . "/thumb/"
			);
		}

		return NULL;
	}

}