<?php

namespace Schmutzka\Security;

use Nette;
use Nette\PhpGenerator\ClassType;

class HttpAuthExtension extends Nette\Config\CompilerExtension
{

	function afterCompile(ClassType $class)
	{
		$config = $this->getConfig();

		if (count($config)) {
			$initialize = $class->methods['initialize'];

			$initialize->addBody('$auth = new Schmutzka\Security\HttpAuthenticator( $this->getByType(\'Nette\Http\IResponse\'), ?, ? );',
				array($config['username'], $config['password']));
			$initialize->addBody('$auth->run();');
		}
	}



	static function register(Nette\Config\Configurator $configurator, $prefix = 'httpAuth')
	{
		$class = __CLASS__;
		$configurator->onCompile[] = function ($configurator, $compiler) use ($prefix, $class) {
			$compiler->addExtension($prefix, new $class);
		};
	}
}
