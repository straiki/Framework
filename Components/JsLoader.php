<?php

namespace Components;

use Schmutzka\Utils\Neon;

class JsLoader extends \WebLoader\Nette\JavaScriptLoader
{

	/**
	 * @param string
	 * @param \Nette\Application\Application
	 */
	public function __construct(\Nette\Application\Application $application, $configPart = "js")
	{
		$basePath = $application->presenter->template->basePath;
	
		$filesArray = Neon::loadConfigPart("header.neon", $configPart);

		$files = new \WebLoader\FileCollection(WWW_DIR . "/js");
		$files->addFiles($filesArray);

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . "/temp");

		parent::__construct($compiler, $basePath . "/temp");
	}

}