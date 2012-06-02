<?php

namespace Schmutzka\Utils;

use Schmutzka\Utils\Validators;

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

			if($type == 1) {
				return (int) $h;
			}
			elseif ($type == 2) {
				return (60 * $h + $m);
			}
			elseif ($type == 3) {
				return ($h . ":00");
			}
			elseif ($type == 4) {
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
			if($key < 6 OR $key >= 18) {
				$midnighter += count($value);
			}
			else {
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
		}
		else {
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

}