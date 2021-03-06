<?php

namespace Schmutzka;

use Nette;


class Configurator extends Nette\Configurator
{

	/**
	 * @param bool|string|array
	 * @param bool
	 */
	public function __construct($debug = NULL, $autoloadConfig = TRUE)
	{
		parent::__construct();

		if ($debug) {
			$this->setDebugMode($debug);
		}
		$this->enableDebugger($this->dir . "/../log");

		$this->setTempDirectory($this->dir . "/../temp");
		$this->createRobotLoader()
			->addDirectory($this->dir)
			->addDirectory($this->dir . "/../libs/")
			->register();

		$this->addConfig($this->dir . "/../libs/Schmutzka/configs/default.neon", FALSE);

		if ($autoloadConfig) {
			if ($this->defaultParameters["environment"] == "development") {
				$this->addConfig($this->dir . "/config/config.local.neon", FALSE);

			} else {
				$this->addConfig($this->dir . "/config/config.prod.neon", FALSE);
			}
		}
	}


	/**
	 * @param  array { [ string => string ] }
	 * @param  string
	 */
	public function loadConfigByHost($hostConfigs, $host)
	{
		foreach ($hostConfigs as $key => $config) {
			if ($key == $host) {
				$this->addConfig($this->dir . "/config/" . $config, FALSE);
			}
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
