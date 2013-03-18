<?php

namespace Schmutzka\Utils;

use Nette;
use Nette\Utils\Strings;

class Password extends Nette\Object
{

	/**
	 * Hash password with salt
	 * @param string
	 * @param string
	 * @param bool
	 * @param int
	 */
	public static function saltHash($password, $salt, $forceSalt = FALSE, $iterations = 10)
	{
		if (!$salt && !$forceSalt) {
			return sha1($password);
		}

		$raw = $password . $salt;
		for ($i = 1; $i <= $iterations; $i++) {
			$raw .= $raw;
		}

		$hashed = sha1($raw);
		return $hashed;
	}


	/**
	 * Encrypts arguments, able to decrypt
	 * @param ... string
	 * @return string
	 */
	public static function blend()
	{
		$args = func_get_args();

		$return = "";
		foreach ($args as $arg) {
			$return .= sha1(sha1(sha1($arg)));
		}

		return sha1($return);
	}


	/**
	 * Encrypts arguments, one use only
	 * @param ... string
	 * @return string
	 */
	public static function blendOnce()
	{
		$args = func_get_args();

		$return = "";
		foreach ($args as $arg) {
			$return .= sha1(sha1(sha1($arg . time() . Strings::random(rand(5,15)))));
		}

		return sha1($return);
	}

}