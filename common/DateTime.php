<?php

namespace Schmutzka;

use Nette;


class DateTime extends Nette\DateTime
{
	/** @var array */
	private static $holidays = array("12-24", "12-25", "12-26", "01-01", "05-01", "05-08", "07-05", "07-06", "09-28", "10-28", "11-17");


	/**
	 * Object factory
	 * @return self
	 */
	public static function get()
	{
		return new static();
	}	



	/**
	 * Is date between limts
	 * @param Nette\DateTime|string
	 * @param Nette\DateTime|string
	 * @return bool
	 */
	public function isBetween($from, $to)
	{	
		$from = self::from($from);
		$to = self::from($to);

		return ($this >= $from && $this <= $to);
	}


	/********************** month/year **********************/


	/**
	 * Get number of days in month
	 * @return int
	 */
	public function daysInMonth()
	{
		$month = $this->format("m");
		$year = $this->format("Y");
		return cal_days_in_month(CAL_GREGORIAN, $month, $year); 
	}


	/**
	 * Get week start and end
	 */
	public function weekStartEnd()
	{
		$year = $this->format("Y");
		$week = $this->format("m") - 1; // - intentionally

		$time = strtotime("1 January $year", time());
		$day = date("w", $time);
		$time += ((7 * $week) + 1 - $day) * 24 * 3600; // @todo check, move to DateTime if possible

		$result = array(
			"start" => new self($time),
			"end" => get($time)->modify("+6 days"),
		);

		return $result;
	}

	/********************** change position **********************/


	/**
	 * Minus another DateTime
	 * @return int
	 */
	public function minus($dateTime)
	{
		$time1 = strtotime($this);
		$time2 = strtotime($dateTime . ":00");

		return $time2 - $time1;
	}


	/**
	 * Add x workdays
	 * @param int
	 */
	public function addWorkday($amount = 1)
	{
		for ($i = 0; $i < $amount; $i++) {
			$this->modify("+1 day");
			while (!$this->isWorkingDay()) {
				$this->modify("+1 day");
			}
		}

		return $this;
	}


	/********************** get position **********************/


	/**
	 * Get nearest day of the week from today
	 * @param int 1-7
	 * @return DateTime
	 */
	public function getNextNearestDay($day)
	{
		$currentDay = $this->format("N");
		$dayShift = ((7 + $day - $currentDay) % 7) ?: 7;

		$this->modify("+$dayShift days");
		return $this;
	}	


	/**
	 * Get nearest h:s time from now
	 * @param string
	 * @return DateTime
	 */
	public function getNextNearestTime($time)
	{
		$currentTime = $this->format("H:i");
		if (strtotime($time) <= strtotime($currentTime)) {
			$this->modify("+1 day");
		} 

		list($hours, $mins) = explode(":", $time);
		$this->setTime($hours, $mins);
		return $this;
	}	


	/**
	 * Get closest working day
	 * @return void
	 */
	public function getNearestWorkday()
	{
		while (!$this->isWorkingDay()) {
			$this->modify("+1 day");
		}

		return $this;
	}	


	/** 
	 * Get distance from now (in days by default)
	 * @param string
	 */
	public function getFromNow($type = "d")
	{
		$today = new self;
		$diff = self::diff($today);
	
		if ($type) { // todofix 
			return $diff->{$type};
		}

		return $diff;
	}


	/**
	 * Get age from birthdate
	 * @return float
	 */
	public function getAge()
	{  
		return floor((date("Ymd") - date("Ymd", $this)) / 10000);
	}


	/********************** localization **********************/


	/**
	 * Localized day in the week
	 * @param string
	 * @param bool
	 * @return string
	 */
	public function dayLocalized($lang = 'cs', $lcfirst = FALSE)
	{
		$nameList = array(
			'cs' => array(1 => 'Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne')
		);
		$month = $this->format('N');

		if (isset($nameList[$lang][$month])) {
			$return = $nameList[$lang][$month];
			return ($lcfirst ? lcfirst($return) : $return);
		}

		return $this->format('D');


// @todo merge!
		$nameList = array(
			"cs" => array(1 => "pondělí", "úterý", "středa", "čtvtek", "pátek", "sobota", "neděle")
		);
		$day = $this->format("N");

		if (isset($nameList[$lang][$day])) {
			$return = $nameList[$lang][$day];
			return ($ucfirst ? ucfirst($return) : $return);
		}

		return $this->format("l");
	}


	/**
	 * Localized month 
	 * @param string
	 * @param bool
	 */
	public function monthLocalized($lang = "cs", $ucfirst = FALSE)
	{
		$nameList = array(
			"cs" => array(1 => "leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec")
		);
		$month = $this->format("n");

		if (isset($nameList[$lang][$month])) {
			$return = $nameList[$lang][$month];
			return ($ucfirst ? ucfirst($return) : $return);
		}

		return $this->format("F");
	}


	/**
	 * Localized day 
	 * @param string
	 * @param bool
	 */
	/*public function dayLocalized($lang = "cs", $ucfirst = FALSE)
	{

	}*/


	/**
	 * Format
	 * @param string
	 */
	public function format($mask = "Y-m-d H:i:s")
	{
		return parent::format($mask);
	}


	/********************** state **********************/


	/**
	 * Is today
	 * @return bool	 
	 */
	public function isToday()
	{
		if ($this->format("Y-m-d") == self::from(NULL)->format("Y-m-d")) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Is working day
	 * @return bool
	 */
	public function isWorkingDay()
	{
		if ($this->format("N") >= 6) {
			return FALSE;
		}

		if ($this->isHoliday()) {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * Is weekend
	 * @return bool
	 */
	public static function isWeekend()
	{
		return ($this->format("N") >= 6);
	}


	/**
	 * Is holiday
	 * @return bool	 
	 */
	public function isHoliday()
	{
		if (in_array($this->format("m-d"), self::$holidays)) {
			return TRUE;
		}

		if ($this->format("m-d") == strftime("%m-%d", easter_date($this->format("Y")))) { // easter
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Is past
	 * @return bool
	 */
	public function isPast()
	{
		if ($this < new self) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Is future
	 * @return bool
	 */
	public function isFuture()
	{
		return !isPast();
	}

}