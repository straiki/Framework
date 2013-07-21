<?php

namespace Schmutzka\Application\UI\Module;

use NiftyGrid;

abstract class Grid extends NiftyGrid\Grid
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	/********************** helpers **********************/


	/**
	 * @return  Nette\ArrayHash
	 */
	public function getModuleParams()
	{
		return $this->paramService->getModuleParams($this->presenter->module);
	}

}
