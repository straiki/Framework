<?php

namespace UserModule\Grids;

use Schmutzka;
use NiftyGrid;

class UserGrid extends NiftyGrid\Grid
{
	/** @persistent */
	public $id;

	/** @var Schmutzka\Models\User */
	protected $model;

	/** @var array */
	private $settings;


	final function inject(Schmutzka\Models\User $model, Schmutzka\Config\ParamService $paramService)
	{
		$this->model = $model;
		$this->settings = $paramService->getModuleParams("user");
	}


	/**
	 * Configure
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$this->useFlashMessage = FALSE;

		$source = new NiftyGrid\DataSource($this->model->all()->where("role != ?", "admin"));
		$this->setDataSource($source);

		// grid structure
		$this->addColumn("email", "Email", "25%");
		$this->addColumn("name", "Jméno", "20%")->setRenderer(function ($row) {
			return $row->name . " " . $row->surname;
		});

		$this->addColumn("created", "Registrován", "15%")->setDateRenderer();
		$this->addColumn("last_active", "Poslední aktivita", "15%")->setDateRenderer();

		$this->addColumn("role", "Role", "10%")->setListRenderer($this->settings["roles"]);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(NULL, TRUE); 
	}

}