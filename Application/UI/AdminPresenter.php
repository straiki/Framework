<?php

namespace Schmutzka\Application\UI;

class AdminPresenter extends \BasePresenter
{

	/** @var redirect unlogged */
	public $unloggedRedirect = ":Front:Homepage:default";


	public function startup()
	{
		parent::startup();

		$currentSite = (ltrim($this->name . ":" . $this->view, "Admin:"));
        if (!$this->user->isLoggedIn()) {
			if ($this->unloggedRedirect != $currentSite) {
				$this->flashMessage("Pro přístup do této sekce se musíte přihlásit.", "flash-info");
				$this->redirect($this->unloggedRedirect);
			}
        }
		elseif (!$this->user->isAllowed($this->name, $this->action)) {
			$this->flashMessage("Na vstup do této sekce nemáte dostatečné oprávnění!", "flash-warning");
			$this->redirect($this->unloggedRedirect);
        }
	}

}