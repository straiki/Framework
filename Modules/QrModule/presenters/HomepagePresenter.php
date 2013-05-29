<?php

namespace QrModule;

use Schmutzka\Utils\Filer;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent @forView(edit) */
	public $id;

	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;


	/**
	 * Delete item
	 * @param  int
	 */
	public function handleDelete($id)
	{
		if ($id) {
			$this->deleteHelper($this->qrModel, $id);
		}
	}


	public function renderDefault()
	{
		$this->template->qrList = $this->qrModel->all();
	}


}
