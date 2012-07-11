<?php

/**
 * Sometimes doo :)
 */

namespace Schmutzka\Forms\Controls;

use Nette\Forms\IControl,
	Time;

class TimeControl extends \Nette\Forms\Controls\BaseControl
{
	/** @var Time|NULL */
	protected $value;

	/** @var string */
	private $className = "time";


	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = "time";
	}


	/**
	 * Generates control's HTML element.
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->addClass($this->className);

		return $control;
	}

	
	/**
	 * Is entered value valid? (empty value is also valid!)
	 */
	public static function validateValid(IControl $control)
	{
		if (!$control instanceof self) throw new Nette\InvalidStateException("Unable to validate " . get_class($control) . " instance.");
		$value = $control->value;

		dd($values);

		return (empty($control->rawValue) || $value instanceof Time);
	}

}