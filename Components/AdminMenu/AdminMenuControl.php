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
	 */
	protected function renderDefault($module)
	{
		$moduleParams = $this->paramService->getModuleParams($module);

		$this->template->menu = $moduleParams->menu;
		if (isset($menu->primaryAdminOnly) && $this->user->id != 1) {
			return;
		}

		$this->template->module = $module;
		$this->template->title = $moduleParams->title;
	}


	protected function renderTitle()
	{
		$module = $this->presenter->module;
		$moduleParams = $this->paramService->getModuleParams($module);

		$view = $this->presenter->view;
		$title = NULL;
		$menu = $this->getMenu($module);

		if ($view == 'add') {
			$path = substr($this->presenter->name, strlen($module) + 1);
			foreach ($moduleParams->menu->items as $key => $row) {
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
			$title = $moduleParams->title;
		}

		$this->template->title = $title;
	}

}
