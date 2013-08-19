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

		$items = array();
		if (isset($moduleParams->menu->items)) {
			foreach ($moduleParams->menu->items as $item) {
				if ( ! isset($item->cond)) {
					$items[] = $item;

				} elseif ($moduleParams->{$item->cond}) {
					$items[] = $item;
				}
			}
		}

		$this->template->icon = $moduleParams->menu->icon;
		$this->template->items = $items;
		$this->template->module = $module;
		$this->template->title = $moduleParams->title;
	}


	protected function renderTitle()
	{
		$module = $this->presenter->module;
		$moduleParams = $this->paramService->getModuleParams($module);

		$view = $this->presenter->view;
		$title = '';

		if ($view == 'add') {
			$link = substr($this->presenter->name, strlen($module) + 1);

			if (isset($moduleParams->menu->items)) {
				foreach ($moduleParams->menu->items as $item) {
					if (Strings::contains($item->link, $link)) {
						$title = $item->label;
					}
				}

			} else {
				$title = $moduleParams->title;
			}

			$title .= ' - nová položka';

		} elseif (Strings::startsWith($view, 'edit')) {
			$item = $this->presenter->template->item;
			$title = 'Úprava položky' .
				(isset($item['title']) ? ': ' . $item['title'] :
					(isset($item['name']) ? ': ' . $item['name'] :
						(isset($item['login']) ? ': ' . $item['login'] :
					NULL)));

		} elseif (isset($moduleParams->menu->items)) {
			$link = substr($this->presenter->name . ':' . $view, strlen($module) + 1);
			foreach ($moduleParams->menu->items as $item) {
				if ($item->link == $link) {
					$title = $item->label;
				}
			}

		} else {
			$title = $moduleParams->title;
		}

		$this->template->title = $title;
	}

}
