<?php

namespace ArticleModule\Forms;

use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Forms\ModuleForm;
use Nette;

class ArticleCategoryForm extends ModuleForm
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;

	/** @var string */
	protected $mainModelName = "articleCategoryModel";


	public function build()
    {
		parent::build();

		$this->addText("name", "Název kategorie:")
			->addRule(Form::FILLED, "Povinné");
	}

}
