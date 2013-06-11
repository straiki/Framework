<?php

namespace GalleryModule\Components;

use Nette;
use Schmutzka;
use Schmutzka\Application\UI\Module\Control;
use Schmutzka\Application\UI\Form;

class GalleryControl extends Control
{
	/** @inject @var Schmutzka\Models\Gallery */
	public $galleryModel;


	public function createComponentForm()
    {
		$form = new Form;
		$form->addText("name", "Název fotogalerie:")
			->addRule(Form::FILLED, "Povinné");

		if ($this->moduleParams->accessToRoles) {
			$roles = $this->paramService->cmsSetup->modules->user->roles;
			$form->addMultiSelect("access_to_roles", "Zobrazit pouze pro:", (array) $roles)
				->setAttribute("data-placeholder", "Zde můžete omezit zobrazení pouze pro určité uživatele")
				->setAttribute("class", "chosen width400");
		}

		if ($this->moduleParams->description) {
			$form->addTextarea("description", "Popis:")
				->setAttribute("class", "span8");
		}

		$form->addSubmit("send", "Uložit")
			->setAttribute("class", "btn btn-primary");

		if ($this->id) {
			$defaults = $this->galleryModel->item($this->id);
			if ($this->moduleParams->accessToRoles) {
				$defaults["access_to_roles"] = unserialize($defaults["access_to_roles"]);
			}

			$form->setDefaults($defaults);
		}

		return $form;
	}


	/**
	 * @param array
	 * @return array
	 */
	public function preProcessValues($values)
	{
		$values["edited"] = new Nette\DateTime;
		$values["user_id"] = $this->user->id;
		if ($this->moduleParams->accessToRoles) {
			$values["access_to_roles"] = serialize($values["access_to_roles"]);
		}

		return $values;
	}

}
