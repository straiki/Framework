<?php

namespace Schmutzka\Application\UI;

use Schmutzka\Templates\TemplateFactory;

class Control extends \Nette\Application\UI\Control
{

	/**	 
	 * Create template
	 * @param string
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		$templateFactory = $this->context->createTemplateFactory();
		$templateFactory->configure($template);

		if (!$template->getFile()) {
			$template->setFile($this->getTemplateFilePath());
		}

		return $template;
	}


	/** 
	 * Sets up template
	 */
	public function useTemplate($name)
	{
		$this->template->setFile($this->getTemplateFilePath($name));
	}


	/**
	 * Derives template path from class name
	 * @return string
	 */
	protected function getTemplateFilePath($name = "")
	{
		$class = $this->getReflection();
		return dirname($class->getFileName()) . "/" . $class->getShortName() . ucfirst($name) . ".latte";
	}


	/********************* shortcuts *********************/


	/**
	 * FlashMessage component
	 * @return \Components\FlashMessageControl
	 */
	protected function createComponentFlashMessage()
	{
		return new \Components\FlashMessageControl;
	}


	/**
	 * Context shortcut
	 */
	final public function getContext()
	{
		return $this->parent->context;
	}


	/**
	 * Model shortcut 
	 */
	final public function getModels()
	{
		return $this->context->models;
	}

}