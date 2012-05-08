<?php

use Nette\Http\Session;

namespace Schmutzka\Panels;

/**
 * Dumps sessions to DebugBar
 * @author Jan Dolecek <juzna.cz@gmail.com>
 * @link: http://forum.nette.org/cs/9575-tip-session-do-debug-panelu#p70798
 */
class Session implements \Nette\Diagnostics\IBarPanel {

	/** @Session */
	private $session;

	/**  @return */
	private $dump;


	public function __construct(\Nette\Http\Session $session)
	{
		$this->session = $session;
	}


	function getTab()
	{
		ob_start();
		require __DIR__ . '/templates/bar.session.tab.latte';
		return ob_get_clean();
	}


	function getPanel()
	{
		ob_start();
		require __DIR__ . '/templates/bar.session.panel.latte';
		return ob_get_clean();
	}
}