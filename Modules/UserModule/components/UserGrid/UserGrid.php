<?php

namespace UserModule\Components;

use NiftyGrid;
use Schmutzka;

class UserGrid extends NiftyGrid\Grid
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	/**
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$params = $presenter->paramService->getModuleParams("user");

		$source = new NiftyGrid\DataSource($this->userModel->fetchAll()); // ->where("role != ?", "admin"));
		$this->setDataSource($source);
		$this->setModel($this->userModel);

		// grid structure
		$this->addColumn("email", "Email");
		$this->addColumn("login", "Jméno", "20%");
		$this->addColumn("created", "Registrován", "10%")->setDateRenderer("j. n. Y");
		$this->addColumn("last_active", "Aktivita", "10%")->setDateRenderer("j. n. Y");
		$this->addColumn("role", "Role", "12%")->setListRenderer((array) $params->roles);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(NULL, TRUE);
	}

}
