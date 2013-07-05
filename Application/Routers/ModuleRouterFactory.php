<?php

namespace Schmutzka\Application\Routers;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Schmutzka\Utils\Name;

class ModuleRouterFactory
{
	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @var array */
	protected $customModules = array();


	public function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route("index.php", "Front:Homepage:default", Route::ONE_WAY);
		$router[] = new Route("index.php", "Admin:Homepage:default", Route::ONE_WAY);

		$activeModules = array_keys($this->paramService->getActiveModules());
		$router[] = new Route("<module admin|" . Name::upperToDashedLower(implode($activeModules + $this->customModules, "|")) . ">/<presenter>/<action>[/<id>]", "Homepage:default");

		return $router;
	}


	/**
	 * @param  self
	 * @return self
	 */
	public function createFrontRouter($router)
	{
		$frontRouter = $router[] = new RouteList("Front");
		$frontRouter[] = new Route("<presenter>/<action>[/<id>]", "Homepage:default");

		return $frontRouter;
	}

}
