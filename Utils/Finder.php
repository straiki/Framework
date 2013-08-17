<?php

namespace Schmutzka\Utils;

use Nette;


class Finder extends Nette\Utils\Finder
{

	/**
	 * Convert found files to array list
	 * @return array
	 */
	public function toArray()
	{
		$array = array();
		foreach ($this as $name => $info) {
			$uid = pathinfo($info->getFilename(), PATHINFO_FILENAME);
			$array[$uid] = $name;
		}

		return $array;
	}

}
