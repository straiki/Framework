<?php

namespace PageModule;

use Schmutzka\Utils\Filer;
use Grids;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent @forView(edit) */
	public $id;

	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageCategory */
	public $pageCategoryModel;

	/** @inject @var Schmutzka\Models\PageInCategory */
	public $pageInCategoryModel;

	/** @inject @var Schmutzka\Models\PageContent */
	public $pageContentModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;

	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;


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
		$this["pageForm"]["content"]->setValue($this->pageContentModel->fetchSingle("content", $versionId));
	}


	/********************** base **********************/

	/**
	 * Render add
	 */
	public function renderAdd()
	{
		$this->loadTinymceVars();
	}


	/**
	 * @param int
	 */
	public function renderEdit($id)
	{
		$this->loadItemHelper($this->pageModel, $id);

		if ($this->moduleParams["attachment_files"]) {
			$this->template->attachmentFiels = $this->fileModel->fetchByType("page_attachment", $id);
		}

		if ($this->moduleParams["content_history"]) {
			$this->template->contentHistory = $this->pageContentModel->all(array("page_id" => $id))->select("user.login login, page_content.*")->order("edited DESC");
		}

		$this->loadTinymceVars();
	}


	/********************** helpers **********************/


	/**
	 * Load page list and article list
	 * @param
	 */
	private function loadTinymceVars()
	{
		$this->template->pageList = $pageList = $this->pageModel->fetchPairs("id", "title");
		$this->template->articleList = $articleList = $this->articleModel->fetchPairs("id", "title");
	}

}
