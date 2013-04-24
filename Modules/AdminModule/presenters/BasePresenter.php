<?php

namespace AdminModule;

use Schmutzka;
use Schmutzka\Utils\Name;
use WebLoader;

abstract class BasePresenter extends Schmutzka\Application\UI\AdminPresenter
{
	/** @var array */
	public $moduleParams;


	public function startup() 
	{ 
		parent::startup(); 

		$this->lang = NULL;
	
		if ($this->paramService->cms == TRUE) {
			$this->template->adminTitle = $this->paramService->cmsSetup->title;
			$this->template->activeModules = $activeModules = $this->paramService->getActiveModules();
			$this->template->cmsParams = $this->paramService->cmsSetup;

			if (isset($this->params->cmsSetup->modules->{$this->module})) {
				$this->template->moduleParams = $this->moduleParams = $this->params->cmsSetup->modules->{$this->module};
			}

			// layout setup
			$layoutSetup = array();
			if (isset($activeModules["page"])) {
				 $layoutSetup["pageCount"] = $this->pageModel->count();
			}

			if (isset($activeModules["article"])) {
				 $layoutSetup["articleCount"] = $this->articleModel->count();
			}

			if (isset($activeModules["user"])) {
				 $layoutSetup["userCount"] = $this->userModel->count();
			}

			$this->template->layoutSetup = $layoutSetup;
		}

		if (!$this->user->loggedIn) {
			$this->layout = "layoutLogin";
		}
	}


	/**
	 * Login form
	 */
	protected function createComponentLoginForm()
	{
		return $this->context->createLoginForm();
	}


	/**
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentAdminLoginCss()
	{
		return new WebLoader\Nette\CssLoader($this->context->{"webloader.cssAdminLoginCompiler"}, $this->template->basePath . "/webtemp/");
	}


	/**
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentAdminLoginJs()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{"webloader.jsAdminLoginCompiler"}, $this->template->basePath . "/webtemp/");
	}

}
