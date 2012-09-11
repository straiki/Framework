<?php

namespace Schmutzka\Utils;

use Schmutzka\Utils\Name,
	Nette\Mail\MimePart,
	Nette\Utils\MimeTypeDetector,
	Nette\Utils\Strings;

class Filer extends \Nette\Utils\Neon
{

	/**
	 * Let download file under different name
	 * @param string
	 * @param string
	 */
	public static function downloadAs($file, $name)
	{
		if (is_file($file)) {
			$content = file_get_contents($file);

		} else {
			$content = $file;
		}

		header('Content-type: ' . MimeTypeDetector::fromString($content));
		header('Content-Disposition: attachment; filename="'. $name .'"');
		readfile($file);
		die;
	}


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

		if ($file->isOk() && in_array($suffix, $allowed)) {
			// recheck: Nette\Utils\MimeTypeDetector::fromString
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