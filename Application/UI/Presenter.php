<?php

namespace Schmutzka\Application\UI;

use Schmutzka\Forms\Replicator,
	Schmutzka\Diagnostics\Panels\UserPanel,
	Schmutzka\Templates\TemplateFactory,
	Schmutzka\Utils\Filer,
	Nette\Mail\Message,
	Nette\Http\Url,
	Nette\Security\Identity,
	Nette\Utils\Strings,
	Nette\Utils\Finder,
	Components\CssLoader,
	Components\JsLoader,
	DependentSelectBox\JsonDependentSelectBox;

abstract class Presenter extends \Nette\Application\UI\Presenter
{
	/** @persistent */
	public $lang;

	/** @var \Panels\User */
	public $userPanel;

	/** @var \Nette\Caching\Cache */
	public $cache;

	/** @var \Nette\Http\SessionSection */
	public $mySession;

	/** @var \Nette\Http\SessionSection */
	public $appSession;

	/** @var string */
	public $role = "";

	/** @var bool */
	public $logged = FALSE;

	/** @var bool */
	protected $runStopwatch = FALSE;

	/** @var string */
	protected $onLogoutLink = ":Front:Homepage:default";

	/** @var  Schmutzka\Services\ParamService */
	protected $paramService;

	/** @var string */	
	private $referer;


	public function beforeRender()
	{
		parent::beforeRender();
		JsonDependentSelectBox::tryJsonResponse($this->presenter);
	}


	/**
	 * Inject services
	 * @param Schmutzka\Services\ParamService
	 */
	public function injectServices(Schmutzka\Services\ParamService $paramService) 
	{ 
		$this->paramService = $paramService;
	}

	public function startup()
	{
		parent::startup();

		$this->params += $this->context->parameters;

		$this->cache = $this->context->cache;

		$sectionKey = substr(sha1($this->params["wwwDir"]), 6);
		$this->mySession = $this->session->getSection("mySession_" . $sectionKey);
		$this->appSession = $this->session->getSection("appSession");

		
		if ($this->params["debugMode"]) {
			Message::$defaultMailer = new \Schmutzka\Diagnostics\Panels\DumpMail($this->getContext()->session); // service conflict with @nette.mail
		}

		$this->userPanel = UserPanel::register($this->user, $this->session, $this->context);
	
		$this->user->storage->setNamespace("user_ " . $sectionKey); 

		// user status and role info
		if ($this->user->loggedIn) {	
			$this->logged = TRUE;
			$this->role = $this->user->getRole();

			if (isset($this->params["logUserActivity"])) {
				$this->user->logUserActivity($this->params["logUserActivity"]);
			}

		} elseif (!in_array($this->presenter->name, array("Homepage", "Front:Homepage"))) { // important, to not redirect to homepage, where login is
			if (!is_array($this->signal) OR !in_array("logout", $this->signal)) { // do not save logout signal either (results in logout after login)
				$this->appSession->backlink = $this->storeRequest();
			}
		}

		$this->template->role = $this->role;
		$this->template->logged = $this->logged;

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
		if ($this->getContext()->hasService("translator")) {
			$message = $this->getContext()->translator->translate($message);
		}

		return parent::flashMessage($message, $type);
	}


	/**
	 * Automated login
	 * @param string
	 */
	public function autologin($user)
	{
        if (!($user instanceof User)) {
			$user = $this->context->database->user->where($user)->fetchRow();
        }

		unset($user["password"]);

		$identity = new Identity($user["id"], (isset($user["role"]) ? $user["role"] : "user"), $user);
		$this->user->login($identity);
	}


	/* ************************ handlers ************************ */


	/**
	 * Logout	
	 */
	public function handleLogout()
	{
		$this->user->logout();
		$this->flashMessage("Byli jste odhlášeni.", "flash-info");

		if ($this->onLogoutLink) {
			$this->redirect($this->onLogoutLink);
		}

		if (Strings::startsWith($this->link("Front:Homepage:default"), "error")) {
			$this->redirect("Homepage:default");	

		} else {
			$this->redirect("Front:Homepage:default");	
		}
	} 


	/* *********************** templates ************************ */


	/**	 
	 * Create template
	 * @param string
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$this->context->template->configure($template, $this->lang);
		return $template;
	}


	/**
	 * Find template files
	 */
	public function formatTemplateFiles()
	{
		$templateFiles = parent::formatTemplateFiles();

		if ($this->useMobileTemplates && MobileDetection::isMobile()) {
			$templateFiles = array_map(function($path) {
					return str_replace("/templates", "/templatesMobile", $path);
			}, $templateFiles);
		}

		return $templateFiles;
	}


	/**	
	 * Find template layout
	 */
	public function formatLayoutTemplateFiles()
	{
		$layoutTemplateFiles = parent::formatLayoutTemplateFiles();
		$layoutTemplateFiles[] = APP_DIR . "/AdminModule/templates/@layout.latte"; // admin layout

		if ($this->useMobileTemplates && MobileDetection::isMobile()) {
			$layoutTemplateFiles = array_map(function($path) {
					return str_replace("/templates", "/templatesMobile", $path);
			}, $layoutTemplateFiles);
		}

		return $layoutTemplateFiles;
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

	/* *********************** components ************************ */
	

	/**
	 * Title component
	 * @return \Components\TitleControl
	 */
	protected function createComponentTitle()
	{
		return $this->context->createTitleControl();
	}


	/**
	 * Css component
	 * @return \Components\CssLoader
	 */
	protected function createComponentCss()
	{
		return $this->context->createCssControl();
	}


	/**
	 * Js component 
	 * @return \Components\JsLoader
	 */
	protected function createComponentJs()
	{
		return $this->context->createJsControl();
	}


	/**
	 * Handles requests to create component / form?
	 * @param string
	 */
	protected function createComponent($name)
	{
		$component = parent::createComponent($name);

		if ($component === NULL) {
			$componentClass = "Components\\" . $name . "Control";
			if (class_exists($componentClass)) {
				$component = new $componentClass;
			}
		}

		return $component;
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
		if ($this->getContext()->hasService("translator")) {
			return $this->getContext()->translator->translate($text);
		}
	
		return $text;
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

}