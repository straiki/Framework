<?php

namespace EventModule\Grids;

use Nette;
use Schmutzka;
use Models;
use NiftyGrid;

class EventGrid extends NiftyGrid\Grid
{
	/** @persistent */
    public $id;

	/** @var Models\Event */
    protected $model;

	/** @var Models\EventCategory  */
    private $eventCategoryModel;

	/** @var Models\Gallery */
    private $galleryModel;

	/** @var Schmutzka\Services\ParamService */
    private $paramService;


	/**
	 * @param Models\Event
	 * @param Models\EventCategory
	 * @param Models\Gallery
	 * @param Schmutzka\Services\ParamService
	 */
    public function __construct(Models\Event $model, Models\EventCategory $eventCategoryModel, Models\Gallery $galleryModel, Schmutzka\Services\ParamService $paramService)
    {
        parent::__construct();
        $this->model = $model;
		$this->eventCategoryModel = $eventCategoryModel;
		$this->galleryModel = $galleryModel;
		$this->paramService = $paramService;
    }


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DataSource($this->model->all()->order("date DESC, time DESC"));
        $this->setDataSource($source);

		// grid structure
		$this->addColumn("title", "Název", "15%");

		if ($this->paramService->params["cmsParams"]["event_module_enable_categories"] && $categoryList = $this->eventCategoryModel->fetchPairs("id", "name")) {
			$this->addColumn("event_category_id", "Kategorie", "20%")->setListRenderer($categoryList);
		}
		$this->addColumn("date", "Datum", "10%")->setDateRenderer("j. n. Y");
		$this->addColumn("time", "Čas", "10%")->setDateRenderer("H:i");

		if ($this->paramService->params["cmsParams"]["event_module_enable_gallery_link"] && $galleryList = $this->galleryModel->fetchPairs("id", "name")) {
			$this->addColumn("gallery_id", "Galerie", "18%")->setListRenderer($galleryList);
		}
		if ($this->paramService->params["cmsParams"]["event_module_enable_calendar"]) {
			$this->addColumn("display_in_calendar", "V kalendáři", "8%", 300)->setBoolRenderer();
		}

		$this->addEditButton(NULL, TRUE);
		$this->addDeleteButton(); 
    }

}