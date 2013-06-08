<?php

namespace Schmutzka\Config;

use Nette;

class Configurator extends Nette\Configurator
{

	public function __construct()
	{
		parent::__construct();
		// $this->enableDebugger(__DIR__ . "/../../../log/");
	}

}
