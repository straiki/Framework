<?php

namespace Components;

use Schmutzka\Utils\Neon,
	Webloader,
	Webloader\FileCollection,
	Webloader\Filter,
	Webloader\Compiler,
	Nette;

class CssLoader extends Webloader\Nette\CssLoader
{

	/**
	 * @param string
	 * @param Nette\Application\Application
	 */
	public function __construct(Nette\Application\Application $application, $configPart = "css")
	{
		$basePath = $application->presenter->template->basePath;

		$files = new FileCollection(WWW_DIR . "/css");
		$filesArray = Neon::loadConfigPart("header.neon", $configPart);
		$files->addFiles($filesArray, "css");

		$compiler = Compiler::createCssCompiler($files, WWW_DIR . "/temp");
		$compiler->addFileFilter(new Filter\LessFilter);

		parent::__construct($compiler, $basePath . "/temp");
	}

}