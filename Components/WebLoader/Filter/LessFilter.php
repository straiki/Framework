<?php

namespace Schmutzka\Components\WebLoader\Filter;

use Nette;
use WebLoader;
use lessc;

class LessFilter extends Nette\Object
{
	/** @inject @var lessc */
	public $lessc;


	/**
	 * @param string
	 * @param WebLoader\Compiler
	 * @return string
	 */
	public function __invoke($code, WebLoader\Compiler $loader)
	{
		return $this->lessc->parse($code);
	}

}
