<?php

namespace Models;

class CalendarControl extends Base
{


	/**
	 * Získá data pro daný měsíc
	 * @string měsíc
	 **/
	public function getMonthData($month)
	{
		$monthStart = $month."-01";
		$monthEnd = $month."-31";

		// 1. všechny pro daný měsíc
		return $this->db()->demand->where("(date_from BETWEEN ? AND ?) OR (date_to BETWEEN ? AND ?)", $monthStart, $monthEnd, $monthStart, $monthEnd)
//			->where("date_to > ?", date("Y-m-d"))
			->where("display", 1) // pouze aktivní, dále rozlišovat naplněné etc.
			->where("approved", 1)
			->select("id, date_from, date_to, count, display, title")
			->fetchPairs("id");

		// 1. všechny pro daný měsíc
		return $this->db()->demand->where("(date_from BETWEEN ? AND ?) OR (date_to BETWEEN ? AND ?)", $monthStart, $monthEnd, $monthStart, $monthEnd)
			->where("date_to > ?", date("Y-m-d"))
			->where("display", 1) // pouze aktivní, dále rozlišovat naplněné etc.
			->where("approved", 1)
			->select("id, date_from, date_to, count, display, title")
			->fetchPairs("id");
	}


}