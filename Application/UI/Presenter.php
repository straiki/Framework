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
	/** @persistent @var string */
	public $lang;

	/** @persistent @var string */
	public $backlink;

	/** @var string */
	public $module;

	/** @inject @var Nette\localization\ITranslator */
	public $translator;

	/** @inject @var Nette\Caching\Cache */
	public $cache;

	/** @inject @var Schmutzka\ParamService */
	public $paramService;

	/** @inject @var Schmutzka\Templates\TemplateService */
	public $templateService;

	/** @var array|callable[] */
	protected $helpersCallbacks = array();


	public function startup()
	{
		parent::startup();

		$this->module = Name::mpv($this->presenter, "module");

		if ($this->user->loggedIn && $this->paramService->logUserActivity) {
			$this->user->logLastActive();
		}
	}


	public function handleLogout()
	{
		$this->user->logout();
		if ($this->paramService->flashes->onLogout) {
			$this->flashMessage($this->paramService->flashes->onLogout, "success timeout");
		}

		if ($this->module) {
			$this->redirect(":Front:Homepage:default");

		} else {
			$this->redirect("Homepage:default");
		}
	}


	/********************** templates **********************/


	/**
	 * @param string
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$this->templateService->configure($template, $this->lang);

		foreach ($this->helpersCallbacks as $helpersCallback) {
			$template->registerHelperLoader($helpersCallback);
		}

		return $template;
	}


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
			$component = call_user_func(array($this->context, "createService" .  ucfirst($name)));
		}

		return $component;
	}


	/**
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentCssControl()
	{
		return new WebLoader\Nette\CssLoader($this->context->{"webloader.cssDefaultCompiler"}, $this->template->basePath . "/webtemp/");
	}


	/**
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentJsControl()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{"webloader.jsDefaultCompiler"}, $this->template->basePath . "/webtemp/");
	}


	/********************** module helpers **********************/


	/**
	 * @param Schmutzka\Models\Base
	 * @param int
	 * @param string
	 */
	protected function deleteHelper($model, $id, $redirect = "default")
	{
		if (!$id) {
			return FALSE;
		}

		if ($model->delete($id)) {
			$this->flashMessage($this->paramService->flashes->onDeleteSuccess, "success");

		} else {
			$this->flashMessage($this->paramService->flashes->onDeleteError, "error");
		}

		if ($redirect) {
			$this->redirect($redirect, array(
				"id" => NULL
			));
		}
	}


	/**
	 * @param Schmutzka\Models\Base
	 * @param int
	 * @param string
	 */
	protected function loadItemHelper($model, $id, $redirect = "default")
	{
		if (!$id) {
			return FALSE;
		}

		if ($item = $model->item($id)) {
			$this->template->item = $item;
			return $item;

		} else {
			$this->flashMessage("Tento záznam neexistuje.", "error");
			$this->redirect($redirect, array(
				"id" => NULL
			));
		}
	}

}
