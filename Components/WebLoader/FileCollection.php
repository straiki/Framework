<?php

namespace WebLoader;

use Nette\Utils\Finder;

/**
 * FileCollection
 *
 * @author Jan Marek
 */
class FileCollection implements IFileCollection
{

	/** @var string */
	private $root;

	/** @var array */
	private $files = array();

	/** @var array */
	private $remoteFiles = array();

	/**
	 * @param string|null $root files root for relative paths
	 * @param bool|int include all files from the folder
	 */
	public function __construct($root = NULL, $autoload = FALSE)
	{
		$this->root = $root;

		if ($autoload == 2) { // load files from dir
			$suffix = pathinfo($root, PATHINFO_FILENAME);
			foreach(Finder::findFiles("*.".$suffix)->from($this->root) as $key => $file) {
				$this->addFile($key);
			}
		}
		elseif ($autoload == 1) { // load files from dir
			$suffix = pathinfo($root, PATHINFO_FILENAME);
			foreach(Finder::findFiles("*.".$suffix)->in($this->root) as $key => $file) {
				$this->addFile($key);
			}
		}
	}

	/**
	 * Get file list
	 * @return array
	 */
	public function getFiles()
	{
		return array_values($this->files);
	}

	/**
	 * Make path absolute
	 * @param $path string
	 * @throws \WebLoader\FileNotFoundException
	 * @return string
	 */
	public function cannonicalizePath($path)
	{
		$rel = realpath($this->root . "/" . $path);
		if ($rel !== false) return $rel;

		$abs = realpath($path);
		if ($abs !== false) return $abs;

		throw new FileNotFoundException("File '$path' does not exist.");
	}


	/**
	 * Add file
	 * @param $file string filename
	 */
	public function addFile($file)
	{
		if (strpos($file, "http://") !== FALSE) {
			$this->addRemoteFile($file);
		}
		else {
			$file = $this->cannonicalizePath($file);

			if (in_array($file, $this->files)) {
				return;
			}

			$this->files[] = $file;
		}
	}


	/**
	 * Add files
	 * @param $files array list of files
	 */
	public function addFiles(array $files)
	{
		foreach ($files as $file) {	
			$this->addFile($file);
		}
	}


	/**
	 * Remove file
	 * @param $file string filename
	 */
	public function removeFile($file)
	{
		$this->removeFiles(array($file));
	}


	/**
	 * Remove files
	 * @param array $files list of files
	 */
	public function removeFiles(array $files)
	{
		$files = array_map(array($this, 'cannonicalizePath'), $files);
		$this->files = array_diff($this->files, $files);
	}


	/**
	 * Add file in remote repository (for example Google CDN).
	 * @param string $file URL address
	 */
	public function addRemoteFile($file)
	{
		if (in_array($file, $this->remoteFiles)) {
			return;
		}

		$this->remoteFiles[] = $file;
	}

	public function addRemoteFiles(array $files)
	{
		foreach ($files as $file) {
			$this->addRemoteFile($file);
		}
	}

	/**
	 * Remove all files
	 */
	public function clear()
	{
		$this->files = array();
		$this->remoteFiles = array();
	}

	/**
	 * @return array
	 */
	public function getRemoteFiles()
	{
		return $this->remoteFiles;
	}

	/**
	 * @return string
	 */
	public function getRoot()
	{
		return $this->root;
	}

}
