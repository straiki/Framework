<?php

namespace EventModule\Components;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Appliaction\UI\Form;
use Schmutzka\Application\UI\Module\Control;
use Schmutzka\Utils\Filer;


class EventControl extends Control
{
	/** @inject @var Schmutzka\Models\Event */
	public $eventModel;

	/** @inject @var Schmutzka\Models\EventCategory */
	public $eventCategoryModel;

	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;

	/** @var filepath */
	private $folder = 'upload/event/';


	protected function createComponentForm()
	{
		$form = new Form;
		$form->addText('title','Název akce:')
			->addRule(Form::FILLED, 'Povinné');

		if ($categoryList = $this->eventCategoryModel->fetchList()) {
			$form->addSelect('event_category_id','Kategorie:', $categoryList)
				->setPrompt('Vyberte')
				->addRule(Form::FILLED, 'Povinné');
		}

		$form->addDatepicker('date','Datum akce:')
			->addRule(Form::FILLED, 'Povinné')
			->addRule(Form::DATE, 'Čas nemá správný formát');

		$form->addText('time','Čas akce:')
			->addCondition(Form::FILLED)
				->addRule(Form::TIME, 'Čas nemá správný formát');

		$form->addUpload('image', 'Obrázek:');

		$form->addTextarea('content','Obsah:')
			->addRule(Form::FILLED, 'Povinné')
			->setAttribute('class','tinymce');

		if ($this->moduleParams->galleryLink && $galleryList = $this->galleryModel->fetchPairs('id', 'name')) {
			$form->addSelect('gallery_id', 'Propojit s galerií:', $galleryList)
				->setPrompt('Bez galerie');
		}

		if ($this->moduleParams->calendar) {
			$form->addCheckbox('display_in_calendar', 'Zobrazit v kalendáři')
			->setDefaultValue(1);
		}

		if ($this->moduleParams->news) {
			$form->addCheckbox('is_news', 'Je aktualita');
		}

		if ($this->moduleParams->link) {
			$form->addText('link', 'Odkaz (více):')
			->addCondition(Form::FILLED)
				->addRule(Form::URL, 'Adresa nemá správný formát');
		}

		$form->addSubmit('send', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		return $form;
	}


	public function processForm( $form)
	{
		if ($this->id && $form['cancel']->isSubmittedBy()) {
			$this->redirect('default', array('id' => NULL));
		}

		$values = $form->values;

		if (!$values['time']) {
			$values['time'] = NULL;
		}

		$values['edited'] = new Nette\DateTime;
		$values['user_id'] = $this->user->id;

		$file = $values['image'];
		if ($file && $suffix = Filer::checkImage($file)) {

			$image = $file->toImage();
			$image->resize(110, 110, Nette\Image::EXACT);

			$values['image'] = $this->folder . Strings::webalize($file->getName()) . '.' . $suffix;
			$image->save(WWW_DIR . '/' . $values['image']);

		} else {
			unset($values['image']);
		}

		if ($this->id) {
			$this->eventModel->update($values, $this->id);

		} else {
			$values['created'] = $values['edited'];
			$this->eventModel->insert($values);
		}

		$this->flashMessage('Uloženo.', 'success');
		$this->redirect('default', array('id' => NULL));
	}

}
