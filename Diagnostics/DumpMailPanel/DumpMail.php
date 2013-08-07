<?php

namespace Schmutzka\Diagnostics\Panels;

use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\IBarPanel;
use Schmutzka\Application\UI\Control;


class DumpMail extends Control implements IBarPanel
{
	/** @var Nette\Http\SessionSection */
	private $sessionData;

	/** @var array */
	private $data = array();


	/**
	 * @param Nette\Http\Session
	 * @param Nette\Application\Application
	 */
	public function __construct(Nette\Http\Session $session, Nette\Application\Application $application)
	{
		$this->sessionData = $session->getSection('dumpMail');
		parent::__construct($application->presenter, 'dumpMailPanel');
	}



	/**
	 * Turns session into array
	 * @param data
	 * @return array
	 */
	private function getData($data)
	{
		$return = array();
		foreach ($data as $key => $row) {
			if (is_array($row) && isset($row['to_email'])) {
				$return[] = $row;
			}
		}

		return $return;
	}


	/**
	 * Renders HTML code for custom tab.
	 * @return string
	 * @see Nette\IDebugPanel::getTab()
	 */
	public function getTab()
	{
		$this->data = $this->getData($this->sessionData);

		if (count($this->data)) {
			return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAITSURBVBgZpcHLThNhGIDh9/vn7/RApwc5VCmFWBPi1mvwAlx7BW69Afeu3bozcSE7E02ILjCRhRrds8AEbKVS2gIdSjvTmf+TYqLu+zyiqszDMCf75PnnnVwhuNcLpwsXk8Q4BYeSOsWpkqrinJI6JXVK6lSRdDq9PO+19vb37XK13Hj0YLMUTVVyWY//Cf8IVwQEGEeJN47S1YdPo4npDpNmnDh5udOh1YsZRcph39EaONpnjs65oxsqvZEyTaHdj3n2psPpKDLBcuOOGUWpZDOG+q0S7751ObuYUisJGQ98T/Ct4Fuo5IX+MGZr95jKjRKLlSxXxFxOEmaaN4us1Upsf+1yGk5ZKhp8C74H5ZwwCGO2drssLZZo1ouIcs2MJikz1oPmapHlaoFXH1oMwphyTghyQj+MefG+RblcoLlaJG/5y4zGCTMikEwTctaxXq/w9kuXdm9Cuzfh9acujXqFwE8xmuBb/hCwl1GKAnGccDwIadQCfD9DZ5Dj494QA2w2qtQW84wmMZ1eyFI1QBVQwV5GiaZOpdsPaSwH5HMZULi9UmB9pYAAouBQbMHHrgQcnQwZV/KgTu1o8PMgipONu2t5KeaNiEkxgAiICDMCCFeEK5aNauAOfoXx8KR9ZOOLk8P7j7er2WBhwWY9sdbDeIJnwBjBWBBAhGsCmiZxPD4/7Z98b/0QVWUehjkZ5vQb/Un5e/DIsVsAAAAASUVORK5CYII=">';
		}
	}


	/**
	 * Renders HTML code for custom panel.
	 * @return string
	 */
	public function getPanel()
	{
		if (count($this->data)) {
			$template = parent::createTemplate();
			$template->data = $this->data;

			return $template;
		}
	}


	/**
	 * Registers panel to Debug bar
	 * @return UserPanel
	 */
	public static function register($session, $presenter)
	{
		$panel = new self($session, $presenter);
		Debugger::addPanel($panel);

		return $panel;
	}

}