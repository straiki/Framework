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

	/** @inject @var Nette\localization\ITranslator */
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
		}

		if ($this->isRequestStoreable($this->presenter, $this->signal)) {
			$this->baseSession->requestBacklink = $this->storeRequest();
		}
	}


	/**
	 * Logout
	 */
	public function handleLogout()
	{
		$this->user->logout();
		$this->flashMessage($this->paramService->flashes->onLogout, "success timeout");
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


	/********************** templates **********************/


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
	 * Format template layout
	 */
	public function formatLayoutTemplateFiles()
	{
		$layoutTemplateFiles = parent::formatLayoutTemplateFiles();
		$layoutTemplateFiles[] = APP_DIR . "/AdminModule/templates/@layout.latte";
		$layoutTemplateFiles[] = LIBS_DIR . "/Schmutzka/Modules/@" . ($this->layout ?: "layout") . ".latte";
		$layoutTemplateFiles[] = APP_DIR . "/FrontModule/templates/@" . ($this->layout ?: "layout") . ".latte";

		return $layoutTemplateFiles;
	}


	/********************** components **********************/


	/**
	 * Handles requests to create component
	 * @param string
	 * @return Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$component = parent::createComponent($name);

		if ($component === NULL) {
			if (method_exists($this->context, ($create = "create" .  ucfirst($name)))) {
				$component = call_user_func(array($this->context, $create));

			} elseif (method_exists($this->context, ($createControl = $create . "Control"))) {
				$component = call_user_func(array($this->context, $createControl));

			} elseif (method_exists($this->context, ($createForm = $create . "Form"))) {
				$component = call_user_func(array($this->context, $createForm));
			}
		}

		return $component;
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


	/********************** module helpers **********************/


	/**
	 * Delete helper
	 * @param Schmutzka\Models\*
	 * @param int
	 * @param string
	 */
	protected function deleteHelper($model, $id, $redirect = "default")
	{
		if ($model->delete($id)) {
			$this->flashMessage($this->paramService->flashes->onDeleteSuccess, "success");

		} else {
			$this->flashMessage($this->paramService->flashes->onDeleteError, "error");
		}

		if ($redirect) {
			$this->redirect($redirect, array("id" => NULL));
		}
	}


	/**
	 * Load item helper
	 * @param Schmutzka\Models\*
	 * @param int
	 * @param string
	 */
	protected function loadItemHelper($model, $id, $redirect = "default")
	{
		if ($item = $model->item($this->id)) {
			$this->template->item = $item;
			return $item;

		} else {
			$this->flashMessage("Tento záznam neexistuje.", "error");
			$this->redirect($redirect, array("id" => NULL));
		}
	}


	/********************** helpers **********************/


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

}
