<?php

namespace EmailModule\Components;

use NiftyGrid;
use Schmutzka\Application\UI\Module\Grid;

class CustomEmailGrid extends Grid
{
	/** @inject @var Schmutzka\Models\CustomEmail */
    public $customEmailModel;


	/**
	 * @param presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->customEmailModel->fetchAll());
        $this->setDataSource($source);
        $this->setModel($this->customEmailModel);

		$this->addColumn("name", "Název");
		$this->addColumn("uid", "Systémové UID", "30%");
		$this->addColumn("subject", "Předmět", "30%", 300);
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton();
    }

}
