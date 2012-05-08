<?php

namespace Schmutzka\Utils;

final class Validators extends \Nette\Utils\Validators
{

	/**
	 * Check time format 
	 * @return bool
	 */ 
	public static function isTime($time)
	{  
		if (preg_match("/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/", $time) OR preg_match("/^([0-9]):([0-5][0-9])$/", $time)) {
			return TRUE; 
		} 

		return FALSE; 
	}


	/**
	 * Check date form
	 * @return bool
	 */
	function isDate($date)
	{  
		$dateArray = explode("-", $date); //explode the date into date,month and year 
		if (count($dateArray) == 3) {
			list($y, $m, $d) = $dateArray; 

			if (checkdate($m, $d, $y) && strtotime("$y-$m-$d") && preg_match('#\b\d{2}[/-]\d{2}[/-]\d{4}\b#', "$d-$m-$y")) { 
				return TRUE; 
			}  
		}

		return FALSE;
	}

}