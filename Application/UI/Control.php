<?php

namespace Schmutzka\Application\UI;

use Nette;
use Components;

abstract class Control extends Nette\Application\UI\Control
{

	/**	 
	 * Create template and autoset file
	 * @param string
	 * @param bool
	 * @return Nette\Templating\FileTemplate;
	 */
	public function createTemplate($class = NULL, $autosetFile = TRUE)
	{
		$template = parent::createTemplate($class);
		$this->parent->templateService->configure($template);

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


	/********************* components *********************/


	/**
	 * FlashMessage component
	 * @return Components\FlashMessageControl
	 */
	protected function createComponentFlashMessage()
	{
		return new Components\FlashMessageControl;
	}


	/********************** localization **********************/


	/**
	 * Translate
	 * @param string
	 */
	public function translate($string)
	{
		dd("Control - translator - is neccessary?");
		if ($this->parent->translator) {
			return $this->parent->translator->translate($string);
		}		

		return $string;
	}

}
