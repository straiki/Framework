<?php

namespace Schmutzka\Utils;

use Schmutzka\Utils\Arrays;

class Strings extends \Nette\Object
{

	/**
	 * Split by specific chars
	 * @param string
	 * @param string
	 */
	public static function split($value, $needle)
	{
		$array = \Nette\Utils\Strings::split($value, $needle);
		$array = Arrays::clearEmpty($array);
		return $array;
	}	


	/**
	 * Mirror of Nette\Utils\Strings
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		return callback("\Nette\Utils\Strings", $name)->invokeArgs($args);
	}

}