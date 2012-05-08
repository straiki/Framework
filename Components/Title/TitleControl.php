<?php

namespace Components;

use Nette\Utils\Html,
	Nette\Utils\Arrays;

/**
 * Title component for static use (only, so far)
 * Controls all titles
 * @2DO/idea: combine wtih translate table (FILTER_TABLE = $this->container->params["..."])
 * @2DO: dynamic - http://forum.nette.org/cs/9871-reseni-title-stranky-z-jednoho-mista
 */
class TitleControl extends \Nette\Application\UI\Control
{
	/** @var array */
	private $titles;

	/** @var string */
	private $sep = " | ";

	/** @var bool */
	private $debug;


	public function __construct($debug = FALSE)
	{
		parent::__construct();
		$this->debug = $debug;

		if (!file_exists(APP_DIR."/config/titles.neon")) {
			throw new \Exception("Missing 'config/titles.neon'.");
		}

		$titlesFile = file_get_contents(APP_DIR."/config/titles.neon");
		$this->titles = \Nette\Utils\Neon::decode($titlesFile);

		if(isset($this->titles["sep"])) {
			$this->sep = " ".trim($this->titles["sep"])." ";
		}
	}


	/**
	 * Create title for particular location
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @return array
	 */
	protected function createTitle($titles, $module, $presenter, $view)
	{
		if (isset($titles[$module]["main"])) {
			$titleArray[] = $titles[$module]["main"];
		}
		else {
			if (!isset($titles["main"])) {
				throw new \Exception("Missing title for 'main' in config.neon");
			}
			$titleArray[] = $titles["main"];
		}

		if ($module) {
			$titleArray["h1"] = Arrays::get($titles, array($module, $presenter, $view), 
				Arrays::get($titles, array($module, $presenter, "main"),
					Arrays::get($titles, array($module, $presenter), 
						Arrays::get($titles, array($module, "main"), NULL)
					)
				)
			);
	
		}
		else {
			$titleArray["h1"] = Arrays::get($titles, array($presenter, $view),
				Arrays::get($titles, array($presenter, "main"), NULL)
			);
		}
		


		if (isset($titleArray["h1"]) AND is_array($titleArray["h1"])) {
			if (isset($titleArray["h1"]["logged"]) AND isset($titleArray["h1"]["unlogged"])) {
				$titleArray["h1"] = ($this->parent->user->loggedIn ? $titleArray["h1"]["logged"] : $titleArray["h1"]["unlogged"]);
			}
			else {

			}
		}

		// clear
		if (empty($titleArray["h1"]) OR is_array($titleArray["h1"])) {
			unset($titleArray["h1"]);
		}


		if(!isset($titleArray["h1"]) AND $this->debug) { // debug active
			throw new \Exception("Missing title for ".$presenter.":".$view." in config.neon");
		}

		return $titleArray;
	}


	/**
	 * Get title from presenter
	 * @param string
	 * @param array
	 * @return array
	 */
	private function titleFromPresenter($presenter, $titles)
	{
		list($module, $presenter, $view) = $this->mpv($presenter);
		return $this->createTitle($titles, $module, $presenter, $view);
	}


	/**
	 * Get module, presenter and view
	 * @var presenter
	 */
	private function mpv($activePresenter)
	{
		$module = NULL;
		$presenter = $activePresenter->name;
		if(strpos($presenter, ":")) {
			list($module, $presenter) = explode(":", $presenter);
		}
		$view = $activePresenter->view;
	
		return array($module, $presenter, $view);
	}


	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$title = $this->titleFromPresenter($this->parent->presenter, $this->titles);

		$title = implode($this->sep, $title);
		echo Html::el("title")->setHtml($title);
	}


	/**
	 * Get title for h1 (last 1 only)
	 */
	public function renderH1($wrapper = NULL)
	{
		$title = $this->titleFromPresenter($this->parent->presenter, $this->titles);

		if (isset($title["h1"])) {
			echo Html::el($wrapper)->setHtml($title["h1"]);
		}
		// $title = (isset($title["h1"]) ? $title["h1"] : array_shift($title));
	}


}