<?php

namespace Schmutzka\Components;

use Schmutzka;


class TwitterModalControl extends Schmutzka\Application\UI\Control
{

	/**
	 * Use this control template
	 * @return Nette\Templating\FileTemlate
	 */
	public function useParentTemplate()
	{
		return $this->createTemplate()->setFile(__DIR__ . '/templates/default.latte');
	}

}
