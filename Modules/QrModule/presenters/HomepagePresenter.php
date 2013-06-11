<?php

namespace QrModule;

use Schmutzka\Application\UI\Module\Presenter;

class HomepagePresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;


	public function renderDefault()
	{
		$this->template->qrList = $this->qrModel->fetchAll();
	}

}
