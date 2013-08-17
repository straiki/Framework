<?php

namespace AdminModule;

use Schmutzka\Application\UI\Presenter;
use WebLoader;


abstract class BasePresenter extends Presenter
{
	/** @var array */
	public $moduleParams;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;

	/** @var string */
	protected $unloggedRedirect = 'Homepage:default';

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

		$currentSite = (ltrim($this->name . ':' . $this->view, 'Admin:'));
		if (! $this->user->isLoggedIn()) {
			if ($this->unloggedRedirect != $currentSite) {
				$this->flashMessage('Pro přístup do této sekce se musíte přihlásit.', 'info');
				$this->redirect(':Admin:Homepage:default');
			}

		} elseif ($this->context->hasService('Nette\Security\IAuthorizator') && ! $this->user->isAllowed($this->name, $this->action)) {
			$this->flashMessage('Na vstup do této sekce nemáte dostatečné oprávnění.', 'warning');
			$this->redirect(':Front:Homepage:default');
		}

		$this->template->modules = $this->paramService->getActiveModules();
	}


	/**
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentAdminCss()
	{
		return new WebLoader\Nette\CssLoader($this->context->{'webloader.cssAdminCompiler'}, $this->template->basePath . '/webtemp/');
	}


	/**
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentAdminJs()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{'webloader.jsAdminCompiler'}, $this->template->basePath . '/webtemp/');
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
