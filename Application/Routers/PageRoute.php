<?php

/**
 * Use:

$pageRoute = new PageRoute("<page>/", array(
	"presenter" => "homepage",
	"action" => "default",
	"page" => array(
		Route::FILTER_OUT => function ($id) use ($container) {
			if (!is_numeric($id)) {
				return $id;
			} else {
				$cache = $container->cache;
				$pages = $cache->load("articles"); // načteme kategorie

				// ověření, že stránky jsou načteny
				if($pages === NULL) {
					$pages = BasePresenter::loadXmlToArray("articles");
					$cache->save("pages", $pages);
				}
				$page = Arrays::findByKeyValue($pages, "ID", $id);

				if(isset($page["NAME"])) {
					return Strings::webalize($page["NAME"]);
				}
				elseif(is_array($page)) {
					return Strings::webalize($page[0]["NAME"]);
				}	
				else {
					return $page["ID"];
				}
			}
		}
	)
));

$pageRoute->cache = $container->cache;
$container->router[] = $pageRoute;

*/

use Schmutzka\Utils\Arrays,
	Nette\Utils\Strings;

class PageRoute extends \Nette\Application\Routers\Route
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

		
		$pages = $this->cache->load("pages");
		if(is_numeric($appRequest->parameters['page']) AND !empty($appRequest->parameters['page'])) {

			$pageId = $appRequest->parameters["page"];
			$page = Arrays::findByKeyValue($pages, "ID", $pageId);
			
			if($page == NULL) {
				return NULL;
			}

			$params = $appRequest->parameters;
			$params['page'] = Strings::webalize($page["NAME"]);
			$appRequest->parameters = $params;
		}

		return $appRequest;
	}

}