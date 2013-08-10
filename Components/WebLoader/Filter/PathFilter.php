<?php

namespace Schmutzka\Components\WebLoader\Filter;

use Nette;
use WebLoader;


class PathFilter extends Nette\Object
{

	/**
	 * @param string
	 * @param WebLoader\Compiler
	 * @return string
	 */
	public function __invoke($code, WebLoader\Compiler $loader)
	{
		$code = strtr($code, array(
			'url(../' => 'url(../../',
			"url('../" => "url('../../",
			'url("../' => 'url("../../'
		));

		return $code;
	}

}
