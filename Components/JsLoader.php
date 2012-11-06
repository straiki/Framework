<?php

namespace Components;

use Schmutzka\Utils\Neon,
	Webloader,
	Webloader\FileCollection,
	Webloader\Filter,
	Webloader\Compiler,
	Nette;

class JsLoader extends Webloader\Nette\JavaScriptLoader
{

	/**
	 * @param string
	 * @param Nette\Application\Application
	 */
	public function __construct(Nette\Application\Application $application, $configPart = "js")
	{
		$basePath = $application->presenter->template->basePath;
	
		$files = new FileCollection(WWW_DIR . "/js");
		$filesArray = Neon::loadConfigPart("header.neon", $configPart);
		$files->addFiles($filesArray, "js");

		$compiler = Compiler::createJsCompiler($files, WWW_DIR . "/temp");

		if (!$application->presenter->params["debugMode"]) { // production only
			$compiler->addFilter(function ($code) {
				return Filter\JSMin::minify($code);
			});
		}

		parent::__construct($compiler, $basePath . "/temp");
	}

}