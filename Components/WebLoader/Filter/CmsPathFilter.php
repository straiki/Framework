<?php

namespace Schmutzka\Components\WebLoader\Filter;

use Nette;
use WebLoader;

class CmsPathFilter extends Nette\Object
{

	/**
	 * @param string
	 * @param WebLoader\Compiler
	 * @return string
	 */
	public function __invoke($code, WebLoader\Compiler $loader)
	{
		// echo $code;
		// dd();
		// dd("Z");

		$code = strtr($code, array(
			"url('../img" => "url('../../images/cms",
			'url("../img' => 'url("../../images/cms',
			"url(../img" => "url(../../images/cms",
			"url('chosen" => "url('../../images/cms/chosen/chosen"
		));

		return $code;
	}

}
