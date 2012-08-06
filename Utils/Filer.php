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
	public static function checkFile(\Nette\Http\FileUpload $file, $allowed, $image = FALSE)
	{
		if ($image AND !$file->isImage()) {
			return FALSE;
		}

		$suffix = Name::suffix($file->name);

		if ($file->isOk() AND in_array($suffix, $allowed)) {
			return $suffix;
		}

		return FALSE;
	}


	/**
	 * Check file if image
	 * @param \Nette\Http\FileUpload
	 * @param array
	 */
	public static function checkImage(\Nette\Http\FileUpload $file, $allowed = array("jpg", "png"))
	{
		return self::checkFile($file, $allowed, TRUE);
	}


	/**
	 * Simple file move to location
	 * @param \Nette\Http\FileUpload
	 * @param string
	 * @param string
	 * @param array
	 */
	public static function moveFile(\Nette\Http\FileUpload $file, $folder, $name, $suffix)
	{
		$fileName = $name . "." . $suffix;
		if ($file->move($folder . "/" . $fileName)) {
			return $fileName;
		}

		return FALSE;
	}

}