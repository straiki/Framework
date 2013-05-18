<?php

namespace Schmutzka\Components;

use Schmutzka;
use Schmutzka\Utils\Name;

class TwitterModalControl extends Schmutzka\Application\UI\Control
{

	/**
	 * Use this control template
	 * @return Nette\Templating\FileTemlate
	 */
	public function useParentTemplate()
	{
		return $this->createTemplate()->setFile(__DIR__ . "/TwitterModalControl.latte");
	}

}
