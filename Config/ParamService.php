<?php

namespace Schmutzka\Config;

use Nette;
use Nette\Utils\Strings;
use Schmutzka\Utils\Name;

class ParamService extends Nette\Object
{
	/** @var array */
	public $params = array();


	/**
	 * @param array
	 */
	public function __construct($parameters)
	{
		$this->params = Nette\ArrayHash::from($parameters);
	}


	/**
	 * Direct value access
	 * @param string
	 */
	public function &__get($name)
	{
		if ($name != "params" && isset($this->params->{$name})) {
			return $this->params->{$name};
		}
	}


	/**
	 * Get active modules
	 * @return array
	 */
	public function getActiveModules()
	{
		$modules = $this->params->cmsSetup->modules;

		$array = array();
		foreach ($modules as $key => $row) {
			if ($row->active) {
				$array[$key] = $row->title;
			}
		}

		return $array;
	}


	/**
	 * Get active module params
	 * @param string
	 * @return array
	 */
	public function getModuleParams($key)
	{
		if (Strings::contains($key, "\\")) {
			$key = Name::moduleFromNamespace($key, "module");
		}

		$modules = $this->params->cmsSetup->modules;
		if (isset($modules[$key])) {
			return $modules[$key];
		}

		return array();
	}
	
}
