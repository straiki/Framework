<?php

namespace Schmutzka\Utils;

use Nette;


class Arrays extends Nette\Object
{

	/**
	 * Check if arrays has all keys
	 * @param array
	 * @param array
	 * @param bool
	 */
	public static function hasAllKeys(array $array, array $requiredKeys)
	{
		if (self::hasMoreLevels($array)) {
			foreach ($array as $value) {
				foreach ($value as $key => $value2) {
					if ( ! in_array($key, $requiredKeys)) {
						return FALSE;
					}
				}
			}

		} else {
			foreach ($requiredKeys as $key) {
				if ( ! array_key_exists($key, $array)) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}


	/**
	 * Array key summary
	 * @param array
	 * @param string
	 * @return int
	 */
	public static function keySum(array $array, $key)
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
	 * Clear all empty values
	 * @param array
	 * @return array
	 */
	public static function clearEmpty($array)
	{
		$result = array();
		foreach ($array as $key => $value) {
			if ($value || $value === 0) {
				if ( ! is_array($value)) {
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
		$array = (array) $array;

		foreach ($array as $subarray) {
			if (isset($subarray[$subkey])) {
				$keys[] = $subarray[$subkey];

			} else {
				$keys[] = 10e3;
			}
		}

		array_multisort($keys, SORT_ASC, $array);
	}


	/**
	 * Order array by subkey
	 * @param array
	 * @param string
	 *
	 */
	public static function sortBySubKeyReverse(&$array, $subkey)
	{
		$keys = array();
		foreach ($array as $subarray) {
			$keys[] = $subarray[$subkey];
		}

		array_multisort($keys, SORT_DESC, $array);
	}


	/********************** helpers **********************/


	/**
	 * Determine if is one level array or multiple level array
	 * @param array
	 * @return bool
	 */
	private static function hasMoreLevels($array)
	{
		return is_array($array[key($array)]);
	}

}
