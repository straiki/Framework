<?php

namespace Schmutzka\Diagnostics\Panels;

class DumpMail extends \Schmutzka\Application\UI\Control implements \Nette\Diagnostics\IBarPanel
{

	/** @var \SessionSection */
	private $sessionData;

	/** @var \Nette\DI\Context */
	public $context;

	/** @var array */
	private $data = array();


	public function __construct(\Nette\Http\Session $session, \Nette\DI\Container $context)
	{
		$this->sessionData = $session->getSection("dumpMail");
		$this->context = $context;
	}


	/**
	 * Turns session into array
	 * @param data
	 * @return array
	 */
	private function getData($data)
	{
		$return = array();

		foreach ($data as $row) {
			$headers = $row->getHeaders();
			$mail = array(
				"from" => isset($headers["From"]) ? $headers["From"] : NULL,
				"to" => $headers["To"],
				"bcc" => isset($headers["Bcc"]) ? $headers["Bcc"] : NULL,
				"subject" => isset($headers["Subject"]) ? $headers["Subject"] : NULL,
				"body" => $row->getBody(),
				"bodyHtml" => $row->getHtmlBody(),
			);

			$return[] = $mail;
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
			return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAITSURBVBgZpcHLThNhGIDh9/vn7/RApwc5VCmFWBPi1mvwAlx7BW69Afeu3bozcSE7E02ILjCRhRrds8AEbKVS2gIdSjvTmf+TYqLu+zyiqszDMCf75PnnnVwhuNcLpwsXk8Q4BYeSOsWpkqrinJI6JXVK6lSRdDq9PO+19vb37XK13Hj0YLMUTVVyWY//Cf8IVwQEGEeJN47S1YdPo4npDpNmnDh5udOh1YsZRcph39EaONpnjs65oxsqvZEyTaHdj3n2psPpKDLBcuOOGUWpZDOG+q0S7751ObuYUisJGQ98T/Ct4Fuo5IX+MGZr95jKjRKLlSxXxFxOEmaaN4us1Upsf+1yGk5ZKhp8C74H5ZwwCGO2drssLZZo1ouIcs2MJikz1oPmapHlaoFXH1oMwphyTghyQj+MefG+RblcoLlaJG/5y4zGCTMikEwTctaxXq/w9kuXdm9Cuzfh9acujXqFwE8xmuBb/hCwl1GKAnGccDwIadQCfD9DZ5Dj494QA2w2qtQW84wmMZ1eyFI1QBVQwV5GiaZOpdsPaSwH5HMZULi9UmB9pYAAouBQbMHHrgQcnQwZV/KgTu1o8PMgipONu2t5KeaNiEkxgAiICDMCCFeEK5aNauAOfoXx8KR9ZOOLk8P7j7er2WBhwWY9sdbDeIJnwBjBWBBAhGsCmiZxPD4/7Z98b/0QVWUehjkZ5vQb/Un5e/DIsVsAAAAASUVORK5CYII=" />';
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
	 */
	static function register()
	{
		Debugger::addPanel(new self);
	}


}	