<?php

namespace Schmutzka\Utils;

use Nette\Utils\Strings;

final class Name extends \Nette\Object
{

	/** @var array */
	private static $convertSuffix = array(
		"jpeg" => "jpg"
	);


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
	 * Presenter name
	 * @param string
	 * @return string
	 */
	public static function presenter($name)
	{
		if (strpos($name, ":") == TRUE) {
			$temp = explode(":", $name);
			return array_pop($temp);
		}

		return $name;
	}


	/**
	 * Modul/presenter/view
	 * @param Presenter
	 */
	public static function mpv($activePresenter)
	{
		$module = NULL;
		$presenter = $activePresenter->name;
		if (strpos($presenter, ":")) {
			list($module, $presenter) = explode(":", $presenter, 2);
		}
		$view = $activePresenter->view;
	
		return array($module, $presenter, $view);
	}


	/**
	 * Get suffix
	 * @param string
	 * @return string
	 */
	public static function suffix($name)
	{
		$temp = explode(".", $name);
		$suffix = array_pop($temp);
		$suffix = strtolower($suffix);

		if (isset(self::$convertSuffix[$suffix])) {
			$suffix = self::$convertSuffix[$suffix];
		}

		return $suffix;
	}

}