<?php

namespace MenuModule;

use Schmutzka\Application\UI\Module\Presenter;


class HomepagePresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\Menu */
	public $menuModel;


	/**
	 * @param  int
	 */
	public function handleDelete($id)
	{
		$this->menuModel->update(array(
			'parent_id' => NULL
		), array(
			'parent_id' => $id
		));

		$this->menuModel->delete($id);

		$this->redirect('this', array(
			'id' => NULL
		));
	}


	/**
	 * @param  int
	 * @param  bool
	 */
	public function handleSetActive($id, $to)
	{
		$this->menuModel->update(array(
			'active' => $to
		), $id);

		$this->redirect('this', array(
			'id' => NULL
		));
	}


	/**
	 * Update menu items rank and parent
	 * @param  string
	 */
	public function handleSortMenu($data)
	{
		$menuItems = json_decode($data);
		foreach ($menuItems as $rank => $item) {
			$this->menuModel->updateParentRank(NULL, $rank, $item->id);

			if (isset($item->children)) {
				foreach ($item->children as $subRank => $subItem) {
					$this->menuModel->updateParentRank($item->id, $subRank, $subItem->id);
				}
			}
		}
	}


	public function renderDefault()
	{
		$this->template->menuItems = $menuItems = $this->menuModel->fetchStructure();
	}

}
