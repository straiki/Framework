<?php

namespace Schmutzka\Models;

class Page extends Base
{
	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;


	/**
	 * Get item
	 * @param  int
	 * @return  array
	 */
	public function fetchItem($id)
	{
		$moduleParams = $this->paramService->getModuleParams("page");

		$item = parent::item($id);
		if ($moduleParams->attachmentGallery && $item["gallery_id"]) {
			$item["gallery_files"] = $this->galleryFileModel->fetchOrderedListByGallery($page["gallery_id"]);
		}

		return $item;
	}

}
