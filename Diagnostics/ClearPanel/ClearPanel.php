<?php

namespace Schmutzka\Diagnostics\Panels;

use Nette;
use Nette\Diagnostics\Debugger;
use Schmutzka;
use Schmutzka\Utils\Filer;

class ClearPanel extends Schmutzka\Application\UI\Control implements Nette\Diagnostics\IBarPanel
{
	/** @var bool */
	private $debugMode;


	/**
	 * @param Nette\DI\Container
	 */
	public function __construct(Nette\DI\Container $context)
	{
		parent::__construct($context->application->getPresenter(), "clearPanel");
		$this->debugMode = $context->parameters["debugMode"];
	}

	/********************** temp clearing **********************/


	/**
	 * Clear temp/cache
	 */
	public function handleClearTempCache()
	{
		if ($this->debugMode) {
			Filer::emptyFolder(TEMP_DIR . "/cache");
		}
		$this->redirect("this");
	}


	/**
	 * Clear www/temp
	 */
	public function handleClearWwwTemp()
	{
		if ($this->debugMode) {
			Filer::emptyFolder(WWW_DIR . "/temp");
		}

		$this->redirect("this");
	}


	/**
	 * Clear temp/cache and www/temp
	 */
	public function handleClearBoth()
	{
		if ($this->debugMode) {
			Filer::emptyFolder(TEMP_DIR . "/cache");
			Filer::emptyFolder(WWW_DIR . "/temp");
		}

		$this->redirect("this");
	}


	/**
	 * Get tab
	 */
	public function getTab()
	{
		return "<span><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFuSURBVBgZBcG/S1RxAADwz3teyp3XFUUWNVSoRGQR3dLQIESBbUZt9gekm9XW2lRbNDv0gxbJWoJoCcT+ABskTgcDDwLpOD19d+/73rfPJ4kAANaejUx03t5eBZIIgKe34r3JB7OTVVvZuzf9lderiKIoip7MLba+xY24H4v4N36PC635uSgFIJ2/Pz7ppH19w66aHk/nqQCfk8LU1BWJAyMyo3Y1bV2nwpeh8nxxthg+Vm+ZUFVKHDjhK1UqlJeK52E61LOkasOhRDAic8EWKp/qxaupmdOO6Fi3bVyiEAQdA6Th7tjMGYcyDTcdtWlUoqYtypHmjy/atadrX6JpU5QaMhDlSPNTFX9kMj0H6rr+gYFCjnSw3XNZ2y9dPfT1lUq5UkA6+Phb3TU3NJArHFeKhtTkSBc+rC//0NBQVbNmwphzGu5oCztUGDz8udydbSrlVmI9eSkIirzYKZokESw+yl+EdtgL75eWAID/yIWfXhcZhKEAAAAASUVORK5CYII='></span>";
	}


	/**
	 * Get panel
	 */
	public function getPanel()
	{
		return $this->template;
	}


	/**
	 * Returns panel ID.
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}


	/**
	 * Registers panel to Debug bar
	 * @return UserPanel
	 */
	public static function register($context)
	{
		$panel = new self($context);
		Debugger::addPanel($panel);

		return $panel;
	}

}