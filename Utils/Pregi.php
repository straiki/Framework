<?php

namespace Schmutzka\Utils;

class Pregi extends \Nette\Object
{

	/**
	 * Remove links from string
	 * @param string
	 */
	public static function removeLinks($string)
	{
		$pattern = '~(<a href="[^"]*">)([^<]*)(</a>)~';
		$string = preg_replace($pattern, '$2', $string);

		return $string;
	}

}