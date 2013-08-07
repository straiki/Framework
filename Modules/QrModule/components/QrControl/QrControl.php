<?php

namespace QrModule\Components;

use Nette\Utils\Strings;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class QrControl extends Control
{
	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;

	/** @inject @var QrModule\Services\QrGenerator */
	public $qrGenerator;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText('alias', 'Alias:')
			->addRule(Form::FILLED, 'Zadejte alias')
			->setOption('description', 'Bude dostupné na http://www.web.cz/qr/<alias>');
		$form->addSubmit('send', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function processForm($form)
	{
		$values = $form->values;
		$values['alias'] = Strings::webalize($values['alias']);

		$url = $this->presenter->template->baseUrl . '/qr/' . $values['alias'];
		$values['filename'] = $this->qrGenerator->generateImageForUrl($url, 150);
		$values['created'] = new Nette\DateTime;
		$this->qrModel->insert($values);

		$this->presenter->flashMessage('Kód byl vygenerován', 'success');
		$this->presenter->redirect('this');
	}

}
