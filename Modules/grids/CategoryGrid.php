<?php

namespace Grids;

use Schmutzka;
use Models;
use NiftyGrid;

class CategoryGrid extends NiftyGrid\Grid
{
	/** @var Models/*Category */
    protected $model;


	/**
	 * @param Models\*Category
	 */
    public function __construct($model)
    {
        parent::__construct();
        $this->model = $model;
    }


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->model->all());
        $this->setDataSource($source);

		$this->addColumn("name", "Název");
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}