<?php

namespace Schmutzka;

use Nette;
use Schmutzka\Utils\Neon;


class Configurator extends Nette\Configurator
{

	/**
	 * @param bool|string|array
	 * @param bool
	 */
	public function __construct($debug = NULL, $autoloadConfig = TRUE)
	{
		parent::__construct();

		$this->parameters = $this->getParameters();

		// debugger
		if ($debug) {
			$this->setDebugMode($debug);
		}
		$this->enableDebugger($this->parameters['appDir'] . '/../log');

		// robot loader
		$this->setTempDirectory($this->parameters['appDir'] . '/../temp');
		$this->createRobotLoader()
			->addDirectory($this->parameters['appDir'])
			->addDirectory($this->parameters['libsDir'])
			->register();

		// configs
		$this->addConfig($this->parameters['libsDir'] . '/Schmutzka/configs/default.neon');
		if ($autoloadConfig) {
			if ($this->parameters['environment'] == 'development') {
				$this->addConfig($this->parameters['appDir'] . '/config/config.local.neon');

			} else {
				$this->addConfig($this->parameters['appDir'] . '/config/config.prod.neon');
			}
		}

		// modules
		$this->registerModules();
	}


	/**
	 * @param  array { [ string => string ] }
	 * @param  string
	 */
	public function loadConfigByHost($hostConfigs, $host)
	{
		foreach ($hostConfigs as $key => $config) {
			if ($key == $host) {
				$this->addConfig($this->parameters['appDir'] . '/config/' . $config, FALSE);
			}
		}
	}


	/********************** helpers **********************/


	/**
	 * Include paths to directories
	 * @return array
	 */
	private function getParameters()
	{
		$parameters = parent::getDefaultParameters();

		$rootDir = realpath(__DIR__ . '/../../../');
		$parameters['appDir'] = $rootDir . '/app/';
		$parameters['libsDir'] =  $rootDir . '/libs/';
		$parameters['wwwDir'] =  $rootDir . '/www/';
		$parameters['modulesDir'] =  $rootDir . '/libs/Schmutzka/modules/';

		return $parameters;
	}


	/**
	 * Add configs of active modules
	 */
	private function registerModules()
	{
		$parameters = Neon::fromFile($this->parameters['appDir'] . '/config/config.neon', 'parameters');

		if (isset($parameters['modules'])) {
			$this->addConfig($this->parameters['modulesDir'] . 'AdminModule/config.neon');
			foreach ($parameters['modules'] as $module) {
				$moduleDirConfig = ucfirst($module) . 'Module/config.neon';
				if (file_exists($config = $this->parameters['modulesDir'] . $moduleDirConfig)) {
					$this->addConfig($config);
				}

				if (file_exists($config = $this->parameters['appDir'] . $moduleDirConfig)) {
					$this->addConfig($config);
				}
			}
		}
	}

}
