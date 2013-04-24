<?php

namespace EmailModule\Grids;

use NiftyGrid;
use Schmutzka\Forms\Form;
use Models;

class CustomEmailGrid extends NiftyGrid\Grid
{
	/** @persistent */
    public $id;

	/** @var Models/CustomEmail */
    protected $model;


	/**
	 * @param Models\CustomEmail
	 */
    public function __construct(Models\CustomEmail $model)
    {
        parent::__construct();
        $this->model = $model;
		$this->useFlashMessage = FALSE;
    }


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->model->all());
        $this->setDataSource($source);

		// grid structure
		$this->addColumn("name", "Název", "20%");
		$this->addColumn("uid", "Systémové UID", "20%");
		$this->addColumn("subject", "Předmět", "55%", 300);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}