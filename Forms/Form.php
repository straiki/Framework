<?php

namespace Schmutzka\Forms;

use Schmutzka\Forms\Controls,
	Nette\Utils\Html,
	DependentSelectBox\JsonDependentSelectBox;

class Form extends \Nette\Application\UI\Form
{
	/** @var array */
	private $typeClass = array(
		"send" => "btn btn-primary",
		"submit" => "btn btn-primary",
		"cancel" => "btn",
		"return" => "btn",
		"reset" => "btn",
		"test" => "btn",
		"remove" => "btn btn-danger",
		"delete" => "btn btn-danger",
		"next" => "btn btn-success",
	);

	/** @var bool */
	private $isBuilt = FALSE;


	/** @var string */
	public $csrfProtection = "Prosím odešlete formulář znovu, vypršel bezpečnostní token.";

	/** @var string */
	public $id = "";

	/** @var string */
	public $target = "";


	/** @var \Translator */
	protected $translator = NULL;


	/**
	 * beforeRender build function
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
	 * Set defaults accepts NULL or empty string
	 * @param mixed
	 */
	public function setDefaults($defaults, $erase = FALSE)
	{
		if (is_array($defaults)) {
			parent::setDefaults($defaults, $erase);
		}
		return $this;
	}


	/**
	 * Flash message error
	 */
	public function addError($message)
	{
		$this->valid = FALSE;

		if ($message !== NULL) {
			$messagePresent = FALSE;
			foreach ($this->parent->template->flashes as $value) {
				if ($message == $value->message) {
					$messagePresent = TRUE;
				}
			}

			if (!$messagePresent) {
				$this->flashMessage($message,"flash-error");
			}
		}
	}


	/**
	 * Will be called when the component becomes attached to a monitored object
	 * @param Nette\Application\IComponent
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		if (!$this->isBuilt) {
			$this->build();
		}

		if ($this->getContext()->hasService("translator")) { // automatic translator
			$this->translator = $this->getContext()->translator;
			$this->setTranslator($this->translator);
		}

		if ($presenter instanceof \Nette\Application\IPresenter) {
			$this->attachHandlers($presenter);
		}
	}


	/**
	 * Automatically attach methods
	 * @param \Nette\Application\UI\Presenter
	 */
	protected function attachHandlers($presenter)
	{
		$formNameSent = lcfirst($this->getName())."Sent";

		$possibleMethods = array(
			array($presenter, $formNameSent),
			array($this->parent, $formNameSent),
			array($this, "process"),
			array($this->parent, "process")
		);

		foreach ($possibleMethods as $method) {
			if (method_exists($method[0], $method[1])) {
				$this->onSuccess[] = callback($method[0], $method[1]);
			}
		}
	}


	/**
	 * Returns values as array
	 * @param bool
	 */
	public function getValues($removeEmpty = FALSE)
	{
		$values = parent::getValues(TRUE);
		

		foreach ($this->httpData as $key => $value) {
			if (empty($values[$key]) AND $value AND !isset($this->typeClass[rtrim($key,"_")]) AND $key != "_token_") {
				$values[$key] = $value;
			}
		}

		foreach ($this->typeClass as $key => $value) { 
			unset($values[$key]);
		}


		foreach ($values as $key => $value) { 
			if (is_object($value) AND (get_class($value) == "Nette\DateTime" OR get_class($value) == "DateTime")) { // object to date
				$values[$key] = $value->format("Y-m-d");
			}
		}

		if ($removeEmpty) { 
			$values = array_filter($values); 
		}

		return $values;
	}


	/**
	 * Set id for the form
	 * @param string
	 */
	public function setId($name)
	{
		$this->elementPrototype->id = $name;
		return $this;
	}


	/**
	 * Set target for the form
	 * @param string
	 */
	public function setTarget($name)
	{
		$this->elementPrototype->target = $name;
		return $this;
	}


	/* ****************************** improved inputs ****************************** */


	/**
	 * Adds email input
	 */
	public function addEmail($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		$item = $this->addText($name, $label, $cols, $maxLength);
		$item->setAttribute('type', "email")->addCondition(self::FILLED)->addRule(self::EMAIL);
		return $item;
	}


