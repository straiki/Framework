<?php

namespace Schmutzka\Forms;

use Nette\Reflection\ClassType;
use Schmutzka;
use Nette;

class FormControl extends Schmutzka\Application\UI\Control
{

	/**
	 * @see http://forum.nette.org/cs/11227-formulare-baseform-a-jeho-limity#p81436
	 */
	public function createTemplate($class = null, $autosetfile = TRUE)
	{
		$template = parent::createTemplate($class, $autosetfile);
		$template->_form = $template->form = $this["form"];
		return $template;
	}


	public function render()
	{
		if ($this->template instanceof Nette\Templating\FileTemplate && !is_file($this->template->getFile())) {
			$args = func_get_args();
			return call_user_func_array(array($this["form"], "render"), $args);

		} else {
			$this->template->render();
		}
	}

}