<?php

namespace Schmutzka\Utils;

use Nette;


class Name extends Nette\Object
{

	/**
	 * Get table name by class name
	 * @param string
	 * @return string
	 * @example Models\Pages => pages, Models\ArticleTag => article_tag
	 */
	public static function tableFromClass($class)
	{
		$table = explode('\\', $class);
		$table = lcfirst(array_pop($table));

		return self::upperToUnderscoreLower($table);
	}


	/**
	 * @param string
	 * @return string
	 * @example CustomModule => custom-module
	 */
	public static function upperToDashedLower($string)
	{
		return strtr($string, self::getReplaceAlphabetBy('-'));
	}


	/**
	 * @param string
	 * @return string
	 * @example customTable => custom_table
	 */
	public static function upperToUnderscoreLower($string)
	{
		return strtr($string, self::getReplaceAlphabetBy('_'));
	}


	/**
	 * Get component template name from class + check if exists
	 * @param Nette\Application\UI\PresenterComponentReflection
	 * @param string
	 * @return string|NULL
	 */
	public static function templateFromReflection(Nette\Application\UI\PresenterComponentReflection $reflection, $name = NULL)
	{
		$file = dirname($reflection->getFileName()) . '/' . $reflection->getShortName() . ucfirst($name) . '.latte';
		if (file_exists($file)) {
			return $file;
		}

		return NULL;
	}


	/**
	 * Modul/presenter/view
	 * @param Presenter
	 * @param string
	 * @return string
	 */
	public static function mpv($activePresenter, $part = NULL)
	{
		$module = NULL;
		$presenter = $activePresenter->name;
		if (strpos($presenter, ':')) {
			list($module, $presenter) = explode(':', $presenter, 2);
		}
		$view = lcfirst($activePresenter->view);
		$presenter = lcfirst($presenter);
		$module = lcfirst($module);

		if ($part == 'module') {
			return $module;

		} elseif ($part == 'presenter') {
			return $presenter;

		} elseif ($part == 'view') {
			return $view;
		}

		return array($module, $presenter, $view);
	}


	/**
	 * Modul from namespace
	 * @param string
	 * @return string
	 */
	public static function moduleFromNamespace($namespace)
	{
		$temp = explode('\\', $namespace);
		$module = substr($temp[0], 0, -6);
		$module = lcfirst($module);

		return $module;
	}


	/********************** helpers **********************/


	/**
	 * @param  string
	 * @return array
	 */
	private static function getReplaceAlphabetBy($char)
	{
		$replace = array();
		foreach (range('A', 'Z') as $letter) {
			$replace[$letter] = $char . strtolower($letter);
		}

		return $replace;
	}


}
