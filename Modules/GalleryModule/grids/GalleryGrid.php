<?php

namespace GalleryModule\Grids;

use Schmutzka;
use NiftyGrid;

class GalleryGrid extends NiftyGrid\Grid
{
	/** @persistent */
    public $id;

	/** @var Schmutzka\Models\Gallery */
    protected $model;


	/**
	 * @param Schmutzka\Models\Gallery
	 */
    public function __construct(Schmutzka\Models\Gallery $model)
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

		$this->addColumn("name", "Název");
		$this->addColumn("description", "Popisek");
		$this->addColumn("created", "Vytvořeno")->setDateRenderer();
		$this->addColumn("file_count", "Počet fotek");

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}