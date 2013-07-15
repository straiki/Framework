<?php

namespace PageModule\Components;

use Schmutzka\Application\UI\Control;

class FrontMenuControl extends Control
{
	/** @inject @var Schmutzka\Models\PageTree */
	public $pageTreeModel;


	public function render()
	{
		parent::useTemplate();
		$this->template->menuItems = $this->pageTreeModel->fetchFront();
		$this->template->render();
	}

}
