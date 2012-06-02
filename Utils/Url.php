<?php

namespace Schmutzka\Utils; // that's me!

use Nette\Diagnostics\Debugger,
	Nette\Utils\Validators;

class Url extends \Nette\Object
{

	/** @var string */
	private static $bitLyLogin = "schmutzka";

	/** @var string */
	private static $bitLyKey = "R_8e56cbee2f90e3dc7193313451edb715";


	/**
	 * Converts url to bit.ly version
	 * @param string
	 * @throws \Exception
	 */
	public static function bitLy($url)
	{
		if (Validators::isUrl($url)) {

 			$url = urlencode($url); // makes & possible etc.
			$ping = "http://api.bitly.com/v3/shorten?login=".self::$bitLyLogin."&apiKey=".self::$bitLyKey."&longUrl=".$url;

			$file  = file_get_contents($ping);
			$data = json_decode($file);

			if ($data->data->url) {
				return $data->data->url;
			}

			return urldecode($url);
		}
		else { // wrong type
			throw \Exception("$url is not an url.");
		}
	}

}