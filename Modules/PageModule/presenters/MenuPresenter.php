<?php

namespace PageModule;

use AdminModule;

class MenuPresenter extends AdminModule\BasePresenter
{
	/** @persistent @var int */
	public $id;

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
		$data = explode(",", $data);
		$i = 1;
		foreach ($data as $item) {
			$this->pageModel->update(array("menu_rank" => $i), $item);
			$i++;
		}
	}

}
