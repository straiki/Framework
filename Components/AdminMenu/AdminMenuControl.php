<?php

namespace Components;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Control;

class AdminMenuControl extends Control
{
	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Nette\Http\Request */
	public $httpRequest;


	/**
	 * @param string
	 * @param string
	 */
	public function render($module, $title)
	{
		parent::useTemplate();

		$this->template->menu = $this->getMenu($module);
		$this->template->moduleParams = $this->paramService->getModuleParams($module);
		$this->template->module = $module;
		$this->template->title = $title;
		$this->template->render();
	}


	public function renderTitle()
	{
		parent::useTemplate("title");
		$module = $this->presenter->module;

		$view = $this->presenter->view;
		$title = NULL;
		$menu = $this->getMenu($module);

		if ($view == "add") {
			$path = substr($this->presenter->name, strlen($module) + 1);
			foreach ($menu->items as $key => $row) {
				if (Strings::contains($row->path, $path)) {
					$title = $key;
				}
			}

			$title .= " - nová položka";

		} elseif ($view == "edit") {
			$item = $this->presenter->template->item;
			$title = "Úprava položky" .
				(isset($item["title"]) ? ": " . $item["title"] :
					(isset($item["name"]) ? ": " . $item["name"] :
						(isset($item["login"]) ? ": " . $item["login"] :
					NULL)));

		} elseif (isset($menu->items)) {
			$path = substr($this->presenter->name . ":" . $view, strlen($module) + 1);
			foreach ($menu->items as $key => $row) {
				if ($row->path == $path) {
					$title = $key;
				}
			}

		} else {
			$moduleParams = $this->paramService->getModuleParams($module);
			$title = $moduleParams->title;
		}

		$this->template->title = $title;
		$this->template->render();
	}


	/********************** helpers **********************/


	/**
	 * Get  by module name
	 * @param  string
	 * @return Nette\ArrayHash
	 */
	public function getMenu($module)
	{
		if (file_exists($config = MODULES_DIR . "/" . ucfirst($module) . "Module/config/menu.neon")) {
			$config = Nette\Utils\Neon::decode(file_get_contents($config));

		} elseif(file_exists($config = APP_DIR . "/" . ucfirst($module) . "Module/config/menu.neon")) {
			$config = Nette\Utils\Neon::decode(file_get_contents($config));
		}

		return Nette\ArrayHash::from((array) $config);
	}

}
