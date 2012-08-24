<?php

namespace Schmutzka\Forms\Controls;

use Nette\Utils\Html;

/**
 * 2DO in the future - https://github.com/twitter/bootstrap/pull/3682
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

	/** @var bool */
	private $useJs = FALSE;


    /**
     * @param string
     * @param array
     */
    public function __construct($label, array $suggestList)
    {
        parent::__construct($label);

		/*
		$dataSource = "[";
		// dd($suggestList);
		$dataSource .= json_encode($suggestList);

		foreach ($suggestList as $value) {
			// js :$dataSource .= "{ id: ". $key . ", name: '" . $name . "' }, ";
			// $dataSource .= '&quot;' . htmlspecialchars($value) . '&quot;, ';
		}

		$dataSource .= "]";
		*/

		// num keys required! add some protection
		$temp = array();
		foreach ($suggestList as $row) {
			$temp[] = $row;
		}
		$suggestList = $temp;
	
		$suggestList = json_encode($suggestList);
		$suggestList = strtr($suggestList, array("{" => "[", "}" => "]"));

        $this->dataSource = $suggestList;
    }


    /**
     * Generates control and suggets script handler
     */
    public function getControl()
    {
        $control = parent::getControl();
		$control->attrs["autocomplete"] = "off";
		$control->attrs["data-provide"] = "typeahead";
		$control->attrs["data-source"] = $this->dataSource;

		/*
		// $control->attrs["name"] . "_suggest";

		//$class = $control->attrs["name"] . "_suggest";
		$control->attrs["class"] = (isset($control->attrs["class"]) ? $control->attrs["class"] . "  " . $class : $class);

		$text = "$( '." . $class . "').typeahead({ source: [" . $this->dataSource . "]});";
        echo $js = Html::el("script")->setText($text);
		*/

		return $control;
    }

}