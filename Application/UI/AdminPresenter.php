<?php

/**
 * Presenter with base functions in Admin module
 */

namespace Schmutzka\Application\UI;

use Nette\Security\User;

class AdminPresenter extends \BasePresenter // if exists
{

	/** @var redirect unlogged */
	public $unloggedRedirect = ":Front:Homepage:default";

	public function startup()
	{
		parent::startup();

        if(!$this->user->isLoggedIn()) {
            $this->redirect($this->unloggedRedirect);
        }
		elseif(!$this->user->isAllowed($this->name, $this->action)) {
			$this->flashMessage("Na vstup do této sekce nemáte dostatečné oprávnění!", "warning");
			$this->redirect($this->unloggedRedirect);
        }
	
	}

}