<?php

namespace Components;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Application\UI\Control;

class GaControl extends Control
{
	/** @var Schmutzka\Config\ParamService */
	private $paramService;

	/** @var Nette\Http\Request */
	private $httpRequest;


	public function inject(Schmutzka\Config\ParamService $paramService, Nette\Http\Request $httpRequest)
	{
		$this->paramService = $paramService;
		$this->httpRequest = $httpRequest;
	}


	/**
	 * @param string
	 * @param string|NULL
	 */
	public function render($code, $domain = NULL)
	{
		parent::useTemplate();

		if (! $this->paramService->productionMode) {
			return;
		}

		$this->template->code = $code;
		list(, $this->template->domain) = Strings::match($this->httpRequest->url->host, '~([^.]+.[^.]+)$~');
		$this->template->ssl = $this->httpRequest->isSecured();

		$this->template->render();
	}

}
