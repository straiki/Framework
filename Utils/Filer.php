<?php

namespace Schmutzka\Utils;

use Schmutzka\Utils\Name;

class Filer extends \Nette\Utils\Neon
{

	/**
	 * Check file
	 * @param \Nette\Http\FileUpload
	 * @param array
	 */
	public static function checkImage(\Nette\Http\FileUpload $file, $allowed = array("jpg", "png"))
	{
		$suffix = Name::suffix($file->name);

		if ($file->isOk() AND $file->isImage() AND in_array($suffix, $allowed)) {
			return $suffix;
		}

		return FALSE;
	}


	/**
	 * Simple image move to location
	 * @param \Nette\Http\FileUpload
	 * @param string
	 * @param string
	 * @param array
	 * @param string
	 */
	public static function moveImage(\Nette\Http\FileUpload $file, $folder, $name, $allowedTypes = array("jpg", "png"))
	{
		if ($suffix = self::checkImage($file, $allowedTypes)) {

			$fileName = $name . "." . $suffix;
			if ($file->move($folder . "/" . $fileName)) {
				return $fileName;
			}
		}

		return FALSE;
	}

}