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
			if (!isset($fileDecoded[$part])) {
				return $fileDecoded[$part];	
			}

			throw new \Exception("Key '$part' does not exits.");
		}

		return $fileDecoded;
	}


	/**
	 * Load config file
	 * @param string
	 */
	private static function loadFile($file)
	{	
		$filePath = APP_DIR . "/config/" . $file;
		if (!file_exists($filePath)) {
			throw new \Exception("Missing 'config/$file'.");
		}

		return file_get_contents($filePath);
	}

}