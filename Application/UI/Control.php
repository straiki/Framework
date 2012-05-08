<?php

/**
 * @author   Jan Tvrdík
 */

namespace Schmutzka\Application\UI;

class Control extends \Nette\Application\UI\Control
{

	/**
	 * Automatically registers template file
	 * @param string
	 * @return Nette\Templates\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->setFile($this->getTemplateFilePath());

		return $template;
	}


	/**
	 * Derives template path from class name
	 * @return string
	 */
	protected function getTemplateFilePath()
	{
		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$filename = $reflection->getShortName() . '.latte';
		return $dir . \DIRECTORY_SEPARATOR . $filename;
	}

}
