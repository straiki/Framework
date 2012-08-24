<?php

namespace Schmutzka\Localization;

class GoogleMaps extends \Nette\Object 
{

	/**
	 * Find address
	 * @param string
	 * @param string
	 * @param int
	 * @param string
	 */
	public static function getLocation($street, $city, $zip, $state)
	{
		$address = trim(strtr($street.",".$city.",".$zip.",".$state,array(" "=>"+")),",");
		$result = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false"));

		if ($result->status == "ZERO_RESULTS") {
			return FALSE;

		} else {
			$row = array_shift($result); // first result
			
			$data["formatted_address"] = (string) $row->formatted_address;
			$data["latitude"] = (float) $row->geometry->location->lat;
			$data["longitude"] = (float) $row->geometry->location->lng;
			$data["sjtsk"] = self::WGS84_SJTSK($latitude,$longitude);

			return $row;
		}
	}



	/**
	 * Converts WGS-84 into S-JTSK
	 * @param float $latitude (e.g.: 50.1580658)
	 * @param float $longtitude (e.g.: 14.3976173)
	 * @return array($x,$y)
	 */
	public static function WGS84_SJTSK($latitude,$longitude)
	{

		$coefs = array(
			"a" => array(
				2 => 703000,
				1058000,
				17 => 11.8067298100,
				-14311.19075,
				-71093.69068,
				0.0452721311,
				1469.29752,
				-62.16573827,
				1.746024222,
				1.482366057,
				-1.646574057,
				1.930950004
			),
			"b" => array(
				2 => 50,
				15,
				17 => 147.1808238,
				-110295.0611,
				9224.512054,
				-13.35425822,
				-192.8902631,
				-473.5502716,
				-4.564660084,
				-4.355296392,
				8.911019558,
				0.3614170182
			)
		);

		$Fi = (float) $latitude;
		$La = (float) $longitude;
		$dFi = (float) $Fi-$coefs["b"][2];
		$dLa = (float) $La-$coefs["b"][3];

		$x = $coefs["a"][2]+
			$coefs["a"][17]+
			$coefs["a"][18]*$dFi+
			$coefs["a"][19]*$dLa+
			$coefs["a"][20]*pow($dFi,2)+
			$coefs["a"][21]*$dFi*$dLa+
			$coefs["a"][22]*pow($dLa,2)+
			$coefs["a"][23]*pow($dFi,3)+
			$coefs["a"][24]*pow($dFi,2)*$dLa+
			$coefs["a"][25]*$dFi*pow($dLa,2)+
			$coefs["a"][26]*pow($dLa,3);

		$y = $coefs["a"][3]+
			$coefs["b"][17]+
			$coefs["b"][18]*$dFi+
			$coefs["b"][19]*$dLa+
			$coefs["b"][20]*pow($dFi,2)+
			$coefs["b"][21]*$dFi*$dLa+
			$coefs["b"][22]*pow($dLa,2)+
			$coefs["b"][23]*pow($dFi,3)+
			$coefs["b"][24]*pow($dFi,2)*$dLa+
			$coefs["b"][25]*$dFi*pow($dLa,2)+
			$coefs["b"][26]*pow($dLa,3);

		return array(
			"x" => $x,
			"y" => $y
		);
	}

}