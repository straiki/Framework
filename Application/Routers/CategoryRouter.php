<?php

/**
 * Use:

$categoryRoute = new CategoryRoute("products[/<category>]", array(
	"presenter" => "products",
	"action" => "default",
	"category" => array(
		Route::FILTER_OUT => function ($id) use ($container) {
			if (!is_numeric($id)) {
				return $id;
			} else {
				$cache = $container->cache;
				$category = $cache->load("category"); // načteme kategorie

				// ověření, že kategorie jsou načteny
				if($category === NULL) {
					$category = BasePresenter::loadXmlToArray("category");
					$cache->save("category", $category);
				}

				$page = Arrays::findByKeyValue($category, "ID", $id);
				return Strings::webalize($page["NAME"]);
			}
		}
	)
));
$categoryRoute->cache = $container->cache;
$container->router[] = $categoryRoute;

*/

use Schmutzka\Utils\Arrays,
	Nette\Utils\Strings;

class CategoryRoute extends \Nette\Application\Routers\Route
{

	/** @var cache */
	public $cache;

	public function match(\Nette\Http\IRequest $request)
	{

		/** @var $appRequest \Nette\Application\Request */
		$appRequest = parent::match($request);

		// doplněno: pokud match vrátí NULL, musíme také vrátit NULL
		if($appRequest === NULL) {
			return NULL;
		}

		$category = $this->cache->load("category");

		if(!is_numeric($appRequest->parameters['category']) AND !empty($appRequest->parameters['category'])) {
			$pageId = NULL;
			foreach($category as $key => $value) {
				if(Strings::webalize($value["NAME"]) == $appRequest->parameters["category"]) {
					$pageId = $value["ID"];
				}
			}


			if($pageId === NULL) {
				return NULL;
			}


			$page = Arrays::findByKeyValue($category, "ID", $pageId);
			
			if($page == NULL) {
				return NULL;
			}

			$params = $appRequest->parameters;
			$params['category'] = $page["ID"];
			$appRequest->parameters = $params;

		}

		return $appRequest;
	}

}