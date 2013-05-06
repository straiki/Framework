<?php

namespace Schmutzka\Utils;

use Nette;
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
		$array = Nette\Utils\Strings::split($value, $needle);
		$array = Arrays::clearEmpty($array);
		return $array;
	}

}
