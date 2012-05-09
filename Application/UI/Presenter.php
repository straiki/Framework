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
	DependentSelectBox\JsonDependentSelectBox;

abstract class Presenter extends \Nette\Application\UI\Presenter
{
	/** @object \Panels\User */
	public $userPanel;

	/** @object \Nette\Caching\Cache */
	public $cache;

	/** @object \Nette\Http\SessionSection */
	public $mySession;

	/** @object \Nette\Http\SessionSection */
	public $appSession;

	/** @var string */
	public $role = NULL;

	/** @var bool */
	public $logged = FALSE;


	public function beforeRender()
	{
		parent::beforeRender();

		// dependency select Json registration
		JsonDependentSelectBox::tryJsonResponse($this->presenter);
	}


	public function startup()
	{
		parent::startup();

		/* params shortcut */
		$this->params += $this->context->parameters;

		/** @var \Nette\Caching\Cache */
		$this->cache = $this->context->cache;

		/** @var \Nette\Session\Section */
		$sectionKey = sha1($this->params["wwwDir"]);
		$this->mySession = $this->session->getSection("mySession" . $sectionKey);
		$this->appSession = $this->session->getSection("appSession");


		/** userPanel registration */
		$this->userPanel = User::register($this->user, $this->session, $this->getContext());
		$this->userPanel->setNameColumn("email")
			->addCredentials("admin", "admin")
			->addCredentials("user", "user");


		// dev mailer
		if(!$this->params["productionMode"]) { // dev only
			Message::$defaultMailer = new \Schmutzka\Diagnostics\DumpMail($this->getContext()->session); // service conflict with @nette.mail
		}


		/** user status and role info  */
		if ($this->user->isLoggedIn()) {	
			$this->logged = TRUE;
			$role = $this->user->getRoles(); // what's his role?
			$this->role = array_shift($role);
		}
		elseif (!in_array($this->presenter->name, array("Homepage", "Front:Homepage"))) { // important, to not redirect to homepage (where login is)
			$this->appSession->backlink = $this->storeRequest();
		}

		$this->tpl($this->role);
		$this->tpl($this->logged);
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
		$this->flashMessage("Byli jste odhlášeni.","flash-info");
		$this->redirect(":Front:Homepage:default");
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


	/**
	 * Logging data into db component
	 */
	protected function createComponentDbLogger()
	{
		$time = microtime(TRUE) - Debugger::$time;
		$memory = memory_get_peak_usage();
		
		$mode = ($this->params["productionMode"] ? "prod" : "dev");
		$devOnly = (isset($this->params["dbLogger"]["devOnly"]) ? $this->params["dbLogger"]["devOnly"] : FALSE);

		if(!$devOnly OR $mode == "dev") { // dev or prod without devOnly
			if(!isset($this->data)) { // pokud je chyba, nelogujeme, asi?
				$logger = new dbLogger($this->getContext(), $time, $memory, $mode, $this->presenter);
			}
		}

		return $logger;
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
	 */
	public function tpl($var) {
		$clear = array("this->" => ""); // předáváme jinou proměnnou ($this->)?

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
		foreach($presenter as $key => $value) {
			if(is_array($value)) {
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
		foreach($array as $key => $value)	{
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
		$list[] = APP_DIR."/AdminModule/templates/@layout.latte"; // admin layout

		return $list;
	}

}