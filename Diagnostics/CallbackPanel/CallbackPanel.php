<?php

/**
 * @author Patrik Votoček
 * @author Milan Šulc
 */

namespace Schmutzka\Diagnostics\Panels;

use Nette;
use Schmutzka\Utils\Filer;

class CallbackPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	/** @var bool */
	private static $registered = FALSE;

	/** @var Nette\DI\Container */
	private $container;

	/** @var array [ name => string, callback => callable, args => array() ] */
	private $callbacks;

	/** @var bool */
	private $active = TRUE;


	/**
	 * @param Nette\DI\Container
	 * @param Nette\Http\Request
	 * @param array
	 */
	public function __construct(Nette\DI\Container $container, Nette\Http\Request $request, $callbacks = array())
	{
		$this->container = $container;
		$this->active = $container->parameters["debugMode"];

		// Clean session
		$this->callbacks["session"] = array(
			'name' => "Clear session",
			'callback' => callback($this, "clearSession"),
			'args' => array(),
		);

		// Clean logs
		$this->callbacks["logs"] = array(
			'name' => "Clear logs",
			'callback' => callback($this, "clearLogs"),
			'args' => array(array(Nette\Caching\Cache::ALL => TRUE)),
		);

		// Clean temp
		$this->callbacks["temp"] = array(
			'name' => "Clear temp (latte, cache)",
			'callback' => callback($this, "clearTemp"),
			'args' => array()
		);

		// Clean webtemp
		$this->callbacks["webtemp"] = array(
			'name' => "Clear web temp (css, js)",
			'callback' => callback($this, "clearWebTemp"),
			'args' => array()
		);

		// Merge custom callbacks
		$this->callbacks = array_merge($this->callbacks, $callbacks);

		// Check signal receiver
		if ($this->active && ($cb = $request->getQuery("callback-do", false))) {
			if ($cb === "all") {
				$this->invokeCallbacks();
			} else {
				$this->invokeCallback($cb);
			}
		}
	}


	/**
	 * Process signal and invoke callback
	 * @param $name
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	private function invokeCallback($name)
	{
		if (strlen($name) > 0 && array_key_exists($name, $this->callbacks)) {
			$this->callbacks[$name]['callback']->invokeArgs($this->callbacks[$name]['args']);

		} else {
			throw new \InvalidArgumentException("Callback '" . $name . "' doesn't exist.");
		}
	}


	/**
	 * Invoke all callbacks
	 * @return void
	 */
	private function invokeCallbacks()
	{
		foreach ($this->callbacks as $callback) {
			$callback['callback']->invokeArgs($callback['args']);
		}
	}


	/********************** PREPARED CALLBACKS **********************/


	/**
	 * Clear session storage
	 * @param array $args
	 */
	public function clearSession($args = array())
	{
		$session = $this->container->getService("session");
		$session->clean();

		if (!$session->isStarted()) {
			$session->clean();

		} else {
			$session->destroy();
			$session->start();
		}
	}


	/**
	 * Clear logs folder
	 */
	public function clearLogs()
	{
		Filer::emptyFolder(LIBS_DIR . "/../log/");
	}


	/**
	 * Clear cache/temp storage
	 */
	public function clearTemp()
	{
		Filer::emptyFolder(LIBS_DIR . "/../temp/cache/");
	}


	/**
	 * Clear webtemp storage
	 */
	public function clearWebTemp()
	{
		Filer::emptyFolder(WWW_DIR . "/webtemp/");
	}


	/** INTERFACE *****************************************************************************************************/


	/**
	 * Returns if activated
	 * @return bool
	 */
	public function isActive()
	{
		return $this->active;
	}


	/**
	 * Renders HTML code for custom tab
	 * @see Nette\Diagnostics\IBarPanel::getTab()
	 * @return string
	 */
	public function getTab()
	{
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAK8AAACvABQqw0mAAAABh0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzT7MfTgAAAY9JREFUOI2lkj1rVUEQhp93d49XjYiCUUFtgiBpFLyWFhKxEAsbGy0ErQQrG/EHCII/QMTGSrQ3hY1FijS5lQp2guBHCiFRSaLnnN0di3Pu9Rpy0IsDCwsz8+w776zMjP+J0JV48nrufMwrc2AUbt/CleMv5ycClHH1UZWWD4MRva4CByYDpHqjSgKEETcmHiHmItW5STuF/FfAg8HZvghHDDMpkKzYXScPgFcx9XBw4WImApITn26cejEAkJlxf7F/MOYfy8K3OJGtJlscKsCpAJqNGRknd+jO6TefA8B6WU1lMrBZ6fiE1R8Zs7hzVJHSjvJnNMb/hMSmht93IYIP5Qhw99zSx1vP+5eSxZmhzpzttmHTbcOKk+413Sav4v3J6ZsfRh5sFdefnnhr2Gz75rvHl18d3aquc43f1/BjaN9V1wn4tq6eta4LtnUCQuPWHmAv0AOKDNXstZln2/f3zgCUX8oFJx1zDagGSmA1mn2VmREk36pxw5NgzVqDhOTFLhjtOgMxmqVOE/81fgFilqPyaom5BAAAAABJRU5ErkJggg==">Callbacks';
	}


	/**
	 * Renders HTML code for custom panel
	 * @see Nette\Diagnostics\IBarPanel::getPanel()
	 * @return string
	 */
	public function getPanel()
	{
		$items = $this->callbacks;
		ob_start();
		require_once __DIR__ . "/CallbackPanel.latte";
		return ob_get_clean();
	}


	/**
	 * Register this panel
	 * @param Nette\DI\Container $container
	 * @param array $callbacks
	 * @throws Nette\InvalidStateException
	 * @return void
	 */
	public static function register(Nette\DI\Container $container, $callbacks = array())
	{
		if (self::$registered) {
			throw new Nette\InvalidStateException("Callback panel is already registered");
		}

		Nette\Diagnostics\Debugger::$bar->addPanel(new static($container, $callbacks));
		self::$registered = TRUE;
	}

}
