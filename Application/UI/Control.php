<?php

namespace Schmutzka\Application\UI;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;

abstract class Control extends Nette\Application\UI\Control
{
	/** @inject @var Nette\Localization\ITranslator */
	public $translator;

	/** @inject @var Schmutzka\Templates\TemplateService */
	public $templateService;


	/**
	 * Rendering view
	 * @param  string
	 * @param  array
	 * @todo simulate as presenter render!
	 */
	public function __call($name, $args)
	{
		if (Strings::startsWith($name, 'render')) {
			$view = $this->getViewFromMethod($name);

			// setup template file
			$class = $this->getReflection();
			$dir = dirname($class->getFileName());
			$this->template->setFile($dir . '/templates/' . $view . '.latte');

			// calls $this->render<View>()
			$this->tryCall($this->formatRenderMethod($view), $args);

			$this->template->render();
		}
	}


	/**
	 * Create template and set file
	 * @param string
	 * @param bool
	 * @return Nette\Templating\FileTemplate
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$this->templateService->configure($template);

		return $template;
	}


	/**
	 * @param  string
	 * @return Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$component = parent::createComponent($name);
		if ($component == NULL) {
			$component = $this->presenter->createComponent($name);
		}

		return $component;
	}


	/********************** render helpers **********************/


	/**
	 * @param  string
	 * @return string
	 */
	private function getViewFromMethod($method)
	{
		if (strlen($method) == 6) {
			return 'default';

		} else {
			return lcfirst(substr($method, 6));
		}
	}


	/**
	 * Formats render view method name.
	 * @param  string
	 * @return string
	 */
	private function formatRenderMethod($view)
	{
		return 'render' . $view;
	}

}
