<?php

namespace Schmutzka;

/**
 * xmlToArray(SimpleXMLElement/string)
 * getFirstSet(...)
 * getEmailServer($email, $url = FALSE)
 * getWhatpulseUserStats($id)
 */

class Utils extends \Nette\Object
{

	/**
	 * Convert xml object/file to array
	 * @param SimpleXMLElement|string
	 * @return array
	 */
	public static function xmlToArray($xml)
	{
		if (is_file($xml)) {
			$xml = simplexml_load_file($xml);
		}

		return json_decode(json_encode((array) $xml), 1);
	}


	/**
	 * Return first set argument
	 * @param ... mixed
	 */
	public static function getFirstSet()
	{
		foreach (func_get_args() as $value) {
			if (!empty($value) && !is_null($value) && $value) {
				return $value;
			}

			return NULL;
		}
	}


	/**
	 * Get known email service from email
	 * @param string
	 * @param bool
	 * @return string
	 */	
	public static function getEmailServer($email, $url = FALSE)
	{
		list($name, $domain) = explode("@", $email);
		$emailList = array("gmail.com", "centrum.cz", "centrum.sk",  "seznam.cz", "zoznam.sk", "post.cz",  "email.cz", "atlas.cz", "atlas.sk", "hotmail.com", "azet.sk", "yahoo.com", "live.com", "mail.com");

		if (in_array($domain, $emailList)) {
			if ($url) {
				return "http://www. " . $domain;
			}

			return $domain;
		}

		return NULL;
	}


	/**
	 * Get whatpulse users stats
	 * @param int
	 * @return array
	 */
	public static function getWhatpulseUserStats($uid) 
	{
		$url = "http://api.whatpulse.org/user.php?UserID=" . $uid;
		$data = file_get_contents($url);

		$data = self::xmlToArray($data);
		$data = $data["WhatPulse"];

		return $data;
	}

}
