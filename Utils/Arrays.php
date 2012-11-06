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
				foreach ($value as $key => $value2) {
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
	 * Fill empty with value
	 * @param array
	 * @param string
	 */
	public static function fillEmpty($array, $emptyValue = "-")
	{
		foreach ($array as $key => $value) {
			if (empty($value)) {
				$array[$key] = $emptyValue;
			}
		}

		return $array;
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
			if ($type == "int") {		
				$array[$key] = (($keepNull && is_null($value)) ? NULL : (int) $value);

			} elseif ($type == "float") {
				$array[$key] = (($keepNull && is_null($value)) ? NULL : (float) $value);

			} elseif ($type == "date") {
				$array[$key] = date($format, strtotime($value));
			}
		}

		return $array;
	}


	/**
	 * Get 1 column into array 	
	 * @param array
	 * @param string
 	 * @return array
	 */ 
	public static function extractColumn($array, $column)
	{
		$result = array();
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$value = iterator_to_array($value);
			}

			if (isset($value[$column]) || $value[$column] === NULL) {
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
			if ($value || $value === 0) {
				if (!is_array($value)) {
					$result[$key] = trim($value);

				} elseif (count($value)) {
					$result[$key] = $value;						
				}
			}
		}

		return $result;
	}


	/**	
	 * Order array by subkey
	 * @param array
	 * @param string
	 */
	public static function sortBySubKey(&$array, $subkey)
	{
		$keys = array();
		foreach ($array as $subarray) {
			$keys[] = $subarray[$subkey];
		}

		array_multisort($keys, SORT_ASC, $array);
	}


	/**	
	 * Order array by subkey
	 * @param mixed
	 * @return array
	 */
	public static function sortBySubKeyReverse(&$array, $subkey)
	{
		$keys = array();
		foreach ($array as $subarray) {
			$keys[] = $subarray[$subkey];
		}

		array_multisort($keys, SORT_DESC, $array);
	}


	/**	
	 * Find row with specific key value
	 * @param array
	 * @param string
	 * @param string
	 * @return mixed 
	 */
	public static function findByKeyValue(array $array, $key, $find, $returnArrayStrict = FALSE)
	{
		$return = array();

		foreach ($array as $value) {
			$compare = $value[$key];

			if (is_array($compare)) {
				while (is_array($compare)) {
					$compare = array_shift($compare); 
				}
			}

			if ($compare == $find) {
				$return[] = $value;
			}
		}

		if ($returnArrayStrict || count($return) > 1) { // more results -> array
			return $return;

		} elseif (count($return) == 1) {
			return $return[0];
		}

		return NULL;
	}

}