<?php

namespace Schmutzka\Application\UI;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;
use Schmutzka\Http\Browser;
use Schmutzka\Utils\Name;
use WebLoader;

abstract class Presenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $lang;

	/** @var string */
	public $module;

	/** @inject @var NetteTranslator\Gettext */
	public $translator;

	/** @inject @var Nette\Caching\Cache */
	public $cache;

	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Templates\TemplateService */
	public $templateService;

	/** @var Nette\Http\SessionSection */
	protected $baseSession;
	
	/** @var string */
	protected $onLogoutLink;

	/** @var bool */
	protected $useMobileTemplates = FALSE;



	public function startup()
	{
		parent::startup();

		$this->module = Name::mpv($this->presenter, "module");

		$sectionKey = substr(sha1($this->paramService->wwwDir), 6);
		$this->baseSession = $this->session->getSection("baseSession_" . $sectionKey);
		$this->user->storage->setNamespace("user_ " . $sectionKey); 

		if ($this->user->loggedIn) {
			if (isset($this->paramService->logUserActivity)) {
				$this->user->logUserActivity($this->paramService->logUserActivity);
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
	 * @return Components\TitleControl
	 */
	protected function createComponentTitle()
	{
		return $this->context->createTitleControl();
	}


	/**
	 * @return Components\FlashMessage
	 */
	protected function createComponentFlashMessage()
	{
		return $this->context->createFlashMessageControl();
	}


	/**
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentCss()
	{
		return new WebLoader\Nette\CssLoader($this->context->{"webloader.cssDefaultCompiler"}, $this->template->basePath . "/webtemp/");
	}


	/**
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentJs()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{"webloader.jsDefaultCompiler"}, $this->template->basePath . "/webtemp/");
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



}