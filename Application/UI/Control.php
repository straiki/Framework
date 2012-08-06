<?php

namespace Schmutzka\Application\UI;

class Control extends \Nette\Application\UI\Control
{

	/**	 
	 * Create template
	 * @param string
	 */
	public function createTemplate($class = NULL, $autosetFile = TRUE)
	{
		$template = parent::createTemplate($class);

		$this->context->template->configure($template);

		if ($autosetFile && !$template->getFile()) {
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


	/**
	 * Handles requests to create component / form?
	 * @param string
	 */
	protected function createComponent($name)
	{
		$component = parent::createComponent($name);

		if ($component === NULL) {
			$componentClass = "Components\\" . $name . "Control";
			if (class_exists($componentClass)) {
				$component = new $componentClass;
			}
		}

		return $component;
	}


	/**
	 * Translate shortcut 
	 */
	public function translate($string)
	{
		if ($this->context->hasService("translator")) {
			return $this->context->translator->translate($string);
		}		

		return $string;
	}

}