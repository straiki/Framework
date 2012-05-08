<?php

namespace Schmutzka\Utils;

class Time extends \Nette\Object
{

	/**
	 * Return difference between 2 timestamp in sec
	 * @param datetime
	 * @param datetime
	 * @return int
	 */
	public static function timestampDiff($timestamp1, $timestamp2)
	{
		return (int) abs(strtotime($timestamp1) - strtotime($timestamp2));
	}


	/**
	 * Get age from birthdate
	 * @param date format/time()
	 */
	public static function age($birthDate)
	{  
		if (!is_int($birthDate)) {
			$birthDate = strtotime($birthDate);
		}

		return floor((date("Ymd") - date("Ymd", $birthDate)) / 10000);
	}


	/**
	 * Get number of days in month
	 * @param date
	 */
	public static function daysInMonth($date)
	{
		$month = date("m", strtotime($date));
		$year = date("Y", strtotime($date));

		return cal_days_in_month(CAL_GREGORIAN, $month, $year); 
	}

}