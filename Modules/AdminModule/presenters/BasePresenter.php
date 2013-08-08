<?php

namespace AdminModule;

use Schmutzka\Application\UI\AdminPresenter;
use WebLoader;


abstract class BasePresenter extends AdminPresenter
{
	/** @var array */
	public $moduleParams;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @var Schmutzka\Models\Page */
	private $pageModel;

	/** @var Schmutzka\Models\Gallery */
	private $galleryModel;


	public function injectModels(Schmutzka\Models\Page $pageModel = NULL,Schmutzka\Models\Gallery $galleryModel = NULL)
	{
		$this->pageModel = $pageModel;
		$this->galleryModel = $galleryModel;
	}


	public function startup()
	{
		parent::startup();

		$this->lang = NULL;

		if ( ! $this->user->loggedIn) {
			$this->layout = 'layoutLogin';
		}

		$this->template->modules = $this->paramService->getActiveModules();
	}


	/**
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentAdminLoginCssControl()
	{
		return new WebLoader\Nette\CssLoader($this->context->{'webloader.cssAdminLoginCompiler'}, $this->template->basePath . '/webtemp/');
	}


	/**
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentAdminLoginJsControl()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{'webloader.jsAdminLoginCompiler'}, $this->template->basePath . '/webtemp/');
	}

}
