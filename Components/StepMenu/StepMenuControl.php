<?php

namespace Components;

use Schmutzka\Application\UI\Control;


/**
 * @method setClassList(array)
 * @method getClassList()
 * @method setSiteList(array)
 * @method getSiteList()
 */
class StepMenuControl extends Control
{
	/** @var array */
	private $siteList;

	/** @var array */
	private $classList = array(
		'prev' => 'prev',
		'current' => 'current',
		'next' => 'next'
	);


	public function renderDefault()
	{
		$this->template->siteList = $this->siteList;
		$this->template->classList = $this->classList;
		$this->template->next = FALSE;
	}

}
