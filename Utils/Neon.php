<?php

namespace Schmutzka\Utils;

use Nette;

class Neon extends Nette\Utils\Neon
{

	/**
	 * Load a config file or it's part
	 * @param file
	 * @param string
	 */
	public static function fromFile($file, $part = NULL)
	{
		$file = self::loadFile($file);
		$fileDecoded = Nette\Utils\Neon::decode($file);

		if ($part) {
			if (isset($fileDecoded[$part])) {
				return $fileDecoded[$part];
			}

			throw new \Exception("Section '$part' does not exits.");
		}

		return $fileDecoded;
	}


	/**
	 * Load config file
	 * @param string
	 * @return string
	 */
	private static function loadFile($file)
	{
		if ( ! file_exists($file)) {
			throw new \Exception('File does not exists');
		}

		return file_get_contents($file);
	}

}
