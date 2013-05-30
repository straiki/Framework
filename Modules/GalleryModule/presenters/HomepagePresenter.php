<?php

namespace GalleryModule;

use AdminModule;

class HomepagePresenter extends AdminModule\BasePresenter
{
	/** @persistent @var int */
	public $id;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;


	/**
	 * @param int
	 */
	public function renderEdit($id)
	{
		$this->loadItemHelper($this->galleryModel, $id);
	}

}