	/**
	 * Adds url input
	 */
	public function addUrl($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		$item = $this->addText($name, $label, $cols, $maxLength);
		$item->setAttribute('type', "url")->addCondition(self::FILLED)->addRule(self::URL);
		return $item;
	}


	/**
	 * Adds a number input control to the form.
	 */
	public function addNumber($name, $label = NULL, $step = 1, $min = 0, $max = NULL)
	{
		$item = $this->addText($name, $label);
		$item->setAttribute('step', $step)->setAttribute('type', "number")
			->addCondition(self::FILLED)->addRule(self::NUMERIC);
		$range = array(NULL, NULL);
		if ($min !== NULL) {
			$item->setAttribute('min', $min);
			$range[0] = $min;
		}
		if ($max !== NULL) {
			$item->setAttribute('max', $max);
			$range[1] = $max;
		}
		if ($range != array(NULL, NULL)) {
			$item->addCondition(self::FILLED)->addRule(self::RANGE, NULL, $range);
		}

		return $item;
	}


	/**
	 * Adds a radio list
	 */
	public function addRadioList($name, $label = NULL, array $items = NULL, $sep = NULL)
	{
		$item = parent::addRadioList($name, $label, $items);

		$sep = trim($sep, "<>");
		$item->getSeparatorPrototype()->setName($sep);

		return $item;
	}


	/**
	 * @return CheckboxList
	 */
	public function addCheckboxList($name, $label = NULL, $cols = NULL, $sep = NULL)
	{
		$item = $this[$name] = new Controls\CheckboxList($label, $cols, NULL);

		$sep = trim($sep, "<>");
		$item->setSeparator(Html::el($sep));	

		return $item;
	}


	/**
	 * Add submit 
	 * @param string
	 */
	public function addSubmit($name = "send", $label = "Uložit", $class = "btn btn-primary")
	{
		if (isset($this->typeClass[$name])) {
			$class = $this->typeClass[$name];
		}

		$item = parent::addSubmit($name, $label);
		$item->setAttribute("class", $class);

		return $item;
	}


	/* ****************************** seperated controls ****************************** */


	/**
	 * @return DatePicker
	 */
	public function addDatePicker($name, $label = NULL, $cols = NULL)
	{
		return $this[$name] = new Controls\DatePicker($label, $cols, NULL);
	}


	/**
	 * @return TimeControl
	 */
	public function addTime($name, $label = NULL, $cols = NULL)
	{
		return $this[$name] = new Controls\TimeControl($label, $cols, NULL);
	}


	/**
	 * @return AntispamControl
	 */
	public function addAntispam($name = "antispam", $label = "Toto pole vymažte.", $msg = "Byl detekován pokus o spam")
	{
		return $this[$name] = new Controls\AntispamControl($label, NULL, NULL, $msg);
	}



	/**
	 * @return JsonDependentSelectBox
	 */
	public function addJSelect($name, $label = NULL, $parents = NULL, $dataCallback)
	{
		return $this[$name] = new JsonDependentSelectBox($label, $parents, $dataCallback);
	}


	/**
	 * @return \Controls\Replicator
	 */
	public function addDynamic($name, $factory, $createDefault)
	{
		return $this[$name] = new Controls\Replicator($factory, $createDefault);
	}



	/* ************************** shortcuts ************************ */


	/**
	 * @return \Nette\DI\IContainer
	 */
	protected function getContext()
	{
		return $this->getPresenter()->context;
	}


	/**
	 * Models shortcut
	 */
	public function getModels()
	{
		return $this->context->models;
	}


	/**
	 * Translate shortuct
	 */
	public function translate($string)
	{
		return $this->translator->translate($string);
	}


	/**
	 * Create template
	 */
	public function createTemplate()
	{
		return $this->getPresenter()->createTemplate();
	}


	/**
	 * Redirect
	 */
	public function redirect()
	{
		call_user_func_array(array($this->getPresenter(), "redirect"), func_get_args());
	}


	/**
	 * Flash message shortcut
	 */
	public function flashMessage()
	{
		call_user_func_array(array($this->getPresenter(), "flashMessage"), func_get_args());
	}

}