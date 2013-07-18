<?php

namespace MenuModule\Components;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class MenuControl extends Control
{
	/** @inject @var Schmutzka\Models\menu */
	public $menuModel;

	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @var string */
	protected $onProcessRedirect = "Homepage:default";


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addGroup("");
		$typeList = array(
			"page" => "Stránka",
			"link" => "Odkaz",
			"drop" => "Podmenu",
			"separator" => "Oddělovač"
		);
		$form->addSelect("type", "Typ položky:", $typeList)
			->setPrompt("Vyberte")
			->addCondition(Form::EQUAL, "page")
				->toggle("page")
			->endCondition()
			->addCondition(Form::EQUAL, "link")
				->toggle("link")
			->endCondition()
			->addCondition(Form::EQUAL, array("link", "drop"))
				->toggle("title");

		$form->addToggleGroup("page");
		$pageList =  $this->pageModel->fetchPairs("id", "title");
		$form->addSelect("page_id", "Stránka:", $pageList)
			->setPrompt("Vyberte")
			->addConditionOn($form["type"], Form::EQUAL, "page")
				->addRule(Form::FILLED, "Vyberte stránku");

		$form->addToggleGroup("title");
		$form->addText("title", "Název:")
			->addConditionOn($form["type"], Form::EQUAL, array("link", "drop"))
				->addRule(Form::FILLED, "Zadejte název položky");

		$form->addToggleGroup("link");
		$form->addUrl("url", "Odkaz:")
			->setAttribute("class", "span6")
			->addConditionOn($form["type"], Form::EQUAL, "link")
				->addRule(Form::FILLED, "Zadejte adresu odkazu");

		$form->addGroup("");
		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}


	public function renderFront()
	{
		parent::useTemplate("front");
		$this->template->menuItems = $this->menuModel->fetchFront();
		$this->template->render();
	}

}
