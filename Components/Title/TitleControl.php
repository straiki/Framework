<?php

namespace Components;

use Schmutzka;

class TitleControl extends Schmutzka\Application\UI\Control
{
	/** @var string */
	public $sep = " | ";

	/** @var string */
	public $mainTitleSep = " | ";

	/** @var string */
	public $alwaysShowMainTitle = FALSE;

	/** @var string */
	private $mainTitle;

	/** @var array */
	private $titles = array();


	public function render()
	{
		parent::useTemplate();

		if ($this->isHomepage()) {
			$title = $this->mainTitle;

		} else {
			$title = implode($this->sep, $this->titles);
			if ($this->alwaysShowMainTitle) {
				$title .= ($title ? $this->mainTitleSep : NULL) . $this->mainTitle;
			}
		}

		$this->template->title = $title;
		$this->template->render();
	}


	/********************** setters **********************/


	/**
	 * Add custom title
	 * @param string
	 * @return this
	 */
	public function addTitle($title)
	{
		$this->titles[] = $title;
		return $this;
	}


	/**
	 * @param string
	 * @return this
	 */
	public function setMainTitle($value)
	{
		$this->mainTitle = $value;
		return $this;
	}


	/********************** helpers **********************/


	/**
	 * @return bool
	 */
	private function isHomepage()
	{
		$name = $this->getPresenter()->name;
		$action = $this->getPresenter()->action;

		return ($action == "default" && in_array($name, array("Front:Homepage", "Homepage")));
	}

}
