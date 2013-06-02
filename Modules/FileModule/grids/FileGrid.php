<?php

namespace FileModule\Grids;

use NiftyGrid;
use Schmutzka;
use Schmutzka\Application\UI\Module\Grid;

class FileGrid extends Grid
{
	/** @inject @var Schmutzka\Models\File */
    public $fileModel;


	/**
     * @param Nette\Application\IPresenter  
     */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->fileModel->fetchAll());
        $this->setDataSource($source);
		$this->setModel($this->fileModel);

		$this->addColumn("name", "Název", "40%");
		$this->addColumn("created", "Upraveno", "15%")
			->setDateRenderer();
		$this->addColumn("user_id", "Přiřazeno k")
			->setListRenderer($this->userModel->fetchPairs("id", "login"));
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}
