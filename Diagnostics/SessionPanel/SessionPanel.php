<?php

namespace Schmutzka\Panels;

class SessionPanel extends \Nette\Application\UI\Control implements \Nette\Diagnostics\IBarPanel
{

	/** @var \Nette\Http\Session $session */
	private $session;

	/** @var array $hiddenSection */
	private $hiddenSections = array(
		"Nette.Http.UserStorage/",
		"Nette.Forms.Form/CSRF",
		"Nette.Application/requests"
	);


	public function __construct(\Nette\Application\Application $application, \Nette\Http\Session $session)
	{
		parent::__construct($application->getPresenter(), $this->getId());
		$this->session = $session;
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
	 * Return panel ID
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}

	/**
	 * Html code for DebugerBar Tab
	 * @return string
	 */
	public function getTab()
	{
		$template = $this->getFileTemplate(__DIR__ . "/templates/tab.latte");
		return $template;
	}

	/**
	 * Html code for DebugerBar Panel
	 * @return string
	 */
	public function getPanel()
	{
		$template = $this->getFileTemplate(__DIR__ . "/templates/panel.latte");
		$template->session = $this->session;
		$template->hiddenSections = $this->hiddenSections;
		return $template;
	}

	/**
	 * Load template file path with aditional macros and variables
	 * @param string $templateFilePath
	 * @return \Nette\Templating\FileTemplate
	 */
	private function getFileTemplate($templateFilePath)
	{
		if (file_exists($templateFilePath)) {
			$template = new \Nette\Templating\FileTemplate($templateFilePath);
			$template->onPrepareFilters[] = callback($this, "templatePrepareFilters");
			$template->registerHelperLoader("Nette\Templating\Helpers::loader");
			$template->basePath = realpath(__DIR__);
			return $template;
		}
		else {
			throw new \Nette\FileNotFoundException("Requested template file is not exist.");
		}
	}

	/**
	 * Load latte and set aditional macros
	 * @param \Nette\Templating\Template $template
	 */
	public function templatePrepareFilters($template)
	{
		$template->registerFilter($latte = new \Nette\Latte\Engine());
		$set = \Nette\Latte\Macros\MacroSet::install($latte->getCompiler());
		$set->addMacro('src', NULL, NULL, 'echo \'src="\'.\Nette\Templating\Helpers::dataStream(file_get_contents(%node.word)).\'"\'');
		$set->addMacro('stylesheet','echo \'<style type="text/css">\'.file_get_contents(%node.word).\'</style>\'');
		$set->addMacro('clickableDump','echo \Nette\Diagnostics\Helpers::clickableDump(%node.word)');
	}

}