<?php

namespace Schmutzka\Templates;

use Schmutzka\Utils\Time;

class MyHelpers extends \Nette\Object
{
	/** @var \SystemContainer */
	private $context;


	public function __construct($context)
	{
		$this->context = $context;
	}


	public function loader($helper)
	{
		if (method_exists($this, $helper)) {
			return callback($this, $helper);
		}
	}


	/**
	 * Get suffix
	 * @param string
	 */
	public static function suffix($file)
	{
		$temp = explode(".", $file);
		return array_pop($temp);
	}


	/**
	 * Minutes to readable time
	 * @param int
	 * @param int
	 */
	public static function minsToTime($mins, $type = 1)
	{
		return Time::im($mins, $type);
	}


	/**
	 * Time to seconds
	 * @param mixed
	 * @param string
	 */
	public static function inSeconds($time, $inputFormat = NULL)
	{
		return Time::inSeconds($time, $inputFormat);
	}


	/**
	 * First upper
	 * @param string
	 */
	public static function fupper($string)
	{
		return ucfirst($string);
	}


	/**
	 * highlight_string without '<?php' at the beggining
	 */
	public static function highlight($code)
	{	
		$code = "<?php ".$code;
		$split = array('&lt;?php&nbsp;' => '');
		return strtr(highlight_string($code,TRUE),$split);
	}


	/**
	 * Combine few fileds to 1 line
	 * param ... array
	 * @dev version
	 */
	public function combineFields()
	{
		$args = func_get_args();
		$data = array_shift($args);

		$temp = "";
		foreach($args[0] as $key) {
			if (isset($data[$key])) {
				$temp .= $data[$key] . " ";
			}
		}

		return trim($temp); 
	}


	/**
	 * Ternal shortcut
	 * @param mixed
	 * @param string
	 * @param string
	 * @param mixed
	 */
	public static function ternal($value, $one = "ano", $two = "ne", $cond = 1)
	{
		if ($value == $cond) {
			return $one;

		} else {
			return $two;
		}
	}


	/**
	 * Return set value
	 * @param mixed
	 * @param string
	 */
	public function isEmpty($value, $emptyReturn = "-", $notEmptyReturn = NULL)
	{
		if ((!isset($value)) OR (!$value AND !is_numeric($value)) OR is_null($value)) {
			return $emptyReturn;

		} else {
			return trim($value." ".$notEmptyReturn);
		}
	}


	/**
	  * Returns field from array
	  * @param mixed
	  * @param string
	  */
	public function inArray($value, $array, $return = "-")
	{
		if (isset($array[$value])) {
			return $array[$value];

		} else {
			return $return;
		}
	}


	/**
	 * Iconv
	 * @param string
	 * @param string
	 * @return string
	 */
	public function iconv($value, $from = "utf-8", $to = "windows-1250")
	{
        return iconv($from, $to, $value);
	}


	/** 
	 * Round function
	 * @param float
	 * @param int
	 * @return int
	 */
	public static function round($n, $precision)
	{
		return round($n, $precision);
	}


	/**
	 * Converts date to words in Czech.
	 * @param mixed $date
	 * @return string
	 */
	public static function dateAgoInWords($date)
	{
		if (!$date) {
			return FALSE;

		} elseif (is_numeric($date)) {
			$date = (int) $date;

		} elseif ($date instanceof DateTime) {
			$date = $date->format("U");

		} else {
			$date = strtotime($date);
		}

		$now = time();
		$delta = self::diffInDays($date, $now);

		if ($delta < 0) {
			$delta = abs($delta);
			if ($delta == 0) return "ještě dnes";
			if ($delta == 1) return "zítra";
			if ($delta < 30) return "za " . $delta . " " . self::plural($delta, "den", "dny", "dní");
			if ($delta < 60) return "za měsíc";
			if ($delta < 365) return "za " . round($delta / 30) . " " . self::plural(round($delta / 30), "měsíc", "měsíce", "měsíců");
			if ($delta < 730) return "za rok";
			return "za " . round($delta / 365) . " " . self::plural(round($delta / 365), "rok", "roky", "let");
		}
		
		if ($delta == 0) return "dnes";
		if ($delta == 1) return "včera";
		if ($delta < 30) return "před " . $delta . " dny";
		if ($delta < 60) return "před měsícem";
		if ($delta < 365) return "před " . round($delta / 30) . " měsíci";
		if ($delta < 730) return "před rokem";
		return "před " . round($delta / 365) . " lety";
	}
	
	
	/**
	 * Converts time to words in Czech
	 * @see http://addons.nette.org/cs/helper-time-ago-in-words.
	 * @param mixed $time
	 * @return string
	 */
	public static function timeAgoInWords($time)
	{
		if (!$time) {
			return FALSE;

		} elseif (is_numeric($time)) {
			$time = (int) $time;

		} elseif ($time instanceof DateTime) {
			$time = $time->format("U");

		} else {
			$time = strtotime($time);
		}

		$delta = time() - $time;

		if ($delta < 0) {
			$delta = round(abs($delta) / 60);
			if ($delta == 0) return "za okamžik";
			if ($delta == 1) return "za minutu";
			if ($delta < 45) return "za " . $delta . " " . self::plural($delta, "minuta", "minuty", "minut");
			if ($delta < 90) return "za hodinu";
			if ($delta < 1440) return "za " . round($delta / 60) . " " . self::plural(round($delta / 60), "hodina", "hodiny", "hodin");
			if ($delta < 2880) return "zítra";
			if ($delta < 43200) return "za " . round($delta / 1440) . " " . self::plural(round($delta / 1440), "den", "dny", "dní");
			if ($delta < 86400) return "za měsíc";
			if ($delta < 525960) return "za " . round($delta / 43200) . " " . self::plural(round($delta / 43200), "měsíc", "měsíce", "měsíců");
			if ($delta < 1051920) return "za rok";
			return "za " . round($delta / 525960) . " " . self::plural(round($delta / 525960), "rok", "roky", "let");
		}

		$delta = round($delta / 60);
		if ($delta == 0) return "před okamžikem";
		if ($delta == 1) return "před minutou";
		if ($delta < 45) return "před $delta minutami";
		if ($delta < 90) return "před hodinou";
		if ($delta < 1440) return "před " . round($delta / 60) . " hodinami";
		if ($delta < 2880) return "včera";
		if ($delta < 43200) return "před " . round($delta / 1440) . " dny";
		if ($delta < 86400) return "před měsícem";
		if ($delta < 525960) return "před " . round($delta / 43200) . " měsíci";
		if ($delta < 1051920) return "před rokem";
		return "před " . round($delta / 525960) . " lety";
	}


