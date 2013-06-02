<?php

namespace QrModule;

use Schmutzka\Utils\Filer;
use AdminModule;

class HomepagePresenter extends AdminModule\BasePresenter
{
	/** @persistent @var int */
	public $id;

	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;


	/**
	 * @param  int
	 */
	public function handleDelete($id)
	{
		$this->deleteHelper($this->qrModel, $id);
	}


	public function renderDefault()
	{
		$this->template->qrList = $this->qrModel->fetchAll();
	}

}
