<?php

namespace TextSnippetModule\Components;

use Schmutzka\Application\UI\Form;
use Schmutzka\Application\UI\Module\Control;


class TextSnippetControl extends Control
{
	/** @inject @var Schmutzka\Models\TextSnippet */
	public $textSnippetModel;


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText('name', 'Název:')
			->addRule(Form::FILLED, 'Zadejte název')
			->setAttribute('class', 'span6');

		$form->addText('uid', 'Identifikátor:')
			->addRule(Form::FILLED, 'Zadejte uid');

		$form->addTextarea('content', 'Obsah:')
			->addRule(Form::FILLED, 'Zadejte text')
			->setAttribute('class', 'ckeditor');

		$form->addSubmit('send', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	/**
	 * @param  string $uid
	 */
	protected function renderDisplay($uid)
	{
		$this->template->content = $this->textSnippetModel->fetchSingle('content', array(
			'uid' => $uid
		));
	}

}
