<?php

namespace Schmutzka;

use Nette;


class Utils extends Nette\Object
{

	/**
	 * Convert xml object/file to array
	 * @param SimpleXMLElement|string
	 * @return array
	 */
	public static function xmlToArray($xml)
	{
		if (is_file($xml)) {
			$xml = simplexml_load_file($xml);
		}

		return json_decode(json_encode((array) $xml), 1);
	}

}
