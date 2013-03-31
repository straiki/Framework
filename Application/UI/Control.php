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
	public function createTemplate($class = NULL, $autosetFile = TRUE)
	{
		$template = parent::createTemplate($class);

		if ($this->templateService === NULL) {
			d("missing template service in Control");
			dd($this);
		}

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
	 * Create template from file
	 * @param string
	 */	
	public function createTemplateFromFile($file)
	{	
		dd("where is this used? to stupid shortcut");
		$template = $this->createTemplate(NULL, FALSE);
		$template->setFile($file);

		return $template;
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
	 * Renders the default template
	 */
	public function render()
	{
		$this->template->render();
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
