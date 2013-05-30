<?php

namespace EmailModule\Grids;

use NiftyGrid;
use Schmutzka\Forms\Form;

class CustomEmailGrid extends NiftyGrid\Grid
{
	/** @persistent @var int */
    public $id;

	/** @inject @var Schmutzka\Models\CustomEmail */
    public $customEmailModel;


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->customEmailModel->all());
        $this->setDataSource($source);
        $this->setModel($this->customEmailModel);

		// grid structure
		$this->addColumn("name", "Název", "20%");
		$this->addColumn("uid", "Systémové UID", "20%");
		$this->addColumn("subject", "Předmět", "55%", 300);

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
    }

}