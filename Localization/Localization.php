<?php

namespace Schmutzka;

use Nette;

class Localization extends Nette\Object
{
	/** @var array  */
	public $allowedLangs = array();

	/** @var array  */
	public $defaultLang = "en";

	/** @inject @var Nette\Http\Request */
	public $httpRequest;

	/** @inject @var Nette\Http\Response */
	public $httpResponse;


	/**
	 * Detects language from cookie or request
	 */
	public function detectLang()
	{
		$cookieName = $this->getCookieName();
		$lang = $this->httpRequest->getCookie($cookieName);
		if ($lang && $this->isAllowed($lang)) {
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
		$this->allowedLangs = array_merge(array($this->defaultLang), $this->fetchAllowedLangs);
		return in_array($lang, $this->fetchAllowedLangs);
	}

}
