<?php

namespace Schmutzka\Templates;

use Schmutzka\Templates\MyHelpers,
	Schmutzka\Templates\MyMacros,
	Nette\Templating\Filters\Haml,
	Nette\Latte;

class TemplateFactory extends \Nette\Object
{
	
	/** @var \SystemContainer */
	private $context;


	public function __construct(\SystemContainer $context)
	{
		$this->context= $context;
	}	


	public function configure(\Nette\Templating\FileTemplate $template)
	{
		$latte = new Latte\Engine;

		// translator
		if ($this->context->hasService("translator")) {
			if (isset($this->context->params["lang"])) {
				$this->context->translator->setLang($this->context->params["lang"]); 
			}

			$template->setTranslator($this->context->translator);
		}

		// macros
		MyMacros::install($latte->compiler);

		// filters
		$template->registerFilter(new Haml);
		$template->registerFilter($latte);

		// helpers
		$helpers = new MyHelpers($this->context);
		$template->registerHelperLoader(array($helpers, "loader"));
	}

}