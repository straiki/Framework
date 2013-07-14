<?php

namespace PageModule\Components;

use Schmutzka\Application\UI\Control;

class FrontMenuControl extends Control
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;


	public function render()
	{
		parent::useTemplate();
		$this->template->menuItems = $this->pageModel->fetchAll(array(
			"menu_active" => TRUE
		))->order("menu_rank");
		$this->template->render();
	}

}
