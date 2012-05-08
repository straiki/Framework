<?php

/**
 * Author: jmac
 * Date: 9/14/11
 * Time: 5:46 PM
 * Desc: HighRoller Line Chart SubClass
 */

class HighRollerLineChart extends HighRoller
{

	public function __construct()
	{
		parent::__construct('line');

		$this->chart->type = 'line';
		$this->xAxis = new HighRollerXAxis();
		$this->yAxis = new HighRollerYAxis();
		$this->plotOptions->line = new HighRollerPlotOptionsByChartType($this->chart->type);
	}
}