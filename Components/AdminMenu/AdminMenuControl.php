<?php

namespace Components;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Control;


class AdminMenuControl extends Control
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Nette\Http\Request */
	public $httpRequest;

	/** @inject @var Schmutzka\Security\User */
	public $user;


	/**
	 * @param string
	 * @param string
	 */
	public function renderDefault($module, $title)
	{
		$this->template->menu = $menu = $this->getMenu($module);
		if (isset($menu->primaryAdminOnly) && $this->user->id != 1) {
			return;
		}

		$this->template->moduleParams = $this->paramService->getModuleParams($module);
		$this->template->module = $module;
		$this->template->title = $title;
	}


	public function renderTitle()
	{
		$module = $this->presenter->module;

		$view = $this->presenter->view;
		$title = NULL;
		$menu = $this->getMenu($module);

		if ($view == 'add') {
			$path = substr($this->presenter->name, strlen($module) + 1);
			foreach ($menu->items as $key => $row) {
				if (Strings::contains($row->path, $path)) {
					$title = $key;
				}
			}

			$title .= ' - nová položka';

		} elseif (Strings::startsWith($view, 'edit')) {
			$item = $this->presenter->template->item;
			$title = 'Úprava položky' .
				(isset($item['title']) ? ': ' . $item['title'] :
					(isset($item['name']) ? ': ' . $item['name'] :
						(isset($item['login']) ? ': ' . $item['login'] :
					NULL)));

		} elseif (isset($menu->items)) {
			$path = substr($this->presenter->name . ':' . $view, strlen($module) + 1);
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
	}


	/********************** helpers **********************/


	/**
	 * Get  by module name
	 * @param  string
	 * @return Nette\ArrayHash
	 */
	public function getMenu($module)
	{
		if (file_exists($config = MODULES_DIR . '/' . ucfirst($module) . 'Module/config/menu.neon')) {
			$config = Nette\Utils\Neon::decode(file_get_contents($config));

		} elseif(file_exists($config = APP_DIR . '/' . ucfirst($module) . 'Module/config/menu.neon')) {
			$config = Nette\Utils\Neon::decode(file_get_contents($config));
		}

		return Nette\ArrayHash::from((array) $config);
	}

}
