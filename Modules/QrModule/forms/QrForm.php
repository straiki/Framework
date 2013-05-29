<?php

namespace QrModule\Forms;

use Schmutzka;
use Schmutzka\Application\UI\Form;
use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

class QrForm extends Form
{
	/** @inject @var Schmutzka\Models\Qr */
	public $qrModel;

	/** @inject @var QrModule\Services\QrGenerator */
	public $qrGenerator;


	public function build()
    {
		parent::build();

		$this->addText("alias", "Alias:")
			->addRule(Form::FILLED, "Zadejte alias")
			->setOption("description", "Bude dostupné na http://www.web.cz/qr/<alias>");
		$this->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");
	}


	public function process(Schmutzka\Application\UI\Form $form)
	{
		$values = $form->values;
		$values["alias"] = Strings::webalize($values["alias"]);

		$url = $this->presenter->template->baseUrl . "/qr/" . $values["alias"];
		$values["filename"] = $this->qrGenerator->generateImageForUrl($url, 150);
		$values["created"] = new Nette\DateTime;
		$this->qrModel->insert($values);

		$this->presenter->flashMessage("Kód byl vygenerován", "success");
		$this->presenter->redirect("this");
	}

}
