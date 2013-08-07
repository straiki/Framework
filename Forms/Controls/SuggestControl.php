<?php

namespace Schmutzka\Forms\Controls;

use Nette\Utils\Html;

/**
 * Suggest control
 * bootstrap.min.js + bootstrap.min.css required
 * 2DO: https://github.com/twitter/bootstrap/pull/3682
	source: function (query, process) {
		$.get('/autocomplete', { q: query }, function (data) {
			process(data)
		})
	}
 */

class SuggestControl extends \Nette\Forms\Controls\TextBase
{

	/** @var string */
	private $dataSource;


	/**
	 * @param string
	 * @param array
	 */
	public function __construct($label, array $suggestList)
	{
		parent::__construct($label);

		$temp = array();
		foreach ($suggestList as $row) { // num keys required! add some protection
			$temp[] = $row;
		}
		$suggestList = $temp;

		$suggestList = json_encode($suggestList);
		$suggestList = strtr($suggestList, array('{' => '[', '}' => ']'));

		$this->dataSource = $suggestList;
	}


	/**
	 * Generates control and suggets script handler
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->attrs['autocomplete'] = 'off';
		$control->attrs['data-provide'] = 'typeahead';
		$control->attrs['data-source'] = $this->dataSource;

		return $control;
	}

}