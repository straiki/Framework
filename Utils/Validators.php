<?php

namespace Schmutzka\Utils;

use Nette;
use DateTime;

/**
 * isTime($time)
 * isDate($date)
 */

class Validators extends Nette\Utils\Validators
{

	/**
	 * Is date time
	 * @param string
	 * @return bool
	 */
	public static function isDateTime($value)
	{
		return preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value);
	}


	/**
	 * Check time format
	 * @return bool
	 */
	public static function isTime($time)
	{
		return (preg_match('/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/', $time) || preg_match('/^([0-9]):([0-5][0-9])$/', $time));
	}


	/**
	 * Check date form
	 * @param string
	 * @return bool
	 */
	public static function isDate($date)
	{
		if (is_object($date) && $date instanceof DateTime) {
			$date = $date->format('Y-m-d');
		}

		if (strpos('-', $date)) { // A. world format
			$dateArray = explode('-', $date, 3);
			list($y, $m, $d) = $dateArray;

		} elseif (strpos('.', $date)) { // B. czech format
			$dateArray = explode('.', $date, 3);
			list($d, $m, $y) = $dateArray;
		}

		return (checkdate((int) $m,(int) $d,(int) $y) && strtotime('$y-$m-$d') && preg_match('#\b\d{2}[/-]\d{2}[/-]\d{4}\b#', '$d-$m-$y'));
	}

}