<?php

namespace Schmutzka\Templates;

use Schmutzka\Templates\MyHelpers,
	Schmutzka\Templates\MyMacros,
	Nette\Templating\Filters\Haml,
	Nette\Latte,
	Nette,
	Schmutzka\Utils;

class TemplateService extends \Nette\Object
{

	/** @var \Nette\DI\Container */
	private $context;


	public function __construct(\Nette\DI\Container $context)
	{
		$this->context = $context;
	}	


	/**
	 * Configure template
	 * @param Nette\Templating\FileTemplate 
	 * @param string
	 */
	public function configure(Nette\Templating\FileTemplate $template, $lang = NULL)
	{
		$latte = new Latte\Engine;

		if ($this->context->hasService("translator")) {
			if ($lang) {
				$this->context->translator->setLang($lang); 
			}
			$template->setTranslator($this->context->translator);

		} else {
			$template->registerHelper("translate", array($this, "translate"));
		}

		MyMacros::install($latte->compiler);

		$template->registerFilter(new Haml);
		$template->registerFilter($latte);

		$helpers = new MyHelpers($this->context);
		$template->registerHelperLoader(array($helpers, "loader"));

		return $template;
	}


	/**	
	 * Inactive translator helper
	 * @param string
	 */
	public function translate($s)
	{
		return $s;
	}

}