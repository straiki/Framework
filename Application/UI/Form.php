<?php

namespace Schmutzka\Application\UI;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
use Nette\Utils\Validators;
use Schmutzka\Forms\Controls;


/**
 * @method setProcessor(callable)
 */
class Form extends Nette\Application\UI\Form
{
	/** validators */
	const RC = 'Schmutzka\Forms\Rules::validateRC';
	const IC = 'Schmutzka\Forms\Rules::validateIC';
	const PHONE = 'Schmutzka\Forms\Rules::validatePhone';
	const ZIP = 'Schmutzka\Forms\Rules::validateZip';
	const DATE = 'Schmutzka\Forms\Rules::validateDate';
	const TIME = 'Schmutzka\Forms\Rules::validateTime';
	const EXTENSION = 'Schmutzka\Forms\Rules::extension';

	/** @var string */
	public $csrfProtection = 'Prosím odešlete formulář znovu, vypršel bezpečnostní token.';

	/** @var bool */
	public $useBootstrap = TRUE;

	/** @inject @var Nette\Localization\ITranslator */
	public $translator;

	/** @var callable */
	protected $processor;

	/** @var bool */
	private $isBuilt = FALSE;

	/** @var string */
	private $id;

	/** @var string */
	private $target;


	/**
	 * BeforeRender build function
	 */
	public function build()
	{
		$this->isBuilt = TRUE;

		if ($this->csrfProtection) {
			$this->addProtection($this->csrfProtection);
		}

		if ($this->id) {
			$this->setId($this->id);
		}

		if ($this->target) {
			$this->setTarget($this->target);
		}
	}


	/**
	 * Changes position of control
	 * @param string
	 * @param string
	 */
	public function moveBefore($name, $where)
	{
		if (!$this->isBuilt) {
			$this->build();
		}

		$component = $this->getComponent($name);
		$this->removeComponent($component);
		$this->addComponent($component, $name, $where);
	}


	/**
	 * Set defaults accepts array, object or empty string
	 * @param array|object
	 * @param bool
	 * @return  this
	 */
	public function setDefaults($defaults, $erase = FALSE)
	{
		$defaults = is_object($defaults) ? get_object_vars($defaults) : $defaults;
		parent::setDefaults($defaults, $erase);

		return $this;
	}


	/**
	 * Flash message error
	 * @param string
	 */
	public function addError($message)
	{
		$this->valid = FALSE;
		$this->presenter->flashMessage($message, 'error');
	}


	/**
	 * @param string
	 * @param string|NULL
	 */
	public function addToggleGroup($id, $label = NULL)
	{
		$fieldset = Html::el('fieldset')->id($id)
			->style('display:none');

		$this->addGroup($label)
			->setOption('container', $fieldset);
	}


	/**
	 * Is called when the component becomes attached to a monitored object
	 * @param Nette\Application\IComponent
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		if (!$this->isBuilt) {
			$this->build();
		}

		if (method_exists($this, 'afterBuild')) {
			$this->afterBuild();
		}

		if ($this->translator) {
			$this->setTranslator($this->translator);
		}

		if ($presenter instanceof Nette\Application\IPresenter) {
			$this->attachHandlers($presenter);
		}

		if ($presenter->module != 'front' && $this->useBootstrap) {
			$this->setRenderer(new BootstrapRenderer($presenter->template));
		}
	}


	/**
	 * Automatically attach methods
	 * @param Nette\Application\UI\Presenter
	 */
	protected function attachHandlers($presenter)
	{
		$formNameSent = 'process' . lcfirst($this->getName());

		$possibleMethods = array(
			array($presenter, $formNameSent),
			array($this->parent, $formNameSent),
			array($this, 'process'),
			array($this->parent, 'process')
		);

		foreach ($possibleMethods as $method) {
			if (method_exists($method[0], $method[1])) {
				$this->onSuccess[] = callback($method[0], $method[1]);
			}
		}
	}


	/**
	 * @param  bool
	 * @return  array|ArrayHash
	 */
	public function getValues($asArray = TRUE)
	{
		$values = parent::getValues($asArray);
		if ($this->processor && is_callable($this->processor)) {
			$values = call_user_func($this->processor, $values);

		} elseif (method_exists($this->parent, lcfirst($this->getName()) . 'Processor') && is_callable($this->processor)) {
			$values = call_user_func($this->processor, $values);
		}

		return $values;
	}


	/**
	 * @return string
	 */
	public function getSubmitName()
	{
		return $this->isSubmitted()->name;
	}


	/**
	 * @param string
	 * @return this
	 */
	public function setId($name)
	{
		$this->elementPrototype->id = $name;
		return $this;
	}


	/**
	 * @param string
	 * @return this
	 */
	public function setTarget($name)
	{
		$this->elementPrototype->target = $name;
		return $this;
	}


	/* ****************************** controls ****************************** */


	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return Controls\AntispamControl
	 */
	public function addAntispam($name = 'antispam', $label = 'Toto pole vymažte.', $msg = 'Byl detekován pokus o spam')
	{
		return $this[$name] = new Controls\AntispamControl($label, NULL, NULL, $msg);
	}


	/**
	 * @param string
	 * @param string|NULL
	 * @param array
	 * @return  Controls\SuggestControl
	 */
	public function addSuggest($name, $label = NULL, $suggestList)
	{
		return $this[$name] = new Controls\SuggestControl($label, $suggestList);
	}


	/**
	 * @param  string
	 * @param  string|NULL
	 * @param  int|NULL
	 * @return Controls\DatePicker
	 */
	public function addDatePicker($name, $label = NULL, $cols = NULL)
	{
		return $this[$name] = new Controls\DatePicker($label, $cols, NULL);
	}


	/**
	 * @param string
	 * @param string|NULL
	 * @return Controls\DateTimePicker
	 */
	public function addDateTimePicker($name, $label = NULL)
	{
		return $this[$name] = new Controls\DateTimePicker($label);
	}


	/**
	 * @param  string
	 * @param  string|NULL
	 * @return  TextInput
	 */
	public function addUrl($name, $label = NULL)
	{
		$control = $this[$name] = new TextInput($label);
		$control->addFilter(function ($value) {
				return Validators::isUrl($value) ? $value : 'http://$value';
			})
			->addCondition(Form::FILLED)
			->addCondition(~Form::EQUAL, 'http://')
				->addRule(Form::URL, 'Opravte adresu odkazu');

		return $control;
	}

}
