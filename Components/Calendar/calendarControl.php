<?php

namespace Components;

use Schmutzka\Application\UI\Control;

class CalendarControl extends Control
{
	/** @string zobrazovaný měsíc */
	private $activeMonth;

	/** @int počet dnů v měsíci */
	private $daysCount;

	/** @var array */
	private $monthNames = array(1=> "leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

	/** @var array */
	private $dayNames = array(1 => "Po", "Út", "St", "Čt", "Pá", "So", "Ne");

	/** @string NotORM_Result */
	private $data;


	/**
	 * @param string
	 * @param NotORM_Result
	 */
	public function __construct($activeMonth, \NotORM_Result $data = NULL)
    {
        parent::__construct();
        $this->activeMonth= $activeMonth;
	
		if ($data) {
			$this->data = $data;
		}
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


	/********************** render **********************/


	/**
	* Výpis malé verze
	**/
	public function renderMini()
	{
		parent::useTemplate("mini");
		$monthData = $this->monthData($this->activeMonth);
		$this->fillInTemplate($this->template);
		$this->template->monthArray = $monthData;
		$this->template->render();
	}

	
	/**
	* Výpis běžné verze
	*/
	public function render()
	{
		$monthData = $this->monthData($this->activeMonth);
		$this->fillInTemplate($this->template);
		$this->template->monthArray = $monthData;
		$this->template->render();
	}


	/**
	* Výpis malé verze s menu
	**/
	public function renderMiniNav()
	{
		parent::useTemplate("miniNav");

		$monthData = $this->monthData($this->activeMonth);

		$template = $this->template;
		$template->showNavigation = TRUE;

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
		if ($this->data) {
			$records = $this->data;

		} else {
			$records = $this->presenter->models->calendarControl->getMonthData($activeMonth);	
		}

		// ohraničení měsíce a základní proměnné
		$monthStartTime = $activeMonth."-01 00:00:00";
		$monthEndTime =  date("Y-m-t 23:59:59", strtotime($monthStartTime));
		$this->daysCount = date("t",strtotime($activeMonth)); // délka měsíce v dnech

		$currentDay = $activeMonth . "-01"; // aktivní den pro procházení
		$monthData = array();

		// rozdělíme data jednotlivým dnům
		for($i = 0; $i < $this->daysCount; $i++) { // projdeme celý měsíc

			// záznamy pro daný den
			$monthData[$currentDay]["active"] = FALSE;
			foreach($records as $row) {
				// if ($row["date_from"] <= $currentDay AND $row["date_to"] >=  $currentDay) {
				if ($row["date"] == $currentDay) {
					$monthData[$currentDay]["active"] = TRUE;
					$monthData[$currentDay]["actions"][] = $row;
				}
	
			}

			$currentDay = date("Y-m-d",strtotime("+ 1 day",strtotime($currentDay))); // další den
		}

		return $monthData;
	}


}