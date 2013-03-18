<?php

namespace Schmutzka\Templates;

use Nette;
use Schmutzka;
use NetteTranslator;

class TemplateService extends Nette\Object
{
	/** @var Schmutzka\Templates\Helpers */
	private $helpers;

	/** @var NetteTranslator\Gettext */
	private $translator;


	/**
	 * @param Schmutzka\Templates\Helpers
	 * @param NetteTranslator\Gettext
	 */
	public function __construct(Schmutzka\Templates\Helpers $helpers, NetteTranslator\Gettext $translator = NULL)
	{
		$this->helpers = $helpers;
		$this->translator = $translator;
	}	


	/**
	 * Configure template
	 * @param Nette\Templating\FileTemplate 
	 * @param string
	 */
	public function configure(Nette\Templating\FileTemplate $template, $lang = NULL)
	{
		if ($this->translator && $lang) {
			$this->translator->setLang($lang); 
			$template->setTranslator($this->translator);

		} else {
			$template->registerHelper("translate", function ($s) { 
				return $s;
			});
		}

		$template->registerFilter(new Nette\Templating\Filters\Haml);
		$template->registerFilter(new Nette\Latte\Engine);

		$template->registerHelperLoader(array($this->helpers, "loader"));

		return $template;
	}

}
