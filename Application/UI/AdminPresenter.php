<?php

namespace Schmutzka\Application\UI;

class AdminPresenter extends \FrontModule\BasePresenter
{
	/** @var string */
	protected $unloggedRedirect = ":Admin:Homepage:default";

	/** @var bool */
	protected $useAcl = FALSE;


	public function startup()
	{
		parent::startup();

		$currentSite = ":" . $this->name . ":" . $this->view;
        if (!$this->user->isLoggedIn()) {
			if ($this->unloggedRedirect != $currentSite) {
				$this->flashMessage("Pro přístup do této sekce se musíte přihlásit.", "flash-info");
				$this->redirect($this->unloggedRedirect);
			}

        } elseif ($this->useAcl && !$this->user->isAllowed($this->name, $this->action)) {
			$this->flashMessage("Na vstup do této sekce nemáte dostatečné oprávnění!", "flash-warning");
			$this->redirect($this->unloggedRedirect);
        }
	}

}