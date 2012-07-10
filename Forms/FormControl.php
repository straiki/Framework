<?php

namespace Schmutzka\Forms;

// use Nette\Utils\PhpGenerator\ClassType;

class FormControl extends \Schmutzka\Application\UI\Control
{

	/**
	 * @see http://forum.nette.org/cs/11227-formulare-baseform-a-jeho-limity#p81436
	 */
	public function createTemplate($class = null)
	{
			$template = parent::createTemplate($class);
			if ($template instanceof \Nette\Templating\FileTemplate) {
					// $path = __DIR__ . "/" . ClassType::from($this)->getShortName() . ".latte";
					$path = dirname(ClassType::from($this)->getFileName()) . '/' . ClassType::from($this)->getShortName() . '.latte';
					$template->setFile($path); // automatické nastavení šablony
			}
			$template->_form = $template->form = $this["form"]; // kvůli snippetům
			return $template;
	}


	public function render()
	{
			if ($this->template instanceof \Nette\Templating\FileTemplate
					&& !is_file($this->template->getFile())) {

					$args = func_get_args();
					return call_user_func_array(array($this["form"], "render"), $args);
			} else {
					$this->template->render();
			}
	}

}