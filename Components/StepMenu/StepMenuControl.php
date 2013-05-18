<?php

namespace Components;

use Schmutzka\Application\UI\Control;

class StepMenuControl extends Control
{

	/** @var array */
	private $siteList;

	/** @var array */
	private $classList = array(
		"prev" => "prev",
		"current" => "current",
		"next" => "next"
	);
	

	public function __construct($siteList, $class = array())
    {
        parent::__construct();
        $this->siteList = $siteList;

		foreach ($class as $key => $value)
		{
			$this->classList[$key] = $value;
		}
    }


	/**
	 * Default render
	 */
	public function render()
	{
		$this->template->siteList = $this->siteList;
		$this->template->classList = $this->classList;
		$this->template->next = FALSE;
		$this->template->render();
	}

}