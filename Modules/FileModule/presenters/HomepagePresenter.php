<?php

namespace FileModule;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\File */
	public $fileModel;

}
