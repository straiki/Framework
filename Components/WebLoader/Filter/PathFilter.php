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
			"url('../img" => "url('../../images",
			'url("../img' => 'url("../../images',
			"url(../img" => "url(../../images",
			"url(../images" => "url(../../images",
			"url('../images" => "url('../../images",
			'url("../images' => 'url("../../images',
		));

		return $code;
	}

}
