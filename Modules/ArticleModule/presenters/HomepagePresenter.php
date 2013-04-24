<?php

namespace ArticleModule;

use Schmutzka\Utils\Filer;
use Grids;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent @forView(edit) */
	public $id;

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

	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;


	/********************** handlers **********************/


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
		$this["articleForm"]["content"]->setValue($this->articleContentModel->fetchSingle("content", $versionId));
	}

	
	/********************** pick from gallery **********************/


	public function handlePickGallery()
	{
		$this->template->pickGallery = TRUE;
		$this->template->galleryList = $this->galleryModel->fetchPairs("id", "name", NULL, "name");
	}


	/**
	 * @param int
	 */
	public function handlePickImage($galleryId)
	{
		$this->template->pickImage = TRUE;
		$this->template->gallery = $this->galleryModel->getItem($galleryId);
	}


	/**
	 * @param int
	 */
	public function handleSetImage($fileId)
	{
		$data["gallery_file_id"] = $fileId;
		$this->articleModel->update($data, $this->id);
		$this->flashMessage("Fotka nastavena","success");
		$this->redirect("this");
	}


	/********************** base **********************/

	
	/**
	 * @param int
	 */
	public function renderEdit($id) 
	{ 
		$item = $this->loadItem($this->articleModel, $id);

		if ($this->moduleParams["attachment_files"]) {
			$this->template->attachmentFiels = $this->fileModel->fetchByType("article_attachment", $id);
		}

		if ($this->moduleParams["content_history"]) {
			$this->template->contentHistory = $this->articleContentModel->all(array("article_id" => $id))->select("user.login login, article_content.*")->order("edited DESC");
		}

		if ($this->moduleParams["promo_photo"] && $item["gallery_file_id"]) {
			$this->template->promoPhoto = $this->galleryFileModel->item($item["gallery_file_id"]);
		}
	} 


	/**
	 * @return ArticleModule\Forms\ArticleForm
	 */
	public function createComponentArticleForm()
	{
		return $this->context->createArticleForm();
	}


	/**
	 * @return ArticleModule\Grids\ArticleGrid
	 */
	protected function createComponentArticleGrid()
	{
		return $this->context->createArticleGrid();
	}

}
