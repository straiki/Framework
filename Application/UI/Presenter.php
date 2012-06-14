<?php

namespace Schmutzka\Application\UI;

use Schmutzka\Forms\Replicator,
	Schmutzka\Diagnostics\Panels\User,
	Schmutzka\Diagnostics\dbLogger,
	Schmutzka\Templates\MyHelpers,
	Schmutzka\Templates\MyMacros,
	Nette\Diagnostics\Debugger,
	Nette\Mail\Message,
	Components\CssLoader,
	Components\JsLoader,
	DependentSelectBox\JsonDependentSelectBox,
	Nette\Http\Url;

abstract class Presenter extends \Nette\Application\UI\Presenter
{
	/** @var \Panels\User */
	public $userPanel;

	/** @var \Nette\Caching\Cache */
	public $cache;

	/** @var \Nette\Http\SessionSection */
	public $mySession;

	/** @var \Nette\Http\SessionSection */
	public $appSession;

	/** @var string */
	public $role = NULL;

	/** @var bool */
	public $logged = FALSE;


	/** @var bool */
	protected $runStopwatch = FALSE;

	/** @var string */
	protected $onLogoutLink = ":Front:Homepage:default";


	/** @var string */	
	private $referer;


	public function beforeRender()
	{
		parent::beforeRender();
		JsonDependentSelectBox::tryJsonResponse($this->presenter);
	}


	public function startup()
	{
		parent::startup();

		$this->params += $this->context->parameters;

		$this->cache = $this->context->cache;

		$sectionKey = substr(sha1($this->params["wwwDir"]), 6);
		$this->mySession = $this->session->getSection("mySession_" . $sectionKey);
		$this->appSession = $this->session->getSection("appSession");

		$this->user->storage->setNamespace("user_ " . $sectionKey); 

		/** userPanel registration - buggy */
		/*
		$this->userPanel = User::register($this->user, $this->session, $this->getContext());
		$this->userPanel->setNameColumn("email")
			->addCredentials("admin", "admin")
			->addCredentials("user", "user");
		*/


		if ($this->params["debugMode"]) {
			Message::$defaultMailer = new \Schmutzka\Diagnostics\DumpMail($this->getContext()->session); // service conflict with @nette.mail
		}

		// user status and role info
		if ($this->user->loggedIn) {	
			$this->logged = TRUE;
			$role = $this->user->getRoles();
			$this->role = array_shift($role);
		}
		elseif (!in_array($this->presenter->name, array("Homepage", "Front:Homepage"))) { // important, to not redirect to homepage, where login is
			if (!is_array($this->signal) OR !in_array("logout", $this->signal)) { // do not save logout signal either (results in logout after login)
				$this->appSession->backlink = $this->storeRequest();
			}
		}

		$this->tpl($this->role);
		$this->tpl($this->logged);

		// referer
		$this->updateReferer($this->mySession, $this->presenter->context->httpRequest);
	}


	/**
	 * Flash message including translator
	 * @param string
	 * @param string
	 */
	public function flashMessage($message, $type = "flash-success")
	{		
		if($this->getContext()->hasService("translator")) { // automatic translator! cool
			$message = $this->getContext()->translator->translate($message);
		}

		return parent::flashMessage($message, $type);
	}


	

	/* ************************ handlers ************************ */


	/**
	 * Logout	
	 */
	public function handleLogout()
	{
		$this->user->logout();
		$this->flashMessage("Byli jste odhlášeni.", "flash-info");
		$this->redirect($this->onLogoutLink);
	} 


	/* *********************** templates ************************ */


	/**	 
	 * Haml, helpers, language
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		// Latte, Haml, macros
		$this->templatePrepareFilters($template);
		
		// Translator
		if ($this->context->hasService("translator")) { // translate service registered
			if (isset($this->params["lang"])) { // set default language
				$this->context->translator->setLang($this->params["lang"]); 
			}
			$template->setTranslator($this->context->translator);
		}

		// helpers
		$helpers = new MyHelpers($this->getContext(), $this->getPresenter());
		$template->registerHelperLoader(array($helpers, "loader"));

		return $template;
	}


	/**
	 * Register filters
	 */
	public function templatePrepareFilters($template)
	{
		$latte = new \Nette\Latte\Engine;

		MyMacros::install($latte->compiler);

		$template->registerFilter(new \Nette\Templating\Filters\Haml);
		$template->registerFilter($latte);
	}


	/* *********************** components ************************ */
	

