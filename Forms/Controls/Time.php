<?php
/**
 * Form time field item
 * @author	Patrik Votoček
 */

namespace Schmutzka\Forms\Controls;

class Time extends BaseDateTime
{
	/** @var string */
	public static $format = "G:i";

	/**
	 * @param string  control name
	 * @param string  label
	 * @param int  width of the control
	 * @param int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label, $cols, $maxLength);
		$this->control->type = "time";
		$this->control->data('nella-forms-time', $this->translateFormatToJs(static::$format));
	}
}