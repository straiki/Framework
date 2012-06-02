<?php

/**
 * @author   Jan Tvrdík
 * @author   Tomáš Votruba
 */

namespace Schmutzka\Application\UI;

use Schmutzka\Templates\MyHelpers,
	Schmutzka\Templates\MyMacros;

class Control extends \Nette\Application\UI\Control
{

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
	 * FlashMessage component
	 * @return \Components\FlashMessageControl
	 */
	protected function createComponentFlashMessage()
	{
		return new \Components\FlashMessageControl;
	}


	/**
	 * Automatically registers template file and all filters
	 * @param string
	 * @return Nette\Templates\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->setFile($this->getTemplateFilePath());

		// Latte, Haml, macros
		$this->templatePrepareFilters($template);
		
		// helpers
		$helpers = new MyHelpers($this->parent->context, $this->parent->presenter);
		$template->registerHelperLoader(array($helpers, "loader"));

		return $template;
	}


	/**
	 * Register filters
	 */
	public function templatePrepareFilters($template)
	{
		$latte = new \Nette\Latte\Engine;

		MyMacros::install($latte->compiler);

		$template->registerFilter(new \Nette\Templating\Filters\Haml);
		$template->registerFilter($latte);
	}


	/**
	 * Derives template path from class name
	 * @return string
	 */
	protected function getTemplateFilePath()
	{
		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$filename = $reflection->getShortName() . ".latte";

		return $dir . \DIRECTORY_SEPARATOR . $filename;
	}

}