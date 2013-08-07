<?php

namespace Schmutzka\Application\Routers;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Schmutzka;
use Schmutzka\Utils\Name;


class ModuleRouterFactory
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Nette\Caching\Cache */
	public $cache;

	/** @var array */
	protected $customModules = array();

	/** @var Schmutzka\Models\Page */
	private $pageModel;

	/** @var Schmutzka\Models\Article */
	private $articleModel;


	public function injectModels(Schmutzka\Models\Page $pageModel = NULL, Schmutzka\Models\Article $articleModel = NULL)
	{
		$this->pageModel = $pageModel;
		$this->articleModel = $articleModel;
	}


	public function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route('index.php', 'Front:Homepage:default', Route::ONE_WAY);
		$router[] = new Route('index.php', 'Admin:Homepage:default', Route::ONE_WAY);

		$modules = (array) $this->paramService->modules;
		$router[] = new Route('<module admin|' . Name::upperToDashedLower(implode($modules, '|')) . '>/<presenter>/<action>[/<id>]', 'Homepage:default');

		if (isset($modules['page'])) {
			$frontRouter = $router[] = new RouteList('Front');
			$frontRouter[] = new PairsRoute('<id>', 'Page:detail', NULL, $this->pageModel, $this->cache, $columns = array('id', 'url'));
		}

		if (isset($modules['article'])) {
			$frontRouter = $router[] = new RouteList('Front');
			$frontRouter[] = new PairsRoute('<id>', 'Article:detail', NULL, $this->articleModel, $this->cache, $columns = array('id', 'url'
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
		$frontRouter = $router[] = new RouteList('Front');
		$frontRouter[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $frontRouter;
	}

}
