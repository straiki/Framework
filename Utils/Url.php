<?php

namespace Schmutzka\Utils;

use Nette;
use Schmutzka\Utils\Validators;

/**
 * shorten($url)
 * linkifyText($string, $linkNode = NULL)
 */

class Url extends Nette\Object
{
	/** @var string */
	private static $bitLyLogin = 'schmutzka';

	/** @var string */
	private static $bitLyKey = 'R_8e56cbee2f90e3dc7193313451edb715';


	/**
	 * Converts url to bit.ly version
	 * @param string
	 */
	public static function shorten($url)
	{
		if (Validators::isUrl($url)) {
			if (strpos($url, 'http://bit.ly/') !== FALSE) {
				return $url;
			}

 			$url = urlencode($url); // makes & possible etc.
			$ping = 'http://api.bitly.com/v3/shorten?login=' . self::$bitLyLogin . '&apiKey=' . self::$bitLyKey . '&longUrl=' . $url;

			$file  = file_get_contents($ping);
			$data = json_decode($file);

			if ($data->data->url) {
				return $data->data->url;
			}

			return urldecode($url);

		} else { // wrong type
			throw new \Exception('$url is not an url.');
		}
	}


	/**
	 * Linkify text
	 * @param string
	 * @param bool
	 */
	public static function linkifyText($string, $linkNode = NULL)
	{
		if ($linkNode) {
			return preg_replace('#((http|https|ft​p)://(\S*?\.\S*?))(\s|\;|\)|​\]|\[|\{|\}|,|\'|'|:[0-9]{1,5}|\<|$|\.\s)#ie', ''<a href=\'$1$4\' target=\'_blank\'>'. $linkNode . '</a>'', $string);

		} else {
			return preg_replace('#((http|https|ft​p)://(\S*?\.\S*?))(\s|\;|\)|​\]|\[|\{|\}|,|\'|'|:[0-9]{1,5}|\<|$|\.\s)#ie', ''<a href=\'$1$4\' target=\'_blank\'>$1$4</a>'', $string);
		}
	}

}