<?php

namespace Schmutzka\Application\UI;

use Nette;
use Nette\Utils\Strings;
use Schmutzka;


abstract class Control extends Nette\Application\UI\Control
{
	/** @inject @var Schmutzka\Templates\TemplateService */
	public $templateService;

	/** @var Nette\Localization\ITranslator */
	protected $translator;


	public function injectTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}


	/**
	 * Rendering view
	 * @param  string
	 * @param  array
	 */
	public function __call($name, $args)
	{
		if (Strings::startsWith($name, 'render')) {
			if ($name == 'render') {
				$view = 'default';

			} else {
				$view = lcfirst(substr($name, 6));
			}

			// setup template file
			$class = $this->getReflection();
			$dir = dirname($class->getFileName());
			$this->template->setFile($dir . '/templates/' . $view . '.latte');

			// calls $this->render<View>()
			$renderMethod = 'render' . ucfirst($view);
			if (method_exists($this, $renderMethod)) {
				call_user_func_array(array($this, $renderMethod), $args);
			}

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

		foreach ($this->presenter->helpersCallbacks as $helpersCallback) {
			$template->registerHelperLoader($helpersCallback);
		}

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

}
