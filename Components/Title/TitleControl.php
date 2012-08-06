<?php

namespace Components;

use Nette\Utils\Html,
	Nette\Utils\Arrays,
	Schmutzka\Utils\Name,
	Schmutzka\Utils\Neon;

class TitleControl extends \Nette\Application\UI\Control
{

	/** @var array */
	private $titles;

	/** @var array */
	private $context;

	/** @var bool */
	private $isHomepage = FALSE;


	public function __construct(\Nette\DI\Container $context)
	{
		parent::__construct();

		if (!file_exists(APP_DIR . "/config/titles.neon")) {
			throw new \Exception("Missing 'config/titles.neon'.");
		}

		$this->titles = Neon::loadConfigPart("titles.neon");

		if (!isset($this->titles["sep"])) {
			throw new \Exception("Separator parameter 'sep' missing.");
		}

		$this->context = $context;
	}


	/**
	 * Create title for particular location
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @return array
	 */
	protected function createTitle($titles, $presenter)
	{
		list($module, $presenter, $view) = Name::mpv($presenter);
		if ($presenter == "Homepage" AND $view == "default") {
			$this->isHomepage = TRUE;
		}


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
		}

		if (empty($titleArray["h1"]) OR is_array($titleArray["h1"])) {
			unset($titleArray["h1"]);
		}

		return $titleArray;
	}


	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$title = $this->createTitle($this->titles, $this->parent->presenter);

		if (!$this->isHomepage AND isset($this->titles["subOnly"]) AND $this->titles["subOnly"] == TRUE) {
			$title = array_pop($title);
		}
		else {
			$title = implode(" " . $this->titles["sep"]. " ", $title);
		}

		echo Html::el("title")->setHtml($this->translate($title));
	}


	/**
	 * Get title for h1 (last 1 only)
	 */
	public function renderH1($wrapper = NULL)
	{
		$title = $this->createTitle($this->titles, $this->parent->presenter);

		if (isset($title["h1"])) {
			echo Html::el($wrapper)->setHtml($this->translate($title["h1"]));
		}
	}


	/**
	 * Translate function
	 * @param string
	 * @2DO: move to control
	 */
	public function translate($string)
	{
		if ($this->context->hasService("translator")) {
			return $this->context->translator->translate($string);
		}

		return $string;
	}


}