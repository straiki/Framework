<?php

namespace Schmutzka\Application\UI;

use Nette;
use Nette\Reflection\Property;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Http\Browser;
use Schmutzka\Utils\Name;
use WebLoader;

abstract class Presenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $lang;

	/** @var array */
	public $module;

	/**
	 * @var NetteTranslator\Gettext
	 * @autowire
	 */
	public $translator;

	/** @var Nette\Http\SessionSection */
	protected $baseSession;
	
	/** @var string */
	protected $onLogoutLink;

	/** @var bool */
	protected $useMobileTemplates = FALSE;

	/**
	 * @var Nette\Caching\Cache
	 * @autowire
	 */
	protected $cache;

	/**
	 * @var Schmutzka\Config\ParamService
	 * @autowire
	 */
	protected $paramService;

	/**
	 * @var Schmutzka\Templates\TemplateService
	 * @autowire
	 */
	protected $templateService;

	/** @var array */
	private $autowire = array();

	/* @var Nette\DI\Container */
	private $serviceLocator;


	public function startup()
	{
		parent::startup();

		$this->params += $this->context->parameters;
		$this->module = Name::mpv($this->presenter, "module");

		$sectionKey = substr(sha1($this->params["wwwDir"]), 6);
		$this->baseSession = $this->session->getSection("baseSession_" . $sectionKey);

		$this->user->storage->setNamespace("user_ " . $sectionKey); 

		if ($this->user->loggedIn) {	
			if (isset($this->params["logUserActivity"])) {
				$this->user->logUserActivity($this->params["logUserActivity"]);
			}

		} elseif ($this->isRequestStoreable($this->presenter, $this->signal)) {
			$this->baseSession->requestBacklink = $this->storeRequest();
		}

		$this->module = Name::mpv($this->presenter, "module");
	}


	/* ************************ handlers ************************ */


	/**
	 * Logout
	 */
	public function handleLogout()
	{
		$this->user->logout();
		$this->flashMessage("Byli jste odhlášeni.", "success timeout");
		$this->redirectOnLogout();
	} 


	/**
	 * Redirect after logout
	 */
	protected function redirectOnLogout()
	{
		if ($this->onLogoutLink) {
			$this->redirect($this->onLogoutLink);
		}

		if (Strings::startsWith($this->link(":Front:Homepage:default"), "error")) {
			$this->redirect("Homepage:default");

		} else {
			$this->redirect(":Front:Homepage:default");
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
		$this->templateService->configure($template, $this->lang);
		return $template;
	}


	/**
	 * Find template files
	 */
	public function formatTemplateFiles()
	{
		$templateFiles = parent::formatTemplateFiles();

		if ($this->useMobileTemplates && Browser::isMobile()) {
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
		$layoutTemplateFiles[] = LIBS_DIR . "/Schmutzka/Modules/@layout.latte"; // cms layout

		if ($this->useMobileTemplates && Browser::isMobile()) {
			$layoutTemplateFiles = array_map(function($path) {
					return str_replace("/templates", "/templatesMobile", $path);
			}, $layoutTemplateFiles);
		}

		return $layoutTemplateFiles;
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
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentCss()
	{
		return new WebLoader\Nette\CssLoader($this->context->{"webloader.cssDefaultCompiler"}, $this->template->basePath . "/webtemp/");
	}


	/**
	 * Js component
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentJs()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{"webloader.jsDefaultCompiler"}, $this->template->basePath . "/webtemp/");
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
	 * Translator shortucut
	 */
	public function translate($text)
	{
		if ($this->translator) {
			return $this->translator->translate($text);
		}
	
		return $text;
	}


	/********************** module helpers @todo move to specifi module class **********************/


	/**
	 * Delete helper
	 * @param Models\*
	 * @param int
	 * @param string
	 */
	protected function deleteHelper($model, $id, $redirect = "default")
	{
		if ($model->delete($id)) {
			$this->flashMessage("Záznam byl úspěšně smazán.","flash-success"); 

		} else {
			$this->flashMessage("Tento záznam neexistuje.", "flash-error"); 
		} 

		if ($redirect) {
			$this->redirect($redirect, array("id" => NULL)); 
		}
	}


	/**
	 * Edit item
	 * @param Models\*
	 * @param int
	 * @param string
	 */
	protected function loadItem($model, $id, $redirect = "default")
	{
		if ($item = $model->item($this->id)) {
			$this->template->item = $item;
			return $item;

		} else {
			$this->flashMessage("Tento záznam neexistuje.", "flash-error");
			$this->redirect($redirect, array("id" => NULL));
		}
	}


	/**
	 * Is reuqest storeable
	 * @param string
	 * @param string
	 */
	private function isRequestStoreable($presenter, $signal)
	{
		$mvp = Schmutzka\Utils\Name::mpv($presenter);
		$presenter = $mvp[1];
		$view = $mvp[2];

		if ($presenter == "homepage" || $presenter == "registration" || $view == "login") {
			return FALSE;
		}

		if (is_array($signal) && (in_array("authorize", $signal) || in_array("login", $signal))) {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * Edit item
	 * @param Models\*
	 * @param int
	 * @param string
	 */
	protected function loadEditItem($model, $id, $redirect = "default")
	{
		if ($item = $model->item($this->id)) {
			$this->template->item = $item;

		} else {
			$this->flashMessage("Tento záznam neexistuje.", "error");
			$this->redirect($redirect, array("id" => NULL));
		}
	}


	/********************** autowire properties (by Hosiplan) **********************/


	/**
	 * @param Nette\DI\Container $dic
	 * @throws Nette\InvalidStateException
	 * @throws Nette\MemberAccessException
	 * @throws Nette\DI\MissingServiceException
	 */
	public function injectProperties(Nette\DI\Container $dic)
	{
		if (!$this instanceof Nette\Application\UI\PresenterComponent) {
			throw new Nette\MemberAccessException('Trait ' . __TRAIT__ . ' can be used only in descendants of PresenterComponent.');
		}

		$this->serviceLocator = $dic;
		$cache = new Nette\Caching\Cache($this->serviceLocator->getByType('Nette\Caching\IStorage'), 'Presenter.Autowire');
		if (($this->autowire = $cache->load($presenterClass = get_class($this))) === NULL) {
			$this->autowire = array();

			$rc = ClassType::from($this);
			$ignore = class_parents('Nette\Application\UI\Presenter') + array('ui' => 'Nette\Application\UI\Presenter');
			foreach ($rc->getProperties(Property::IS_PUBLIC | Property::IS_PROTECTED) as $prop) {
				/** @var Property $prop */
				if ((in_array($prop->getDeclaringClass()->getName(), $ignore) || !$prop->hasAnnotation('autowire')) && !($prop->hasAnnotation('var') && Strings::startsWith($prop->getAnnotation('var'), "Schmutzka\\Models\\"))) {
					continue;
					/* schmu lazyness! */
					/*if !($prop->hasAnnotation('var') && Strings::startsWith($prop->getAnnotation('var'), "Models\\")) {
					} else {
						continue;
					}*/
				}

				if (!$type = ltrim($prop->getAnnotation('var'), '\\')) {
					throw new Nette\InvalidStateException("Missing annotation @var with typehint on $prop.");
				}

				if (!class_exists($type) && !interface_exists($type)) {
					if (substr($prop->getAnnotation('var'), 0, 1) === '\\') {
						throw new Nette\InvalidStateException("Class \"$type\" was not found, please check the typehint on $prop");
					}

					if (!class_exists($type = $prop->getDeclaringClass()->getNamespaceName() . '\\' . $type) && !interface_exists($type)) {
						throw new Nette\InvalidStateException("Neither class \"" . $prop->getAnnotation('var') . "\" or \"$type\" was found, please check the typehint on $prop");
					}
				}

				if (empty($this->serviceLocator->classes[strtolower($type)])) {
					throw new Nette\DI\MissingServiceException("Service of type \"$type\" not found for $prop.");
				}

				// unset property to pass control to __set() and __get()
				unset($this->{$prop->getName()});

				$this->autowire[$prop->getName()] = array(
					'value' => NULL,
					'type' => ClassType::from($type)->getName()
				);
			}

			$files = array_map(function ($class) {
				return ClassType::from($class)->getFileName();
			}, array_diff(array_values(class_parents($presenterClass) + array('me' => $presenterClass)), $ignore));

			$cache->save($presenterClass, $this->autowire, array(
				$cache::FILES => $files,
			));

		} else {
			foreach ($this->autowire as $propName => $tmp) {
				unset($this->{$propName});
			}
		}
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		if (!isset($this->autowire[$name])) {
			return parent::__set($name, $value);

		} elseif ($this->autowire[$name]['value']) {
			throw new Nette\MemberAccessException("Property \$$name has already been set.");

		} elseif (!$value instanceof $this->autowire[$name]['type']) {
			throw new Nette\MemberAccessException("Property \$$name must be an instance of " . $this->autowire[$name]['type'] . ".");
		}

		return $this->autowire[$name]['value'] = $value;
	}


	/**
	 * @param $name
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (!isset($this->autowire[$name])) {
			return parent::__get($name);
		}

		if (empty($this->autowire[$name]['value'])) {
			$this->autowire[$name]['value'] = $this->serviceLocator->getByType($this->autowire[$name]['type']);
		}

		return $this->autowire[$name]['value'];
	}




}