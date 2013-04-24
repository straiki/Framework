<?php

namespace UserModule\Grids;

use Schmutzka;
use NiftyGrid;

class UserGrid extends NiftyGrid\Grid
{
	/** @persistent */
    public $id;

	/** @inject @var Schmutzka\Models\User */
    public $userModel;


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {	
        $source = new NiftyGrid\DataSource($this->userModel->all()->where("role != ?", "admin"));
        $this->setDataSource($source);
		$this->setModel($this->userModel);

		// grid structure
		$this->addColumn("email", "Email", "25%");
		$this->addColumn("name", "Jméno", "20%")->setRenderer(function ($row) {
			return $row->name . " " . $row->surname;
		});

		$this->addColumn("created", "Registrován", "15%")->setDateRenderer();
		$this->addColumn("last_active", "Poslední aktivita", "15%")->setDateRenderer();

		$this->addColumn("role", "Role", "10%")->setListRenderer((array) $this->settings["roles"]);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(NULL, TRUE);
    }

}