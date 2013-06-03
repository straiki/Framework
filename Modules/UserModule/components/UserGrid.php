<?php

namespace UserModule\Grids;

use Schmutzka;
use NiftyGrid;

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
		$this->addColumn("email", "Email", "25%");
		$this->addColumn("login", "Jméno", "20%");
		$this->addColumn("created", "Registrován", "15%")->setDateRenderer();
		$this->addColumn("last_active", "Poslední aktivita", "15%")->setDateRenderer();
		$this->addColumn("role", "Role")->setListRenderer((array) $params->roles);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(NULL, TRUE);
	}

}
