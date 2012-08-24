<?php

namespace Schmutzka\Utils;

use Schmutzka\Utils\Validators;

class Time extends \Nette\Object
{

	/**
	 * Find week borders
	 * @param date
	 * @param int
	 * @param string
	 */
	public static function weekStartEnd($year, $week, $type = NULL)
	{
		$year = date("Y", strtotime($year));
		--$week; // why? :/

		$time = strtotime("1 January $year", time());
		$day = date("w", $time);
		$time += ((7*$week)+1-$day)*24*3600;
		$return[0] = date("Y-m-d", $time);
		$time += 6*24*3600;
		$return[1]= date("Y-m-d", $time);

		if ($type == "start") {
			return $return[0];

		} elseif ($type == "end") {
			return $return[1];
		}

		return $return;
	}


	/**
	 * Return difference between 2 timestamp in sec
	 * @param datetime
	 * @param datetime
	 * @param string
	 * @return int
	 */
	public static function timestampDiff($timestamp1, $timestamp2 = NULL, $format = "d", $floor = TRUE)
	{
		$seconds = (int) abs(strtotime($timestamp1) - strtotime($timestamp2));

		switch($format) {
			case "d":
				$return = $seconds / (60 * 60 * 24);
				break;
			default:
				$return = $seconds;
		}

		if ($floor) {
			return (int) floor($return);
		}

		return (int) $return;
	}


	/**
	 * Return birthdate from rc (rodné číslo)
	 * @param string
	 * @param bool
	 * @return date
	 */
	public static function birthdateFromRC($rc, $detectGender = FALSE)
	{
		$female = FALSE;

		$rc = strtr($rc, array("/" => NULL));
		$y = substr($rc, 0, 2);
		$m = substr($rc, 2, 2);
		$d = substr($rc, 4, 2);
		
		if ($m >= 50) { // female
			$female = TRUE;
			$m -= 50;
		}

		if ($y < date("y")) { // 20xx
			$y = "20" . $y;

		} else {
			$y = "19" . $y;			
		}

		$date = date("Y-m-d", strtotime("$y-$m-$d"));

		if ($detectGender) {
			return array(
				"date" => $date,
				"gender" => ($female ? "female" : "male")
			);
		}

		return $date;
	}


	/**
	 * Parse date in misc format and return it in YYYY-MM-DD
	 */
	static function parse($date)
	{
		if (empty($date)) {
			return null;
		}

		if (preg_match('/^([12][0-9]{3})([0-9]{2})([0-9]{2})$/', "$date", $m)) {
			$date = date('Y-m-d', mktime(0, 0, 0, $m[2], $m[3], $m[1]));

		} elseif (preg_match('/^([12][0-9]{3})-([0-9]{1,2})-([0-9]{1,2})(?: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}))?$/', "$date", $m) OR
			preg_match('/^([0-9]{1,2}).\s*([0-9]{1,2}).\s*([12][0-9]{3})(?: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}))?$/', "$date", $m)) {
			$date = date(@$m[4] ? 'Y-m-d H:i:s' : 'Y-m-d', mktime(@$m[4], @$m[5], @$m[6], $m[2], $m[1], $m[3]));

		} elseif (preg_match('/^([0-9]{1,2})\\.\s*([0-9]{1,2})\\.$/', "$date", $m)) { // dd.mm.
			$date = date('Y-m-d', mktime(0, 0, 0, $m[2], $m[1], date('Y')));

		} elseif (preg_match('/^([0-9]{1,2})\\.\s*([0-9]{1,2})\\.([0-9]{1,2})$/', "$date", $m)) {
			$date = date('Y-m-d', mktime(0, 0, 0, $m[2], $m[1], $m[3] < 70 ? "20{$m[3]}" : "19{$m[3]}"));
		}

		if ($time = @strtotime($date)) {
			$date = date('Y-m-d', $time);
			if ((int) date('His', $time)) {
				$date .= " " . ($time = date('H:i:s', $time));
			}
		}

		return $date;
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


	/**
	 * Divide time to hours and minutes
	 * @param string 
	 * @param int
			1 = h (6)
			2 = m (360)
			3 = hh:00 (22:00)
			4 = hh:mm (390 => 6:30)
	 * @return mixed
	 */
	public static function ex($time, $type = 1)
	{
		if (Validators::isTime($time)) {
			list($h, $m) = explode(":", $time);

			if ($type == 1) {
				return (int) $h;

			} elseif ($type == 2) {
				return (60 * $h + $m);

			} elseif ($type == 3) {
				return ($h . ":00");

			} elseif ($type == 4) {
				return sprintf("%02d:%02d", floor($time/60), $time%60);
			}
		}

		return FALSE;
	}




	/********************* 2DO *********************/


	/** 
	 * @unfinished
	 * Average sleep time (midnights/noon oscialation = biday oscilation)
	 * @param array
	 * @return string
	 */
	public static function getAverage(array $data) 
	{
		if (!count($data) OR !is_array($data)) { // žádná/chybná data
			return NULL;
		}

		// 1. určení cyklocentrické části A. půlnoc, B. poledne
		$hourDistribution = array();
		foreach ($data as $value) {
			if (Validators::isTime($value)) {
				$hourDistribution[self::ex($value)][] = $value;
			}
		}

		// sečteme pro jednolivé části - možná různé pro usnínání a vstávání?
		$midnighter = $nooner = 0;


		foreach ($hourDistribution as $key => $value) {
			if ($key < 6 OR $key >= 18) {
				$midnighter += count($value);

			} else {
				$nooner += count($value);
			}
		}

		// určíme typ
		$type = ($midnighter >= $nooner) ? "midnight" : "noon";

		// spočteme průměr
		$timeSum = 0;
		if ($type == "noon") {
			foreach($data as $value) {
				$timeSum += self::ex($value, 2);
			}

		} else {
			foreach($data as $value) {
				$mins = self::ex($value, 2);
				if($mins < (12*60)) { // 12 je klíčové číslo!!!!!!!!!!!
					$timeSum += 24*60; // posuneme o den
				}
				$timeSum += self::ex($value, 2);
			}
		}

		$timeMean = $timeSum/count($data);
		while($timeMean > 1440) {
			$timeMean -= 1440;
		}

		return self::im($timeMean); 
	}


	/**
	 * Converts time to seconds
	 * @param mixed

	 * @param string
	 */
	public static function inSeconds($time, $inputFormat = NULL)
	{
		$h = $m = $s = 0;
		$list = explode(":", $time);

		if (count($list) == 3) {
			list ($h, $m, $s) = $list;

		} elseif (count($list) == 2) {
			if ($inputFormat == "H:i") {
				list ($h, $m) = $list;

			} elseif ($inputFormat == "i:s") {
				list ($m, $s) = $list;
			}
		}

		$secodns = $h * 60 * 60 + $m * 60 + $s;
		return $secodns;


	}


	/**
	 * Convert time







	 * @param int
	 * @param int
	 */
	public static function im($time, $type = 1)
	{
		$h = floor(($time)/60);
		$m = $time - ($h * 60);

		if ($type == 1) {
			return $h . ":" . $m;

		} elseif ($type == 2) { 
			if ($h) {
				return $h . ":" . ($m < 10 ?  ("0" . $m) : $m) . " hod.";			

			} else {
				return $m . " min.";			
			}
		}	
	}
}