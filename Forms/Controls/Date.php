<?php
/**
 * Form date field item
 * @author	Patrik Votoček
 */

namespace Schmutzka\Forms\Controls;

class Date extends BaseDateTime
{
	/** @var string */
	public static $format = "Y-n-j";

	/**
	 * @param string  control name
	 * @param string  label
	 * @param int  width of the control
	 * @param int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label, $cols, $maxLength);
		$this->control->type = "date";
		$this->control->data('nella-forms-date', $this->translateFormatToJs(static::$format));
	}
}
