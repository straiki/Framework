<?php

namespace Schmutzka\Forms;

use Nette;
use Nette\Forms\IControl;
use Nette\Utils\Strings;


class Rules extends Nette\Object
{

	/**
	 * @param IControl
	 * @return  bool
	 */
	public static function validateTime(IControl $control)
	{
		return Strings::match($control->value, '~^([0-9]|0[0-9]|1[0-9]|2[0-3])(:)(0[0-9]|[1-5][0-9])$~');
	}


	/**
	 * @param IControl
	 * @return  bool
	 */
	public static function validateDate(IControl $control)
	{
		$date = $control->value;
		if (! $date) {
			return FALSE;
		}

		$date = $control->value->format('Y-m-d');

		$dateArray = explode('-', $date);
		if (count($dateArray) == 3) {
			list($y, $m, $d) = $dateArray;

			if (checkdate($m, $d, $y) && strtotime('$y-$m-$d') && preg_match('#\b\d{2}[/-]\d{2}[/-]\d{4}\b#', '$d-$m-$y')) {
				return TRUE;
			}
		}

		return FALSE;
	}


	/**
	 * Check allowed extensions
	 * @param IControl
	 * @param array
	 * @return bool
	 * @source http://forum.nette.org/cs/9855-nefunkcni-validace-uploadovaneho-souboru
	 */
	public function validateFileExtension(IControl $control, $allowedExtensions = array())
	{
		$file = $control->getValue();

		if ($file instanceof Nette\Http\FileUpload) {
			$ext = strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION));
			return in_array($ext, $allowedExtensions);
		}

		return false;
	}

}
