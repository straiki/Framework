<?php

namespace Schmutzka\Application\UI;

use WebLoader;
use FrontModule;

class AdminPresenter extends FrontModule\BasePresenter
{
	/** @var string */
	protected $unloggedRedirect = "Homepage:default";


	public function startup()
	{
		parent::startup();

		$currentSite = (ltrim($this->name . ":" . $this->view, "Admin:"));
		if (! $this->user->isLoggedIn()) {
			if ($this->unloggedRedirect != $currentSite) {
				$this->flashMessage("Pro přístup do této sekce se musíte přihlásit.", "info");
				$this->redirect(":Admin:Homepage:default");
			}

		} elseif ($this->context->hasService("Nette\Security\IAuthorizator") && ! $this->user->isAllowed($this->name, $this->action)) {
			$this->flashMessage("Na vstup do této sekce nemáte dostatečné oprávnění.", "warning");
			$this->redirect(":Front:Homepage:default");
		}
	}


	/**
	 * @return WebLoader\Nette\CssLoader
	 */
	protected function createComponentAdminCss()
	{
		return new WebLoader\Nette\CssLoader($this->context->{"webloader.cssAdminCompiler"}, $this->template->basePath . "/webtemp/");
	}


	/**
	 * @return WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentAdminJs()
	{
		return new WebLoader\Nette\JavaScriptLoader($this->context->{"webloader.jsAdminCompiler"}, $this->template->basePath . "/webtemp/");
	}

}