	/**
	 * Css component
	 * @return \Components\CssLoader
	 */
	protected function createComponentCss()
	{
		return new CssLoader($this->template->basePath);
	}


	/**
	 * Js component 
	 * @return \Components\JsLoader
	 */
	protected function createComponentJs()
	{
		return new JsLoader($this->template->basePath);
	}


	/**
	 * Css component for AdminModule
	 * @return \Components\CssLoader
	 */
	protected function createComponentCssAdmin()
	{
		return new CssLoader($this->template->basePath, "cssAdmin");
	}


	/**
	 * Js component for AdminModule
	 * @return \Components\JsLoader
	 */
	protected function createComponentJsAdmin()
	{
		return new JsLoader($this->template->basePath, "jsAdmin");
	}


	/**
	 * Title component
	 * @return \Components\TitleControl
	 */
	protected function createComponentTitle()
	{
		return new \Components\TitleControl;
	}


	/**
	 * FlashMessage component
	 * @return \Components\FlashMessageControl
	 */
	protected function createComponentFlashMessage()
	{
		return new \Components\FlashMessageControl;
	}


	/* *********************** shortcuts ************************ */


	/**
	 * Model shortcut 
	 */
	final public function getModels()
	{
		return $this->context->models;
	}


	/**	
	 * Translator shortucut
	 */
	public function translate($text)
	{
		return $this->getContext()->translator->translate($text);
	}


	/**
	 * Add variable into the template
	 * @param mixed
	 * @2DO: improve
	 */
	public function tpl($var)
	{
		$clear = array("this->" => "");

		$trace = debug_backtrace();
		$i = !isset($trace[1]['class']) && isset($trace[1]['function']) && $trace[1]['function'] === 'dump' ? 1 : 0;
		if (isset($trace[$i]['file'], $trace[$i]['line']) && is_file($trace[$i]['file'])) {
			$lines = file($trace[$i]['file']);
			preg_match('#\(\$(.*)\)#', $lines[$trace[$i]['line'] - 1], $m); // ještě vychytat, aby to přesně sedělo
		
			if (isset($m[1])) {
				$varName = strtr($m[1], $clear);
				$this->template->$varName = $var;
			}
		}
	}


	/**
	 * Takes array values from presenter
	 * @param $this
	 * @return array
	 */
	protected function getPresenterArrays($presenter)
	{
		$array = array();
		foreach ($presenter as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $value;
			}
		}
		return $array;
	}


	/**
	 * Adds every list to the template
	 * @param array
	 */
	public function listsToTemplate($array)
	{
		foreach ($array as $key => $value) {
			$this->template->{$key} = $value;
		}	
	}


	/* ********************* modularity ********************* */


	/**	
	 * Add layout address for module
	 */
	public function formatLayoutTemplateFiles()
	{
		$list = parent::formatLayoutTemplateFiles();
		$list[] = APP_DIR . "/AdminModule/templates/@layout.latte"; // admin layout

		return $list;
	}


	/********************* debugging *********************/


	/**
	 *	@use $this->stopwatchStart(__METHOD__);
	 */
	private function stopwatchStart($methodName)
	{	
		if ($this->runStopwatch) {
			$methodName = explode("::", $methodName);
			Stopwatch::start(array_pop($methodName));
		}
	}


	/**
	 *	@use $this->stopwatchStop(__METHOD__);
	 */
	private function stopwatchStop($methodName)
	{	
		if ($this->runStopwatch) {
			$methodName = explode("::", $methodName);
			Stopwatch::stop(array_pop($methodName));
		}
	}

	/********************* helpers *********************/


	/**
	 * Update referer, if changed
	 * @param \Nette\Session\SessionSection
	 * @param \httpRequest
	 */
	protected function updateReferer(&$session, $http) 
	{
		$previous = $session->referer;

		// get this url
		$url = new Url($http->url);
		$url->query = NULL;
		$url = $url->absoluteUrl;

		// compare with this referer - drop shits
		$present = new Url($http->referer);
		$present->query = NULL;
		$present = $present->absoluteUrl;

		if ($present != $url OR empty($previous)) { // it's not the same, return new one
			$return = $present;
		}
		else {
			$return = $previous; // the same, return old one
		}

		$this->referer = $session->referer = $return;
	}


	/**
	 * Update user identity data
	 * @param array
	 */
	public function updateIdentity($values)
	{
		foreach($this->user->identity->data as $key => $value) {
			if (array_key_exists($key, $values)) {
				$this->user->identity->{$key} = $values[$key];
			}
		}
	}

}