<?php

namespace Schmutzka\Utils;

class Neon extends \Nette\Utils\Neon
{

	/**
	 * Load a config file or it's part
	 * @param file
	 * @param string
	 */
	public static function loadConfigPart($file, $part = NULL)
	{
		if (!file_exists(APP_DIR."/config/$file")) {
			throw new \Exception("Missing 'config/$file'.");
		}

		$file = file_get_contents(APP_DIR."/config/$file");
		$fileDecoded = \Nette\Utils\Neon::decode($file);

		if ($part) {
			if (!isset($fileDecoded[$part])) {
				throw new \Exception("Key '$part' does not exits.");
			}

			return $fileDecoded[$part];
		}

		return array_shift($fileDecoded);
	}

}