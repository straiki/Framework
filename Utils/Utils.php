<?php

namespace Schmutzka;

class Utils extends \Nette\Object
{

	/**
	 * Return first set argument
	 * @param ... mixed
	 */
	static public function getFirstSet()
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

}