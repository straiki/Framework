<?php

namespace PageModule;

use AdminModule;

class HomepagePresenter extends AdminModule\BasePresenter
{
	/** @persistent @var int */
	public $id;

	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;


	/**
	 * @param int
	 */
	public function renderEdit($id)
	{
		$this->loadItemHelper($this->pageModel, $id);
	}

}
