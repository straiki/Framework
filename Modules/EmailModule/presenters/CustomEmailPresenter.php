<?php

namespace EmailModule;

use AdminModule;

class CustomEmailPresenter extends AdminModule\BasePresenter
{
	/** @persistent @var int */
	public $id;

	/** @inject @var Schmutzka\Models\CustomEmail */
	public $customEmailModel;


	/**
	 * @param  int
	 */
	public function renderEdit($id)
	{ 
		$this->loadItemHelper($this->customEmailModel, $id);
	} 

}
