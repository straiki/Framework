<?php

namespace Schmutzka\Utils;

use Nette;


class Neon extends Nette\Utils\Neon
{

	/**
	 * Load a config file or it's part
	 * @param file
	 * @param string
	 * @return  Nette\ArrayHash
	 */
	public static function fromFile($file, $section = NULL)
	{
		if ( ! file_exists($file)) {
			throw new \Exception('File does not exists');
		}

		$file = file_get_contents($file);
		$config = Nette\Utils\Neon::decode($file);

		if ($section && isset($config[$section])) {
			return $config[$section];
		}

		return $config;
	}

}
