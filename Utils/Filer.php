<?php

namespace Schmutzka\Utils;

use Schmutzka\Utils\Name;
use Nette;
use Nette\Mail\MimePart;
use Nette\Utils\MimeTypeDetector;
use Nette\Utils\Strings;
use Nette\Utils\Finder;
use Nette\Image;


class Filer extends Nette\Object
{
	/** @var array */
	private static $convertExtension = array(
		'jpeg' => 'jpg'
	);


	/**
	 * Get file extension
	 * @param string
	 */
	public static function extension($name)
	{
		$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		if (isset(self::$convertExtension[$extension])) {
			$extension = self::$convertExtension[$extension];
		}

		return $extension;
	}


	/**
	 * Empty all folders and all files from particular folder
	 * @param string
	 */
	public static function emptyFolder($folder)
	{
		if (is_dir($folder)) {
			chmod($folder, 0777);

			foreach (Finder::findFiles('*')->from($folder) as $item => $info) {
				if (file_exists($item)) {
					unlink($item);
				}
			}

			foreach (Finder::findDirectories('*')->in($folder) as $item => $info) {
				rmdir($item);
			}
		}
	}


	/**
	 * Let download file under different name
	 * @param string
	 * @param string
	 * @param string
	 */
	public static function downloadAs($file, $name, $type = NULL)
	{
		if (is_file($file)) {
			$content = file_get_contents($file);

		} else {
			$content = $file;
		}

		header('Content-type: ' . $type ?: MimeTypeDetector::fromString($content));
		header('Content-Disposition: attachment; filename="' . $name . '"');
		readfile($file);
		die;
	}


	/**
	 * @param Nette\Http\FileUpload
	 * @param array
	 */
	public static function checkFile(Nette\Http\FileUpload $file, $allowed, $image = FALSE)
	{
		if ($image && ! $file->isImage()) {
			return FALSE;
		}

		$ext = self::extension($file->name);

		if ($file->isOk() && in_array($ext, $allowed)) {
			return $ext;
		}

		return FALSE;
	}


	/**
	 * Check file if image
	 * @param Nette\Http\FileUpload
	 * @param array
	 */
	public static function checkImage(Nette\Http\FileUpload $file, $allowed = array('jpg', 'png'))
	{
		return self::checkFile($file, $allowed, TRUE);
	}


	/**
	 * Simple file move to location
	 * @param Nette\Http\FileUpload
	 * @param string
	 * @param bool
	 * @param string
	 * @param array
	 * @param bool
	 */
	public static function moveFile(Nette\Http\FileUpload $file, $dir, $keepUnique = TRUE, $oldFile = NULL, $alterImage = array(), $encryptName = FALSE)
	{
		// @todo fix
		$hostDir = WWW_DIR . '/' . $dir;

		if ($oldFile) {
			if (file_exists($hostDir . $oldFile)) {
				unlink($hostDir . $oldFile);
			}
		}

		// alter image
		$alterWidth = isset($alterImage['width']) ? $alterImage['width'] : NULL;
		$alterHeight = isset($alterImage['height']) ? $alterImage['height'] : NULL;
		$alterName = ($alterWidth ? $alterWidth . '_' : NULL) . ($alterHeight ? $alterHeight .'_' : NULL);

		$name = $origName = Strings::webalize($alterName . $file->getName(), '.');
		$i = 1;

		// alter image
		if ($alterImage && $file->isImage()) {
			$image = $file->toImage();
			$image->resize($alterWidth, $alterHeight);
		}

		if ($encryptName) {
			$name = Strings::random(12);
		}

		while ($keepUnique && file_exists($hostDir . $name)) {
			if ($encryptName) {
				$name = Strings::random(12);

			} else {
				$filename = pathinfo($name, PATHINFO_FILENAME);
				$extension = self::extension($name);
				$name = $filename . '_' . $i++ . '.' . $extension;
			}
		}

		if ($alterImage && $image->save($hostDir . $name)) {
			return $dir . $name;

		} else {
			if ($file->move($hostDir . $name)) {
				return $dir . $name;
			}
		}

		return FALSE;
	}


	/**
	 * Resize to subfolder
	 * @param Nette\Http\FileUpload|Nette\Image
	 * @param string
	 * @param int|NULL
	 * @param int|NULL
	 * @param string
	 */
	public static function resizeToSubfolder($file, $dir, $width = NULL, $height = NULL, $filename)
	{
		if ($file instanceof Nette\Http\FileUpload) {
			$image = $file->toImage();

		} else {
			$image = $file;
		}

		if ($width && $height) {
			$image->resize($width, $height, Image::SHRINK_ONLY | Image::EXACT);

		} else {
			$image->resize($width, $height, Image::SHRINK_ONLY); // ignore Image::EXACT on one param NULL
		}

		$dir .= self::createFolderName($width, $height) . '/';
		if (! file_exists($dir)) {
			mkdir($dir);
		}

		$image->save($dir . $filename);
	}


	/**
	 * Get unique name
	 * @param string $dir
	 * @param string $file
	 * @return string filename encoded in sha1
	 */
	public static function getUniqueName($dir, $file)
	{
		$ext = self::extension($file);
		$name = sha1($file) . '.' . $ext;

		if (is_dir($dir)) {
			while (file_exists($dir . '/' . $name)) {
				$name = sha1($file . Strings::random()) . '.' . $ext;
			}
		}

		return $name;
	}


	/********************** helpers **********************/


	/**
	 * @param int
	 * @param int
	 * @return string
	 */
	private static function createFolderName($width = NULL, $height = NULL)
	{
		if ($width == NULL && $height == NULL) {
			throw new Exception('At least one size has to be specified.');
		}

		$name = '';
		if ($width) {
			$name .= 'w' . $width;
		}

		if ($height) {
			if ($name) {
				$name .= '_';
			}
			$name .= 'h' . $height;
		}

		return $name;
	}

}
