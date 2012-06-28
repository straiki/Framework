<?php

namespace Schmutzka\Utils;

class Arrays extends \Nette\Object
{

	/**
	 * Determine, if is one level array or multiple level array 
	 * @param array
	 * @return bool
	 */
	public static function hasMoreLevels($array)
	{
		return is_array($array[key($array)]);
	}
	

	/**
	 * Check if arrays has all keys
	 * @param array
	 * @param array
	 */
	public static function hasAllKeys(array $array, array $requiredKeys)
	{
		if (self::hasMoreLevels($array)) {
			foreach ($array as $value) {
				foreach($value as $key => $value2) {
					if (!in_array($key, $requiredKeys)) {
						return FALSE;
					}
				}
			}
			
		} else {
			foreach ($requiredKeys as $key) {
				if (!array_key_exists($key, $array)) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}


	/**
	 * Switch rows into colums order { 1 2 / 3 4 / 5 } => {1 3 / 2 4 / 5 }
	 * @param array
	 * @param int
	 */
	public static function rowsToColumns($array, $int = 2)	
	{
		$p = ceil(count($array)/2);
		$m = $p - 1;
		$k = 1;

		sort($array);

		$return = array();
		for($i = 0; $i < count($array); $i++) {
			if ($i == 0) {
				$return[0] = $array[0];
			}
			elseif ($i%$int == 1) {
				$k = $k + $p;
				$return[$k] = $array[$i];
			}
			else {
				$k = $k - $m;
				$return[$k] = $array[$i];
			}
		}
 
		return $return;
	}


	/**
	 * Switch rows and cols in two-dimensional array
	 * @param array
	 */
	public static function tr2td($array)
	{
		$return = array();
		foreach ($array as $tr => $td) {
			foreach ($td as $key => $value) {
				$return[$key][$tr] = $value;
			}
		}

		return $return;
	}


	/**
	 * Array key summary
	 * @param array
	 * @param string
	 * @return int
	 */
	public static function keySum($array, $key)
	{
		$sum = 0;
		foreach ($array as $value) {
			if (isset($value[$key])) {
				$sum += $value[$key];
			}
		}

		return $sum;
	}


	/**
	 * Returns min and max value
	 * @param array
	 * @return array
	 */
	public static function minMax($array)
	{
		$min = $max = array_pop($array);
		foreach ($array as $key => $value) {
			$min = min($min, $value);
			$max = max($max, $value);
		}

		return array($min, $max);
	}


	/**
	 * Change type of array to another specific 
	 * @param array
	 * @param string
	 * @param string
	 * @return array
	 */
	public static function retype($array, $type = "int", $format = NULL, $keepNull = TRUE)	
	{
		foreach ($array as $key => $value) {
			if($type == "int") {		
				$array[$key] = (($keepNull AND is_null($value)) ? NULL : (int) $value);
			}
			elseif($type == "float") {
				$array[$key] = (($keepNull AND is_null($value)) ? NULL : (float) $value);
			}
			elseif($type == "date") {
				$array[$key] = date($format, strtotime($value));
			}
		}

		return $array;
	}


	/**
	 * Gets 1 column into array 	
	 * @param array
	 * @param string
 	 * @return array
	 */ 
	public static function extractColumn($array, $column)
	{
		$result = array();
		foreach($array as $key => $value) {
			if (!is_array($value)) {
				$value = iterator_to_array($value);
			}

			if (isset($value[$column]) OR $value[$column] === NULL) {
				$result[] = $value[$column];
			}
		}

		return $result;
	}


	/**
	 * Clear all empty values
	 * @param array
	 * @return array
	 */
	public static function clearEmpty($array)
	{
		$result = array();
		foreach ($array as $key => $value) {
			if ($value OR $value === 0) {
				$result[$key] = trim($value);
			}
		}

		return $result;
	}


	/**	
	 * Order array by subkey, subkey 2 optional 
	 * @param mixed
	 * @return array
	 */
	public static function sortBySubKey()
	{
		$args = func_get_args(); 
		return self::helperSubkeySort($args);
	}


	/**	
	 * Order array by subkey, subkey 2 optional 
	 * @param mixed
	 * @return array
	 */
	public static function sortBySubKeyReverse($array, $subkey)
	{
		$args = func_get_args(); 
		$array = self::helperSubkeySort($args);
		return array_reverse($array);
	}


	/**
	 * @param array
	 * @return array
	 */
	private static function helperSubkeySort($args)
	{
		//get args of the function 
		$c = count($args); 
		if ($c < 2) { 
			return false; 
		} 

		//get the array to sort 
		$array = array_splice($args, 0, 1); 
		$array = $array[0]; 

		//sort with an anoymous function using args 
		usort($array, function($a, $b) use($args) { 
			$i = 0; 
			$c = count($args); 
			$cmp = 0; 
			while($cmp == 0 && $i < $c) { 
				$cmp = strcmp($a[$args[$i]], $b[$args[$i]]); 
				$i++; 
			} 
			return $cmp;
		});

		return $array;
	}


	/**	
	 * Vrátí záznam, kde klíč bude dané hodnoty
	 * @array pole	 
	 * @string zkoumané pole
	 * @string hodnota pole
	 * @return mixed nalezená hodnota / NULL
	 */
	public static function findByKeyValue(array $array, $key, $find, $returnArrayStrict = FALSE)
	{
		$return = array();

		foreach($array as $value) {
			$compare = $value[$key];

			/* array s 1 hodnotou */
			if(is_array($compare)) {
				while(is_array($compare)) {
					$compare = array_shift($compare); 
				}
			}

			if($compare == $find) {
				$return[] = $value;
			}
		}

		if($returnArrayStrict OR count($return) > 1) { // více výsledků -> pole
			return $return;
		}
		elseif(count($return) == 1) {
			return $return[0];
		}
		return NULL; // žádný výsledek
	}


	/**
	 * Mirror of Nette\Utils\Arrays
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		return callback("\Nette\Utils\Arrays", $name)->invokeArgs($args);
	}

}