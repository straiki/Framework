<?php

namespace PageModule\Components;

use Schmutzka;
use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\TextControl;


class PageControl extends TextControl
{
	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\PageContent */
	public $pageContentModel;

	/** @var string */
	protected $type = 'page';


	/**
	 * Render page on front
	 * @param  string $uid page uid
	 * @param  bool  $displayTitle display page title
	 */
	public function renderDisplay($uid, $displayTitle = TRUE)
	{
		$this->template->page = $this->pageModel->fetchByUid($uid);
		$this->template->displayTitle = $displayTitle;
	}


	public function createComponentForm()
	{
		$form = new Form;
		$form->addGroup('');
		$form->addText('title', 'Název stránky:')
			->addRule(Form::FILLED, 'Zadejte název stránky')
			->setAttribute('class', 'span6');

		$form->addGroup('Obsah');
		$this->addFormPerexShort($form);
		$this->addFormPerexLong($form);
		$this->addFormContent($form);

		$this->addFormAttachments($form);

		$form->addSubmit('send', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function renderDefault()
	{
		$this->loadTemplateValues();
	}

}
