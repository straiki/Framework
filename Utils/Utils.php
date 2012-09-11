<?php

namespace Schmutzka;

use Nette\Utils\MimeTypeDetector;

class Utils extends \Nette\Object
{

	/**
	 * Return first set argument
	 * @param ... mixed
	 */
	public static function getFirstSet()
	{
		foreach (func_get_args() as $value) {
			if (!empty($value) AND !is_null($value) AND $value) {
				return $value;
			}

			return NULL;
		}
	}


	/**
	 * Get email service from email
	 * @string email
	 * @return domain/NULL
	 */	
	public static function getEmailServer($email)
	{
		list($name,$domain) = explode("@", $email);
		$emailList = array("gmail.com", "centrum.cz", "centrum.sk",  "seznam.cz", "zoznam.sk", "post.cz",  "email.cz", "atlas.cz", "atlas.sk", "hotmail.com", "azet.sk", "yahoo.com", "live.com", "mail.com");

		if (in_array($domain, $emailList)) {
			return $domain;
		}

		return NULL;
	}


	/**
	 * Converts icons to base64 version
	 * @param string
	 * @param array
	 */
	public static function icons2css($folder, $fileTypes = array("*.jpg", "*.gif", "*.png")) 
	{
		$folder = WWW_DIR . "/" . $folder;
		if (!file_exists($folder)) {
			throw new \Nette\InvalidStateException("Directory ". $folder . " not found");
		}

		echo "<code>";
		foreach (Finder::findFiles($fileTypes)->in($folder) as $image) {
			$icon = Strings::webalize(substr($image->getbaseName(), 0, strrpos($image->getbaseName(), ".")));
			$mime = MimeTypeDetector::fromFile($image->getRealPath());

			$imageContent = file_get_contents((string)$image);
			$data = "data:" . $mime . ';base64,' . base64_encode($imageContent);

			echo "." . $icon . " {background-image:url(", $data, ")}" . "<br><br>";
		}
		echo "</code>";

		die;
	}

}