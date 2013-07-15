<?php

namespace PageModule;

use Schmutzka\Application\UI\Module\Presenter;

class MenuPresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageTree */
	public $pageTreeModel;


	public function renderDefault()
	{
		$this->template->pageTreeStructure = $this->pageTreeModel->fetchStructure();
	}


	/**
	 * @param  int
	 * @param  bool
	 */
	public function handleSetActive($id, $to)
	{
		$this->pageModel->update(array(
			"menu_active" => $to
		), $id);

		$this->flashMessage("Zobrazení změneno.", "success");
		$this->redirect("this");
	}


	/**
	 * Update menu items rank and parent
	 * @param  string
	 */
	public function handleSortMenu($data)
	{
		$menuItems = json_decode($data);
		foreach ($menuItems as $rank => $item) {
			$itemData = array(
				"menu_parent" => NULL,
				"menu_rank" => $rank
			);
			$this->pageModel->update($itemData, $item->id);

			if (isset($item->children)) {
				foreach ($item->children as $subRank => $subItem) {
					$subItemData = array(
						"menu_parent" => $item->id,
						"menu_rank" => $subRank
					);
					$this->pageModel->update($subItemData, $subItem->id);
				}
			}
		}
	}

}
