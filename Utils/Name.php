<?php

namespace Schmutzka\Utils;

use Nette;

/**
 * tableFromClass($class)
 * mpv($activePresenter, $part = NULL)
 */

class Name extends Nette\Object
{

	/**
	 * Get table name by class name
	 * @param string
	 * @return string
	 * @example Pages => pages, ArticleTag => article_tag
	 */
	public static function tableFromClass($class)
	{
		$table = explode("\\", $class);
		$table = lcfirst(array_pop($table));

		$replace = array();
		foreach (range("A", "Z") as $letter) {
			$replace[$letter] = "_" . strtolower($letter);
		}

		return strtr($table, $replace); 
	}


	/**
	 * Modul/presenter/view
	 * @param Presenter
	 * @param string
	 */
	public static function mpv($activePresenter, $part = NULL)
	{
		$module = NULL;
		$presenter = $activePresenter->name;
		if (strpos($presenter, ":")) {
			list($module, $presenter) = explode(":", $presenter, 2);
		}
		$view = lcfirst($activePresenter->view);
		$presenter = lcfirst($presenter);
		$module = lcfirst($module);

		if ($part == "module") {
			return $module;

		} elseif ($part == "presenter") {
			return $presenter;

		} elseif ($part == "view") {
			return $view;
		} 

		return array($module, $presenter, $view);
	}

}