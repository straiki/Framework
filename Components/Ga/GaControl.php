<?php

namespace Components;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Control;


class GaControl extends Control
{
	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Nette\Http\Request */
	public $httpRequest;


	/**
	 * @param string
	 * @param string
	 */
	public function render($code, $domain = NULL)
	{
		if ( ! $this->paramService->productionMode) {
			return;
		}

		$this->template->code = $code;
		list(, $this->template->domain) = Strings::match($this->httpRequest->url->host, '~([^.]+.[^.]+)$~');
		$this->template->ssl = $this->httpRequest->isSecured();
	}

}
