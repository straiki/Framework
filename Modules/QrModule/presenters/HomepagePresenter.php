<?php

namespace QrModule;

use Schmutzka;

class HomepagePresenter extends Schmutzka\Application\UI\Module\Presenter
{
	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;


	public function renderDefault()
	{
		$this->template->qrList = $this->qrModel->fetchAll();
	}

}
