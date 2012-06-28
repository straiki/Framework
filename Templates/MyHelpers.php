<?php

namespace Schmutzka\Templates;

use Nette\Templating\FileTemplate,
	Nette\Templating\Template,
	Schmutzka\Utils\Time,
	Nette\Utils\Html;

class MyHelpers extends \Nette\Object
{
	/** @var \SystemContainer */
	private $context;

	/** @var \Presenter */
    private $presenter;


    public function __construct($context, $presenter)
    {
		$this->context = $context;
		$this->presenter = $presenter;
	}


	public function loader($helper)
	{
		if (method_exists($this, $helper)) {
			return callback($this, $helper);
		}
	}


	/** 
	 * Translate - hotfix
	 * @param string
	 */
	public static function translate($s)
	{
		return $s;
	}


	/**
	 * highlight_string without '<?php' at the beggining
	 */
	public static function highlight($code) {	
		$code = "<?php ".$code;
		$split = array('&lt;?php&nbsp;' => '');
		return strtr(highlight_string($code,TRUE),$split);
	}


	/**
	 * Timeline helper
	 * @param int
	 * @param int
	 * @return html
	 */
	public function timeLine($time, $steps = 25) 
	{
		$step = floor($time/$steps);
		$width = floor(100/$steps);
	
		$return = "";
		$time = -4;
		for ($i = 0; $i < $steps; $i++) {
			$time += $step;
			$return .= Html::el("td")->setHtml(Time::im($time))->width($width . "%");
			$time += 2.1; // special constant
		}

		return $return;
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
				$temp .= $data[$key]." ";
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
	public function ternal($value, $one = "ano", $two = "ne", $cond = 1) {
		if ($value == $cond) {
			return $one;
		}
		else {
			return $two;
		}
	}


	/**
	 * Return set value
	 * @param mixed
	 * @param string
	 */
	public function isEmpty($value, $emptyReturn = "-", $notEmptyReturn = NULL) {
		if ((!isset($value)) OR (!$value AND !is_numeric($value)) OR is_null($value)) {
			return $emptyReturn;
		}
		else {
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
		}
		else {
			return $return;
		}
	}


	/**
	 * Iconv
	 * @param string
	 * @param string
	 * @return string
	 */
	public function iconv($value, $from = "utf-8", $to = "windows-1250") {
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
	 * @return string
	 */
	public function secureMail($email, $node = NULL, $clickable = TRUE)
	{	
		$return = NULL;
		for($i=0,$j=strlen($email);$i<$j; $i++) {
			$return .= "&#0".ord($email[$i]).";";
		}

		if ($clickable) {
			$node = $node ? $node : $return;
			return "<a href='mailto:$return'>$node</a>";
		}
		else {
			return $return;
		}
	}


	/**
	 * If empty value, returns zero
	 * @param mixed $value
	 */
	public static function zero($value, $zero = "0")
	{
		return (empty($value) ? $zero : $value);
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
	 * Prepares string for use in TITLE element
	 * @param string $s
	 * @return string
	 */
	public static function title($s)
	{
		return String::replace(html_entity_decode(strip_tags($s), ENT_COMPAT, 'UTF-8'), '#\\s+#u', ' ');
	}


	/** [sleeper.cz]
	 * Vrátí fotku daného uživatele nebo prázdnou
	 * @string jméno profilovky
	 * @string název třídy
	 * @return html
	 */
	public function showProfilePhoto($profilePhoto, $class = "profilePhoto")
	{
		$basePath = $this->context->httpRequest->url->scriptPath;
		$profilePhoto = (isset($profilePhoto[5]) ? $profilePhoto : "no_profile.jpg");

		return '<img src="'.$basePath.'/images/profile_photo/'.$profilePhoto.'" alt="" class="'.$class.'">';
	}

}