<?php

namespace FileModule\Grids;

use Nette;
use Schmutzka;
use NiftyGrid;

class FileGrid extends NiftyGrid\Grid
{
	/** @var Schmutzka\Models\File */
    private $fileModel;

	/** @var Schmutzka\Models\User */
    private $userModel;

	/** @var array */
	private $moduleParams;


    public function inject(Schmutzka\Models\File $fileModel, Schmutzka\Models\User $userModel, Schmutzka\Config\ParamService $paramService)
    {
		$this->fileModel = $fileModel;
		$this->userModel = $userModel;
		$this->moduleParams = $paramService->getModuleParams($this->getReflection()->getName());
    }


    protected function configure(Nette\Application\IPresenter $presenter)
    {
        $source = new NiftyGrid\DataSource($this->fileModel->all());
        $this->setDataSource($source);
		$this->setModel($this->fileModel);

		$this->addColumn("name", "Název");
		$this->addColumn("created", "Upraveno", "15%")->setDateRenderer();
		$this->addColumn("user_id", "Přiřazeno k", "15%")->setListRenderer($this->userModel->fetchPairs("id", "login"));

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}
