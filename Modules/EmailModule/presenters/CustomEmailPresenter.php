<?php

namespace EmailModule;

class CustomEmailPresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;


	public function renderEdit($id)
	{ 
		$this->loadItem($this->models->customEmail, $id);
	} 


	/**
	 * Email form
	 * @return EmailModule\Forms\CustomEmailForm
	 */
	public function createComponentCustomEmailForm()
	{
		return new Forms\CustomEmailForm($this->models->customEmail, $this->user, $this->id);
	}


	/**
	 * Email grid
	 * @return EmailModule\Grids\CustomEmailGrid
	 */
	protected function createComponentCustomEmailGrid()
	{
		return new Grids\CustomEmailGrid($this->models->customEmail);
	}

}