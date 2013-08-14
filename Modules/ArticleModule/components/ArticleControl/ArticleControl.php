<?php

namespace ArticleModule\Components;

use Nette;
use Nette\Utils\Html;
use Schmutzka;
use Schmutzka\Application\UI\Module\TextControl;
use Schmutzka\Application\UI\Form;


class ArticleControl extends TextControl
{
	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;

	/** @inject @var Schmutzka\Models\ArticleCategory */
	public $articleCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleInCategory */
	public $articleInCategoryModel;

	/** @inject @var Schmutzka\Models\ArticleContent */
	public $articleContentModel;

	/** @var string */
	protected $type = 'article';

	/** @var array */
	private $articleCategories;

	/** @var Schmutzka\Models\Qr */
	private $qrModel;


	public function injectQrModel(Schmutzka\Models\Qr $qrModel = NULL)
	{
		$this->qrModel = $qrModel;
	}


	public function createComponentForm()
	{
		$form = new Form;
		$form->addGroup('');
		$form->addText('title', 'Nadpis článku:')
			->setAttribute('class', 'form-control')
			->addRule(Form::FILLED, 'Zadejte nadpis článku');

		if ($this->moduleParams->categories) {
			$categoryList = $this->articleCategoryModel->fetchPairs('id', 'name');
			$form->addMultiSelect('article_categories', 'Kategorie:', $categoryList)
				->setAttribute('data-placeholder', 'Vyberte jednu či více kategorií')
				->setAttribute('class', 'chosen form-control')
				->addRule(Form::FILLED, 'Vyberte aspoň jednu kategorii');
		}

		if ($this->moduleParams->customAuthorName || $this->moduleParams->publishState || $this->moduleParams->accessToRoles) {
			$form->addGroup('Publikování');
			if ($this->moduleParams->customAuthorName) {
				$form->addText('custom_author_name', 'Jméno autora:')
					->setOption('description', 'Přepíše autora článku')
					->setAttribute('class', 'form-control');
			}

			if ($this->moduleParams->publishDatetime) {
				$form->addDateTimePicker('publish_datetime', 'Čas publikování:')
					->setDefaultValue(new Nette\DateTime)
					->addRule(Form::FILLED, 'Zadejte čas publikování')
					->setAttribute('class', 'form-control');
			}

			if ($this->moduleParams->publishState) {
				$publishTypes = (array) $this->moduleParams->publishTypes;
				$form->addSelect('publish_state', 'Stav publikování:', $publishTypes)
					->setAttribute('class', 'form-control');
			}

			if ($this->moduleParams->accessToRoles) {
				$roles = (array) $this->paramService->cmsSetup->modules->user->roles;
				$form->addMultiSelect('access_to_roles', 'Zobrazit pouze pro:', $roles)
					->setAttribute('data-placeholder', 'Zde můžete omezit zobrazení pouze pro určité uživatele')
					->setAttribute('class', 'chosen form-control');

			}
		}

 		$form->addGroup('Obsah');
		$this->addFormPerexShort($form);
		$this->addFormPerexLong($form);
		$this->addFormContent($form);

		$this->addFormAttachments($form);

		if ($this->moduleParams->qr) {
			$cond = array('article_id IS NULL OR article_id = ?' => $this->id);
			$qrList = $this->qrModel->fetchPairs('id', 'alias', $cond);
			if ($qrList) {
				$form->addSelect('qr', 'QR kód:', $qrList)
					->setPrompt('Vyberte');
			}
		}

		$form->addSubmit('send', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->id = $presenter->id) {
			$defaults = $this->articleModel->item($this->id);
			if ($this->moduleParams->qr) {
				$defaults['qr'] = $this->qrModel->fetchSingle('id', array(
					'article_id' => $this->id
				));
			}

			if ($this->moduleParams->accessToRoles) { // @todo separate table
				$defaults['access_to_roles'] = unserialize($defaults['access_to_roles']);
			}

			$this['form']->setDefaults($defaults);
		}
	}

	public function preProcessValues($values)
	{
		$values = parent::preProcessValues($values);

		if ($this->moduleParams->categories) {
			$this->articleCategories = $values['article_categories'];
			unset($values['article_categories']);
		}


		if ($this->moduleParams->accessToRoles) {
			$values['access_to_roles'] = serialize($values['access_to_roles']);
		}

		if ($this->moduleParams->qr) {
			if ($values['qr']) {
				$this->qrModel->update(array('article_id' => $this->id), $values['qr']);
			}
			unset($values['qr']);
		}

		return $values;
	}


	public function postProcessValues($values, $id)
	{
		parent::postProcessValues($values, $id);

		if ($this->moduleParams->categories) {
			$this->articleInCategoryModel->modify($id, $this->articleCategories);
		}
	}


	public function renderDefault()
	{
		$this->loadTemplateValues();
	}

}