	/**
	 * Plural
	 * @param  int
	 * @return mixed
	 */
	public static function plural($n)
	{
		$args = func_get_args();
		return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
	}


	/**
	 * Formats duration in seconds to number with units
	 * @param int $s
	 */
	public static function duration($s)
	{
		if ($s < 60) return "$s\xC2\xA0s";
		if ($s < 3600) return ((int)($s / 60)) . "\xC2\xA0min";
		return ((int)($s / 3600)) . "\xC2\xA0h";
	}


	/**
	 * Protect mail against spam
	 * @param string
	 * @param string
	 * @param bool
	 * @param string
	 * @return string
	 */
	public function secureMail($email, $node = NULL, $clickable = TRUE, $class = NULL)
	{	
		$return = NULL;
		for($i=0,$j=strlen($email);$i<$j; $i++) {
			$return .= "&#0".ord($email[$i]).";";
		}

		if ($clickable) {
			$node = $node ? $node : $return;
			return "<a " . ($class ? "class='" . $class . "' " : NULL)."href='mailto:$return'>$node</a>";

		} else {
			return $return;
		}
	}
 

	/********************** date structures & localization **********************/


	/**
	 * Localized day
	 * @param mixed
	 * @param string
	 * @param string
	 * @return string
	 */
	public static function dayName($date, $lang = "cs", $type = "short")
	{
		$day = (is_int($date) ? $date : date("N", strtotime($date)));

		if ($type == "short") {
			if ($lang == "en") {
				if ($type == "short") {
					return date("D", strtotime($date));

				} else {
					return date("l", strtotime($date));
				}
			}
		}

		static $dayNames = array(
			"cs" => array(
				"short" => array(1 => "po", "út", "st", "čt", "pá", "so", "ne"),
				"long" => array(1 => "pondělí", "úterý", "středa", "čtvrtek", "pátek", "sobota", "neděle")
			)
		);

		return $dayNames[$lang][$type][$day];
	}


	/**
	 * Localized month
	 * @param mixed
	 * @param string
	 * @return string
	 */
	public static function monthLoc($date, $lang)
	{
		$month = (is_int($date) ? $date : date("n", strtotime($date)));
	
		if ($lang == "en") {
			return date("F", strtotime($month));
		}

		static $monthNames = array(
			"cs" => array(1 => "leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec") 
		);

		return $monthNames[$lang][$month];
	}


	/**
	 * Date default values
	 * @param string
	 * @param string
	 */
	public static function dateCs($date, $format = "j. n. Y H:i")
	{
		return Helpers::date($date, $format);
	}


	/**
	 * Date default values
	 * @param string
	 * @param string
	 */
	public static function dateEn($date, $format = "Y-m-d H:i")
	{
		return Helpers::date($date, $format);
	}


	/**
	 * Returns month name
	 * @param int $month
	 */
	public static function month($month, $monthList = array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec"))
	{
		return $monthList[$month - 1];
	}


	/**
	 * Returns Czech weekday
	 * @param int $weekday
	 */
	public static function weekday($weekday, $weekdayList = array("pondělí", "úterý", "středa", "čtvrtek", "pátek", "sobota", "neděle"))
	{
		return $week[$weekday - 1];
	}


	/** 
	 * Joins two datetimes as term (from - to)
	 * @param string/DibiDateTime
	 * @param string/DibiDateTime
	 */
	public static function term($from, $to)
	{
		$from = new \Nette\DateTime($from);
		$to = new \Nette\DateTime($to);

		if ($from->format('Y-m-d H:i') == $to->format('Y-m-d H:i')) {
			return $from;
		}
		
		$dayFrom = $from->format('j. n. Y');
		$dayTo = $to->format('j. n. Y');
		$timeFrom = $from->format('H:i');
		$timeTo = $to->format('H:i');

		if ($from->format('Y') == $to->format('Y')) { // same year

			if ($dayFrom == $dayTo) { // same day
				$term = $dayFrom . ' ' . $timeFrom . ' - ' . $timeTo;
				
			} else { // different day
				$term = $from->format('j. n.') . '-' . $to->format('j. n. Y') . ' ' . $timeFrom . '-' . $timeTo;
			}
				
		} else { // different year
			$term = $dayFrom . '  ' . $timeFrom . '-' . $dayTo . ' ' . $timeTo;
		}

		return $term;
	}


	/**
	 * Translate 
	 * @hotfix
	 */
	public function translate($s)
	{
		return $s;
	}

}