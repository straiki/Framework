<?php

namespace PageModule;

use Schmutzka\Application\UI\Module\Presenter;

class MenuPresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;


	public function renderDefault()
	{
		$this->template->pages = $this->pageModel->fetchAll()->order("menu_rank");
	}


	/**
	 * @param  int
	 * @param  bool
	 */
	public function handleSetActive($id, $to)
	{
		$this->pageModel->update(array("menu_active" => $to), $id);
		$this->flashMessage("Zobrazení změneno.", "success");
		$this->redirect("this");
	}


	/**
	 * @param  array
	 */
	public function handleSort($data)
	{
		parent::handleSort($data, "menu_rank");
	}

}
