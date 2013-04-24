<?php

namespace EventModule\Grids;

use Schmutzka;
use Models;
use NiftyGrid;

class EventCategoryGrid extends NiftyGrid\Grid
{
	/** @var Models/EventCategory */
    protected $model;

	/** @param Schmutzka\Services\ParamService */
    private $paramService;


	/**
	 * @param Models\EventCategory
	 * @param Schmutzka\Services\ParamService
	 */
    public function __construct(Models\EventCategory $model, Schmutzka\Services\ParamService $paramService)
    {
        parent::__construct();
        $this->model = $model;
		$this->paramService = $paramService;
    }


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->model->all());
        $this->setDataSource($source);

		$this->addColumn("name", "Název", "50%");
		if ($this->paramService->params["cmsParams"]["event_module_enable_expiration"]) {
			$this->addColumn("use_expiration", "Expirovat", "20%")->setBoolRenderer();
		}
		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}