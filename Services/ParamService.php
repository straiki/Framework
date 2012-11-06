<?php

namespace Schmutzka\Services;

use Nette;

class ParamService extends \Nette\Object
{
	/** @var public */
	public $params;


	/**
	 * @param Nette\DI\Container 
	 */
	public function __construct(Nette\DI\Container $context) 
	{ 
		$this->params = Nette\ArrayHash::from($context->parameters);
	}


	/**
	 * Direct access
	 * @param string
	 */
	public function &__get($name)
	{
		if ($name != "params") {
			return $this->params->{$name};
		}

		return parent::__get($name);
	}	
	
}