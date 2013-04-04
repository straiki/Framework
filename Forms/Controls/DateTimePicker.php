<?php

/**
 * Addons and code snippets for Nette Framework. (unofficial)
 *
 * @author   Jan Skrasek
 * @license  MIT
 */


namespace Schmutzka\Forms\Controls;


use Schmutzka\Forms\Controls\DatePicker;

use Nette\Utils\Strings;
use Exception;
use Schmutzka;



/**
 * Form control for selecting date.
 *
 * Compatible with jQuery UI DatePicker and HTML 5
 * Require Jan Tvrdik's Nette DatePicker
 * https://github.com/JanTvrdik/NetteExtras/blob/master/NetteExtras/Components/DatePicker/DatePicker.php
 *
 * @author   Jan Skrasek
 */
class DateTimePicker extends DatePicker
{
	const W3C_DATETIME_FORMAT = 'Y-m-d H:i:s';
	const CZECH_FORMAT = 'j. n. Y H:i';



	/**
	 * Class constructor.
	 *
	 * @author   Jan Skrasek
	 * @param    string            label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'datetime-local';
		$this->setClassName('datetime');
	}



	/**
	 * Generates control's HTML element.
	 *
	 * @author   Jan Skrasek
	 * @return   \Nette\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();

		list($min, $max) = $this->extractRangeRule($this->getRules());
		if ($min !== NULL) $control->min = $min->format(self::W3C_DATETIME_FORMAT);
		if ($max !== NULL) $control->max = $max->format(self::W3C_DATETIME_FORMAT);

		if ($this->value) $control->value = $this->value->format(self::CZECH_FORMAT);

		return $control;
	}



	/**
	 * Sets DatePicker value.
	 *
	 * @author   Jan Skrasek
	 * @param    \DateTime|int|string
	 * @return   self
	 */
	public function setValue($value)
	{
		if (!is_string($value)) {
			return parent::setValue($value);
		}

		$matches = Strings::match($value, '#^(?P<dd>\d{1,2})[. -] *(?P<mm>\d{1,2})(?:[. -] *(?P<yyyy>\d{4})?)?(?: *[ \-@] *(?P<hh>\d{1,2}):(?P<ii>\d{1,2}))?$#');
		if ($matches) {
			$dd = $matches['dd'];
			$mm = $matches['mm'];
			$yyyy = isset($matches['yyyy']) ? $matches['yyyy'] : date('Y');

			$hh = isset($matches['hh']) ? $matches['hh'] : 0;
			$ii = isset($matches['ii']) ? $matches['ii'] : 0;

			if (!($hh > -1 && $hh < 24 && $ii > -1 && $ii < 60))
				$hh = $ii = 0;

			if (checkdate($mm, $dd, $yyyy)) {
				$value = date(self::W3C_DATETIME_FORMAT, mktime($hh, $ii, 0, $mm, $dd, $yyyy));
			} else {
				$value = NULL;
			}

		} elseif ($value) {
			$dateTime = Schmutzka\DateTime::from($value);
			parent::setValue($dateTime);

		} else {
			$value = NULL;
		}

		if ($value !== NULL) {
			try {
				$value = Schmutzka\DateTime::from($value); // clone DateTime when given
			} catch (Exception $e) {
				$value = NULL;
			}
		}

		if ($value instanceof \DateTime) {
			$this->rawValue = $value->format(self::W3C_DATETIME_FORMAT);
		}

		$this->value = $value;
		return $this;
	}

}
