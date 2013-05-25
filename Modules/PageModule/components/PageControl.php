<?php

namespace PageModule\Controls;

use Schmutzka\Application\UI\Control;

class PageControl extends Control
{
	/** @persistent @var int */
	public $id;

	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageContent */
	public $pageContentModel;


	/**
	 * Render page on front	
	 * @param  string $uid page uid
	 * @param  bool  $displayTitle display page title
	 */
	public function renderDisplay($uid, $displayTitle = TRUE)
	{
		parent::useTemplate("display");
		$this->template->page = $this->pageModel->item(array("uid" => $uid));
		$this->template->displayTitle = $displayTitle;
		$this->template->render();
	}

}
