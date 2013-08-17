<?php

namespace Schmutzka;

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
		if ($name != 'params' && isset($this->params->{$name})) {
			return $this->params->{$name};
		}
	}


	/**
	 * @return array|NULL
	 */
	public function getActiveModules()
	{
		if (isset($this->params->modules)) {
			return $this->params->modules;
		}

		return NULL;
	}


	/**
	 * @param string
	 * @return array
	 */
	public function getModuleParams($key)
	{
		if (Strings::contains($key, '\\')) {
			$key = Name::moduleFromNamespace($key, 'module');
		}

		$moduleName = $key . 'Module';
		if (isset($this->params->$moduleName)) {
			return $this->params->$moduleName;
		}

		return array();
	}

}
