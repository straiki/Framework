<?php

namespace Schmutzka\Utils;

use Nette\Utils\Strings;

final class Name extends \Nette\Object
{

	/** @var array */
	private static $convert = array(
		"jpeg" => "jpg"
	);


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

		if (isset(self::$convert[$suffix])) {
			$suffix = self::$convert[$suffix];
		}

		return $suffix;
	}

}