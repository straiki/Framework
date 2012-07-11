<?php

namespace Schmutzka\Diagnostics\Panels;

class SessionPanel extends \Schmutzka\Application\UI\Control implements \Nette\Diagnostics\IBarPanel
{

	/** @var \Nette\Http\Session $session */
	private $session;

	/** @var \Nette\DI\Context */
	public $context;

	/** @var array $hiddenSection */
	private $hiddenSections = array(
		"Nette.Forms.Form/CSRF",
		"Nette.Application/requests"
	);


	public function __construct(\Nette\Application\Application $application, \Nette\Http\Session $session, \Nette\DI\Container $context)
	{
		$this->session = $session;
		$this->context = $context;

		parent::__construct($application->getPresenter(), "SessionPanel");
	}


	/**
	 * Add section name in list of hidden
	 * @param string $sectionName
	 */
	public function hideSection($sectionName)
	{
		$this->hiddenSections[] = $sectionName;
	}


	/**
	 * Html code for DebugerBar Tab
	 * @return string
	 */
	public function getTab()
	{
		return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAMRJREFUeNpiZCAT2Orr6QOpAhYyNdcDqQAgdmAhw9YFQKwAxAaHL176yESirRegmh2Amh+CxFlIsNUAKpQA1HwRJs9EpK3Imjciq2Eh0lYQmADUvBBdLRMRtoLAAqDmQmyWERuIBlBXYQBmdIFHL18elJcQ3wBkWgCxBFQYRGcAxcHyxLjgAQ4xDHFGHAG4ARrfMI0N2AIQwwCgZnuoZgFCGjGiEag5Hhp1II0FhDSiGADU3A/NHAnEakSJd6jtZAGAAAMA89NIrMVVIPoAAAAASUVORK5CYII='>";
	}


	/**
	 * Html code for DebugerBar Panel
	 * @return string
	 */
	public function getPanel()
	{
		$template = parent::createTemplate();
		$template->session = $this->session;
		$template->hiddenSections = $this->hiddenSections;

		return $template;
	}

}