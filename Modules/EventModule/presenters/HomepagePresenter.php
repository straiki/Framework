<?php

namespace EventModule;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;


	/**
	 * Edit event
	 * @param int
	 */
	public function renderEdit($id) 
	{ 
		$this->loadEditItem($this->models->event, $id);
	} 


	/**
	 * Event form
	 */
	public function createComponentEventForm()
	{
		return new Forms\EventForm($this->models->event, $this->models->eventCategory,  $this->user, $this->paramService, $this->models->gallery, $this->id);
	}


	/**
	 * Event grid
	 */
	protected function createComponentEventGrid()
	{
		return new Grids\EventGrid($this->models->event, $this->models->eventCategory, $this->models->gallery, $this->paramService);
	}

}