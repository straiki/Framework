<?php

namespace EventModule;

class CategoryPresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;


	/**
	 * @param int
	 */
	public function renderEdit($id) 
	{ 
		$this->loadEditItem($this->models->eventCategory, $id);
	} 


	/**
	 * Event category form
	 */
	public function createComponentEventCategoryForm()
	{
		return new Forms\EventCategoryForm($this->models->eventCategory, $this->paramService, $this->id);
	}


	/**
	 * Event category grid
	 */
	protected function createComponentEventCategoryGrid()
	{
		return new Grids\EventCategoryGrid($this->models->eventCategory, $this->paramService);
	}

}
