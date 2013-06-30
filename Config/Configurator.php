<?php

namespace Schmutzka\Config;

use Nette;

class Configurator extends Nette\Configurator
{

	/**
	 * @param bool|array
	 */
	public function __construct($debug = NULL)
	{
		parent::__construct();

		$this->enableDebugger($this->dir . "/../log");
		if ($debug) {
			$this->setDebugMode($debug);
		}

		$this->setTempDirectory($this->dir . "/../temp");
		$this->createRobotLoader()
			->addDirectory($this->dir)
			->addDirectory($this->dir . "/../libs/")
			->register();

		$this->addConfig($this->dir . "/../libs/Schmutzka/defaultConfig.neon", FALSE);
		if ($this->defaultParameters["environment"] == "development") {
			$this->addConfig($this->dir . "/config/config.local.neon", FALSE);

		} else {
			$this->addConfig($this->dir . "/config/config.prod.neon", FALSE);
		}
	}


	/**
	 * @return array
	 */
	public function getDefaultParameters()
	{
		$defaultParameters = parent::getDefaultParameters();
		$defaultParameters["appDir"] = $this->dir;

		return $defaultParameters;
	}


	/**
	 * @return string
	 */
	public function getDir()
	{
		return APP_DIR;
	}

}
