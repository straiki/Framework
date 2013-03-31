<?php

namespace Schmutzka;

use Nette;

class Localization extends Nette\Object
{
	/** @var array  */
	public $allowedLangs = array();

	/** @var array  */
	public $defaultLang = "en";

	/** @var Nette\Http\Request */
	private $httpRequest;

	/** @var Nette\Http\Response  */
	private $httpResponse;


	/**
	 * @param Nette\Http\Request
	 * @param Nette\Http\Response
	 */
	public function __construct(Nette\Http\Request $httpRequest, Nette\Http\Response $httpResponse) 
	{ 
		$this->httpRequest = $httpRequest;
		$this->httpResponse= $httpResponse;
	}	


	/**
	 * Detects language from cookie or request
	 */
	public function detectLang()
	{
		$cookieName = $this->getCookieName();
		$lang = $this->httpRequest->getCookie($cookieName);
		if ($lang AND $this->isAllowed($lang)) {
			return $lang;
		}

		$lang = $this->httpRequest->detectLanguage($this->allowedLangs);	
		if ($this->isAllowed($lang)) {
			return $lang;
		}

		return $this->defaultLang;
	}

	
	/**
	 * Saves lang via cookie
	 * @param string
	 */
	public function setLang($lang)
	{
		$cookieName = $this->getCookieName();
		$this->httpResponse->setCookie($cookieName, $lang, "+ 100 days");
	}
	

	/**
	 * Get lang cookie name	 
	 */
	private function getCookieName()
	{
		return "_lang_" . $this->httpRequest->url->authority;
	}


	/**
	 * Is lang allowed?
	 * @param string
	 */
	private function isAllowed($lang)
	{
		$this->allowedLangs = array_merge(array($this->defaultLang), $this->allowedLangs);
		return in_array($lang, $this->allowedLangs);
	}

}
