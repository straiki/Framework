<?php

namespace Schmutzka\Application\Routers;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Schmutzka\Utils\Name;

class ModuleRouterFactory
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Models\Page */
	public $pageModel;

	/** @inject @var Schmutzka\Models\Article */
	public $articleModel;

	/** @inject @var Nette\Caching\Cache */
	public $cache;

	/** @var array */
	protected $customModules = array();


	public function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route("index.php", "Front:Homepage:default", Route::ONE_WAY);
		$router[] = new Route("index.php", "Admin:Homepage:default", Route::ONE_WAY);

		$activeModules = $this->paramService->getActiveModules();
		$activeModulesKeys = array_keys($activeModules);
		$router[] = new Route("<module admin|" . Name::upperToDashedLower(implode($activeModulesKeys + $this->customModules, "|")) . ">/<presenter>/<action>[/<id>]", "Homepage:default");

		if (isset($activeModules["page"])) {
			$frontRouter = $router[] = new RouteList("Front");
			$frontRouter[] = new PairsRoute("<id>", "Page:detail", NULL, $this->pageModel, $this->cache, $columns = array(
				"id", "url"
			));
		}

		if (isset($activeModules["article"])) {
			$frontRouter = $router[] = new RouteList("Front");
			$frontRouter[] = new PairsRoute("<id>", "Article:detail", NULL, $this->articleModel, $this->cache, $columns = array(
				"id", "url"
			));
		}

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
