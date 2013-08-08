<?php

namespace Schmutzka\Utils;

use Nette;
use Nette\Utils\Strings;
use Schmutzka\Utils\Validators;


class Time extends Nette\Object
{

	/**
	 * Conver one number to another
	 * @param int
	 * @param string { [ d, h, m, s ] }
	 * @param string { [ d, h, m, s ] }
	 * @return  int|string
	 */
	public static function convert($time, $from, $to)
	{
		$result = NULL;
		if ($from == 's') {
			switch ($to) {
				case 'm' :
					$time /= 30;
				case 'd' :
					$time /= 24;
				case 'h' :
					$time /= 60;
				case 'm' :
					$time /= 60;
					break;
			}
			return $time;

		} elseif ($from == 'h:m') {
			list ($h, $m) = explode(':', $time);
			switch ($to) {
				case 's' :
					return $h * 60 * 60 + $m * 60;

				case 'h' :
					return $h + $m/60;

				case 'm' :
					return 60 * $h + $m;

				case 'h:0':
					return $h . ':00';
			}

		} elseif ($from == 'm:s') {
			list ($m, $s) = explode(':', $time);
			switch ($to) {
				case 's' :
					return $m * 60 + $s;
			}

		} elseif ($from == 'm') {
			$h = floor(($time)/60);
			$m = $time - ($h * 60);
			switch ($to) {
				case 'h:m' :
					return $h . ':' . $m;

				case 'h:mm' :
					return $h . ':' . Strings::padLeft($m, 2, 0);


				case 'h:m hod/min' :
					if ($h) {
						return $h . ':' . Strings::padLeft($m, 2, 0) . ' hod.';

					} else {
						return $m . ' min.';
					}
			}

		} elseif ($from == 'h:m:s') {
			list ($h, $m, $s) = explode(':', $time);
			switch ($to) {
				case 's' :
					return $h * 60 * 60 + $m * 60 + $s;
					break;
			}

		}

		throw new \Exception('Not defined yet');
	}


	/**
	 * Sum hh:mm + hh:mm
	 * @param string
	 * @param string
	 */
	public static function sum2Times24($time1,$time2)
	{
		$time1 = strtotime($time1 . ':00');
		$time2 = strtotime($time2 . ':00');

		$minute = date('i',$time2);
		$hour = date('H',$time2);

		$convert = strtotime('+$minute minutes', $time1);
		$convert = strtotime('+$hour hours', $convert);

		return date('H:i', $convert);
	}


	/********************* 2DO *********************/


	/**
	 * Count average values for start - end intervals
	 * @refactor
	 */
	/* vrátí průměr spánku xx:xx-yy:yy za dané období */
	public static function startEndMean($result,$dateStart = NULL,$dateEnd = NULL, $id = NULL)
	{

		// kurnik ale !!!!

		$increase = FALSE;
		if (count($result)) { // máme výsledky
			$start = $end = NULL;
			foreach ($result as $row) {


				// spánek začíná po 18 hodině a zároveň končí před 18 hodinou = po půlnoci - odečteme 24 hodin pro zachování točení výsledku kolem půlnoci (ještě třeba empirikovat)
				/*
				if(self::minutesFromTimeStamp($row['start']) > 1*60 AND self::minutesFromTimeStamp($row['end']) < 0 * 60) { // nikdy nebude víc jak 24 * 60, že :)
					$start -= 24*60;
				}
				*/



				/* méně jak 12:00, přidáme 24 * 60 minut */
				$startInMins = self::minutesFromTimeStamp($row['start']);

				$break = 18;

				if($startInMins < $break *60) {
					$startInMins += self::$dayMins;
					$increase = TRUE; // @test
				}

				$endInMins = self::minutesFromTimeStamp($row['end']);
				if($endInMins < $break *60 OR $increase) {
					$endInMins += self::$dayMins;
					$increase = TRUE; // @test
				}

				$start += $startInMins;
				$end += $endInMins+1; // hh:m9 fix
			}

			if($start<0) {$start *= -1;}

			$startMean = $start/$result->count('*');
			while($startMean > self::$dayMins) { // vyrovnání překročení 24 hodin
				$startMean -= self::$dayMins;
			}

			$endMean = $end/$result->count('*');
			while($endMean > self::$dayMins) { // vyrovnání překročení 24 hodin
				$endMean -= self::$dayMins;
			}


			$startMean = self::time_form(60 * $startMean,3);
			$endMean= self::time_form(60 * $endMean,3);

			return '$startMean-$endMean';
		}
		return NULL;
	}


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
		$type = ($midnighter >= $nooner) ? 'midnight' : 'noon';

		// spočteme průměr
		$timeSum = 0;
		if ($type == 'noon') {
			foreach ($data as $value) {
				$timeSum += self::ex($value, 2);
			}

		} else {
			foreach ($data as $value) {
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
