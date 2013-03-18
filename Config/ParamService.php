<?php

namespace Schmutzka\Config;

use Nette;
use Schmutzka\Utils\Neon;

class ParamService extends Nette\Object
{
	/** @var array */
	public $params = array();


	/**
	 * @param array
	 */
	public function __construct($parameters) 
	{ 
		$this->params = $parameters;
	}


	/**
	 * Direct value access
	 * @param string
	 */
	public function &__get($name)
	{
		if ($name != "params" && isset($this->params[$name])) {
			return $this->params[$name];
		}
	}


	/**
	 * Get active modules
	 * @return array
	 */
	public function getActiveModules()
	{
		$data = Neon::fromFile("cms.neon");
		$modules = $data["parameters"]["cmsSetup"]["modules"];

		$array = array();
		foreach ($modules as $key => $row) {
			if ($row["active"]) {
				$array[$key] = $row["title"];
			}
		}

		return $array;
	}


	/**
	 * Get active module params
	 * @param string
	 * @return array
	 */
	public function getModuleParams($module)
	{
		$data = Neon::fromFile("cms.neon");
		$modules = $data["parameters"]["cmsSetup"]["modules"];

		return $modules[$module];
	}
	
}
