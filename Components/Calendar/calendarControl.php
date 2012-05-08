<?php

use Nette\Application\UI\Control;

class CalendarControl extends Control
{

	/** @string zobrazovaný měsíc */
	private $activeMonth;

	/** @int  počet dnů v měsíci */
	private $daysCount;

	/** @array názvy měsíců ve zvoleném jazyce */
	private $monthNames = array(1=> "leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

	/** @array názvy dnů ve zvoleném jazyce */
	private $dayNames = array("Po", "Út", "St", "Čt", "Pá", "So", "Ne");


	public function __construct($activeMonth)
    {
		if(!isset($activeMonth)) {
			throw MemberAccessException("Missing parameter $activeMonth");
		}
        parent::__construct();
        $this->activeMonth= $activeMonth;
    }


	/**
	* Doplnění společných prvků do šablony
	* @$template
	*/
	private function fillInTemplate($template)
	{
		list($year, $month) = explode("-", $this->activeMonth);	
		$this->template->year = $year;
		$this->template->month = $month;

        $template->running_day = date('w',mktime(0,0,0, $month,1, $year))-1;
		if($template->running_day < 0) {
			$template->running_day += 7;
		}

        $template->days_in_month = $this->daysCount;
        $template->days_in_this_week = 1;
        $template->day_counter = 0;
        $template->dayNames = $this->dayNames;
        $template->monthNames = $this->monthNames;
        $template->activeMonth = $this->activeMonth;

		$template->rowCount = date("W", mktime(0, 0, 0, $month, $this->daysCount, $year))- date("W", mktime(0, 0, 0, $month, 1, $year)); // css purpohose
		if($template->rowCount < 0) {
			$template->rowCount = $template->rowCount+53;
		}

		$template->prevMonth = date("Y-m",strtotime("-1 month",strtotime($this->activeMonth)));
		$template->nextMonth = date("Y-m",strtotime("+1 month",strtotime($this->activeMonth)));

		return $template;
	}


	/**
	* Výpis malé verze
	**/
	public function renderMini()
	{
		$monthData = $this->monthData($this->activeMonth);

		$template = $this->template;
		$template->setFile(dirname(__FILE__) . "/renderMini.latte");
		$template = $this->fillInTemplate($template);
		$template->monthArray = $monthData;

		$template->render();
	}

	
	/**
	* Výpis běžné verze
	**/
	public function render()
	{
		$monthData = $this->monthData($this->activeMonth);

		$template = $this->template;
		$template->setFile(dirname(__FILE__) . "/render.latte");
		$template = $this->fillInTemplate($template);
		$template->monthArray = $monthData;

		$template->render();
	}


	/**
	* Výpis malé verze s menu
	**/
	public function renderMiniNav()
	{
		$monthData = $this->monthData($this->activeMonth);

		$template = $this->template;
		$template->showNavigation = TRUE;
		$template->setFile(dirname(__FILE__) . "/renderMini.latte");
		$template = $this->fillInTemplate($template);
		$template->monthArray = $monthData;

		$template->render();
	}


	/**
	* Získá data pro daný měsíc, zpracuje je a naplní pole měsíce
	* @string měsíc
	* @array pole s akcemi pro jednotlivé dny
	**/
	private function monthData($activeMonth)
	{
		// data pro tento měsíc
		$records = $this->presenter->models->calendar->getMonthData($activeMonth);

		// ohraničení měsíce a základní proměnné
		$monthStartTime = $activeMonth."-01 00:00:00";
		$monthEndTime =  date("Y-m-t 23:59:59", strtotime($monthStartTime));
		$this->daysCount = date("t",strtotime($activeMonth)); // délka měsíce v dnech

		$currentDay = $activeMonth."-01"; // aktivní den pro procházení
		$monthData = array();

		// rozdělíme data jednotlivým dnům
		for($i = 0; $i < $this->daysCount; $i++) { // projdeme celý měsíc

			// záznamy pro daný den
			$monthData[$currentDay]["active"] = FALSE;
			foreach($records as $row) {
				if($row["date_from"] <= $currentDay AND $row["date_to"] >=  $currentDay) {
					$monthData[$currentDay]["active"] = TRUE;
					$monthData[$currentDay]["actions"][] = $row;
				}
	
			}

			$currentDay = date("Y-m-d",strtotime("+ 1 day",strtotime($currentDay))); // další den
		}

		return $monthData;
	}


}