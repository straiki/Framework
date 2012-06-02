<?php

namespace Schmutzka\Utils;

use Nette\Utils\Strings;

class Password extends \Nette\Object
{

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