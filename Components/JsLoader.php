<?php

namespace Components;

use Schmutzka\Utils\Neon;

class JsLoader extends \WebLoader\Nette\JavaScriptLoader
{

	public function __construct($basePath, $configPart = "js")
	{
		$filesArray = Neon::loadConfigPart("header.neon", $configPart);

		// d($basePath);
		$files = new \WebLoader\FileCollection(WWW_DIR . "/js");
		$files->addFiles($filesArray);

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . "/temp");

		// $compiler->addFileFilter(new \Webloader\Filter\jsShrink); // - breaking some code

		parent::__construct($compiler, $basePath . "/temp");
	}

}