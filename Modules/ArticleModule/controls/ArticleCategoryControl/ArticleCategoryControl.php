<?php

namespace ArticleModule\Controls;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;

class ArticleCategoryControl extends Control
{
	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;


	public function createComponentForm()
	{
		$form = new Form;
		$form->addText("name", "Název kategorie:")
			->addRule(Form::FILLED, "Zadejte název kategorie");
		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		return $form;
	}

}
