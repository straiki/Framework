<?php

namespace Schmutzka\Components\WebLoader\Filter;

use Nette;
use WebLoader;
use lessc;

class LessFilter extends Nette\Object
{
	/** @var lessc */
	private $lessc;


	function inject(lessc $lessc)
	{
		$this->lessc = $lessc;
	}


	/**
	 * @param string
	 * @param WebLoader\Compiler
	 * @return string
	 */
	public function __invoke($code, WebLoader\Compiler $loader)
	{
		try {
			return $this->lessc->parse($code);

		} catch (\Exception $e) {
			return $code;
		}
	}

}
