<?php

namespace Schmutzka\Xml;

/*
 * PHP Script to convert XML to an associate array
 * ===============================================
 *
 * Usage
 * -----
 *
 * $xml = '<foo></foo>';
 *
 * $XMLToArray = new XmlToArray($xml);
 *
 * print_r($XMLToArray->array);
 *
 * @package XML_To_Array
 * @copyright 2010 ElbertF http://elbertf.com
 * @license http://sam.zoy.org/wtfpl/COPYING DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 *
 */

/*
 * Convert XML to an array
 * @abstract
 */
class XmlToArray extends \Nette\Object
{
	public $array = array();


	/**
	 * Initialize
	 * @param $xml
	 */
	function __construct(&$xml)
	{
		$parser = xml_parser_create();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

		xml_parse_into_struct($parser, $xml, $values);

		xml_parser_free($parser);

		foreach ( $values as $value )
		{
			$tag = $value['tag'];

			$i = isset($this->array[$tag]) ? count($this->array[$tag]) : 0;

			switch ( $value['type'] )
			{
				case 'open':
					$currentTag = &$this->array[$tag];

					if ( isset($value['attributes']) )
					{
						$currentTag[$i]['_ATTR'] = $value['attributes'];
					}

					$currentTag[$i]['_RECURSION'] = &$this->array;
					$this->array                  = &$currentTag[$i];

					break;
				case 'complete':
					$currentTag = &$this->array[$tag];

					if ( isset($value['attributes']) )
					{
						$currentTag[$i]['_ATTR'] = $value['attributes'];
					}


//					$currentTag[$i]['_VALUE'] = isset($value['value']) ? $value['value'] : ''; // původní

					if(isset($value["value"])) {
						if(is_array($value["value"]) OR isset($currentTag[$i])) {
							$currentTag[$i][$tag] = $value['value'];
						}
						else {
							$currentTag = $value['value'];
						}
					}

					break;
				case 'close':
					$this->array = &$this->array['_RECURSION'];

					break;
			}
		}

		$this->removeRecursion($this->array);

		return $this->array;
	}


	/**
	 * Remove recursion in result array
	 * @param $array
	 */
	function removeRecursion(&$array)
	{
		if ($array) {
			foreach ($array as $k => $v) {
				if ($k === "_RECURSION") {
					unset($array[$k]);

				} elseif (is_array($array[$k])) {
					$this->removeRecursion($array[$k]);

				}
			}
		}
	}

}