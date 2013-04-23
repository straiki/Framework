<?php

namespace Schmutzka\Application\UI;

use Nette;

class BaseFormControl extends Nette\Application\UI\Control
{

    protected function template() {}


    public function render()
    {
        $args = func_get_args();

		$class = $this->getReflection();
		$path = dirname($class->getFileName()) . "/" . $class->getShortName() . ".latte";
        if (!is_file($path)) {
            return call_user_func_array(array($this['form'], 'render'), $args);
        }
        $template = $this->template;
        $template->_form = $template->form = $this['form']; // allows snippets anywhere!
        $template->setFile($path); // automatické nastavení šablony
        array_unshift($args, $template);
        call_user_func_array(array($this, 'template'), $args); // inherit and override 'template' to configure it
        $template->render();
    }

}
