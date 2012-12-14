<?php

namespace Schmutzka\Forms;

use Nette\Forms\IControl,
	Nette\Utils\Strings;

/**
 * validateTime
 * validateDate (js missing)
 * validateZip
 * validatePhone
 * validateRC
 * validateIC
 */

class Rules extends \Nette\Object
{

	/**
	 * Validate time
	 *	@param IControl
	 */
	public static function validateTime(IControl $control)
	{
		return Strings::match($control->value, "~^([0-9]|0[0-9]|1[0-9]|2[0-3])(:)(0[0-9]|[1-5][0-9])$~");
	}


	/**
	 * Validate date
	 *	@param IControl
	 */
	public static function validateDate(IControl $control)
	{
		$date = $control->value;

		$dateArray = explode("-", $date); //explode the date into date,month and year 
		if (count($dateArray) == 3) {
			list($y, $m, $d) = $dateArray; 

			if (checkdate($m, $d, $y) && strtotime("$y-$m-$d") && preg_match('#\b\d{2}[/-]\d{2}[/-]\d{4}\b#', "$d-$m-$y")) { 
				return TRUE; 
			}  
		}

		return FALSE;
	}


	/**
	 * Validate zip
	 *	@param IControl
	 */
	public static function validateZip(IControl $control)
	{
		return Strings::match($control->value, "~^(\d{3}) ?\d{2}$~");
	}


	/**
	 * Validate phone
	 *	@param IControl
	 */
	public static function validatePhone(IControl $control)
	{
		$phone = $control->value;
		$pattern = '~^(\+\d{2,3})? ?\d{3} ?\d{3} ?\d{3}$~';

		if (preg_match($pattern, $phone)) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Validate RC
	 * @param IControl
	 * http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
	 */
	public static function validateRC(IControl $control)
	{
		$rc = $control->value;

		// "be liberal in what you receive"
		if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
			return FALSE;
		}

		list(, $year, $month, $day, $ext, $c) = $matches;

		// till 1954 numbers of 9 digits cannot be validated
		if ($c === '') {
			return $year < 54;
		}

		// check number
		$mod = ($year . $month . $day . $ext) % 11;
		if ($mod === 10) $mod = 0;
		if ($mod !== (int) $c) {
			return FALSE;
		}

		// check date
		$year += $year < 54 ? 2000 : 1900;

		// 20, 50 or 70 can be added to month
		if ($month > 70 && $year > 2003) $month -= 70;
		elseif ($month > 50) $month -= 50;
		elseif ($month > 20 && $year > 2003) $month -= 20;

		if (!checkdate($month, $day, $year)) {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * Validate IC
	 * @param IControl
	 * http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
	 */
	public static function validateIC(IControl $control)
	{
		$ic = $control->value;

		// "be liberal in what you receive"
		$ic = preg_replace('#\s+#', '', $ic);

		// has correct form?
		if (!preg_match('#^\d{8}$#', $ic)) {
			return FALSE;
		}

		// checksum
		$a = 0;
		for ($i = 0; $i < 7; $i++) {
			$a += $ic[$i] * (8 - $i);
		}

		$a = $a % 11;

		if ($a === 0) $c = 1;
		elseif ($a === 10) $c = 1;
		elseif ($a === 1) $c = 0;
		else $c = 11 - $a;

		return (int) $ic[7] === $c;
	}

}