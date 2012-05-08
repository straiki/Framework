<?php

namespace Components;

use Schmutzka\Utils\Neon;

class CssLoader extends \WebLoader\Nette\CssLoader
{

	public function __construct($basePath, $configPart = "css" )
	{
		$filesArray = Neon::loadConfigPart("header.neon", $configPart);

		$files = new \WebLoader\FileCollection(WWW_DIR . "/css");
		$files->addFiles($filesArray);

		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . "/temp");

		$compiler->addFileFilter(new \Webloader\Filter\LessFilter);

		parent::__construct($compiler, $basePath . "/temp");
	}

}
