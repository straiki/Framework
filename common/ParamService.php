<?php

namespace Schmutzka;

use Nette;
use Nette\Utils\Strings;
use Schmutzka\Utils\Arrays;
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
	 * @return array
	 */
	public function getActiveModules()
	{
		$modules = $this->params->cmsSetup->modules;
		$array = array();

		Arrays::sortBySubkey($modules, "rank");

		foreach ($modules as $key => $row) {
			if ($row->active) {
				$array[$key] = $row->title;
			}
		}

		return $array;
	}


	/**
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
