<?php

namespace Schmutzka\Utils;

class Whatpulse extends \Nette\Object
{

	/**
	 * Get user data
	 * @param int
	 */
	public static function getUserStats($userId) 
	{
		$url = "http://whatpulse.org/api/user.php?UserID=" . $userId;
		$data = file_get_contents($url);

		$data = new \XmlToArray($data);
		$data = $data->array;
		$data = $data["UserStats"][0];

		return $data;
	}

}