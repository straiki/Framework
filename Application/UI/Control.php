<?php

namespace Schmutzka\Application\UI;

use Nette;
use Schmutzka;
use Components;
use NetteTranslator;

abstract class Control extends Nette\Application\UI\Control
{
	/** @var NetteTranslator\Gettext */
	protected $translator;

	/** @var Schmutzka\Templates\TemplateService */
	private $templateService;


	function injectBaseServices(Schmutzka\Templates\TemplateService $templateService, NetteTranslator\Gettext $translator = NULL)
	{
		$this->templateService = $templateService;
		$this->translator = $translator;
	}


	/**
	 * Create template and autoset file
	 * @param string
	 * @param bool
	 * @return Nette\Templating\FileTemplate
	 */
	public function createTemplate($class = NULL)
	{
		if ($this->templateService === NULL) {
			throw new \Exception("TemplateService is not available. Add component to config.");
		}

		$template = parent::createTemplate($class);
		$this->templateService->configure($template);

		if ($autosetFile && ! $template->getFile() && file_exists($this->getTemplateFilePath())) {
			$template->setFile($this->getTemplateFilePath());
		}

		return $template;
	}


	/** 
	 * Sets up template
	 * @param string
	 */
	public function useTemplate($name = NULL)
	{
		$this->template->setFile($this->getTemplateFilePath($name));
	}


	/**
	 * Derives template path from class name
	 * @param string
	 * @return string
	 */
	protected function getTemplateFilePath($name = "")
	{
		$class = $this->getReflection();
		return dirname($class->getFileName()) . "/" . $class->getShortName() . ucfirst($name) . ".latte";
	}


	/**
	 * FlashMessage component
	 * @return Components\FlashMessageControl
	 */
	protected function createComponentFlashMessage()
	{
		return new Components\FlashMessageControl;
	}

}
