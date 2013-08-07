<?php

namespace Schmutzka\Models;

use Schmutzka;


class Page extends Base
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @var Schmutzka\Models\GalleryFile */
	private $galleryFileModel;


	public function injectModels(Schmutzka\Models\GalleryFile $galleryFileModel = NULL)
	{
		$this->galleryFileModel = $galleryFileModel;
	}


	/**
	 * Get item
	 * @param  int
	 * @return  array
	 */
	public function fetchItem($id)
	{
		$moduleParams = $this->paramService->getModuleParams('page');

		$item = parent::item($id);
		if ($moduleParams->attachmentGallery && $item['gallery_id']) {
			$item['gallery_files'] = $this->galleryFileModel->fetchOrderedListByGallery($page['gallery_id']);
		}

		return $item;
	}

}
