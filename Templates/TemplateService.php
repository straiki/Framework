<?php

namespace Schmutzka\Templates;

use Nette;


class TemplateService extends Nette\Object
{
	/** @inject @var Schmutzka\Templates\Helpers */
	public $helpers;

	/** @var Nette\Latte\Engine */
	private $latte;

	/** @var Nette\Localization\ITranslator */
	private $translator;


	public function injectTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}


	/**
	 * @param Nette\Templating\FileTemplate
	 * @param string
	 * @return Nette\Templating\FileTemplate
	 */
	public function configure(Nette\Templating\FileTemplate $template, $lang = NULL)
	{
		if ($this->translator && $lang) {
			$this->translator->setLang($lang);
			$template->setTranslator($this->translator);

		} else {
			$template->registerHelper('translate', function ($s) {
				return $s;
			});
		}

		$template->registerFilter(new Nette\Templating\Filters\Haml);
		$template->registerFilter($this->latte);
		$template->registerHelperLoader(array($this->helpers, 'loader'));

		return $template;
	}


	/**
	 * Set latte engine
	 * @param Nette\Latte\Engine
	 * @return  this
	 */
	public function setLatte(Nette\Latte\Engine $latte)
	{
		$this->latte = $latte;
		return $this;
	}

}
