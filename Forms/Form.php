<?php

namespace Schmutzka\Forms;

use Schmutzka\Forms\Controls,
	Nette\Utils\Html,
	DependentSelectBox\JsonDependentSelectBox,
	DependentSelectBox\DependentSelectBox;

class Form extends \Nette\Application\UI\Form
{
	/** @var array */
	private $typeClass = array(
		"send" => "btn btn-primary",
		"submit" => "btn btn-primary",
		"cancel" => "btn",
		"return" => "btn",
		"reset" => "btn",
		"remove" => "btn btn-danger",
		"delete" => "btn btn-danger",
		"next" => "btn btn-success",
	);

	/** @var bool */
	private $isBuilt = FALSE;

	/** @var string */
	public $csrfProtection = "Prosím odešlete formulář znovu, vypršel bezpečnostní token.";

	/** @var \Translator */
	protected $translator = NULL;


	/**
	 * @param \Nette\ComponentModel\IContainer
	 * @param string
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		if ($this->csrfProtection) {
			$this->addProtection($this->csrfProtection);
		}
	}


	/**
	 * @deprecated
	 * Application form constructor
	 * @see: https://github.com/Venne/Venne-CMS/blob/master/Venne/Application/UI/Form.php#L34
	 */
	public function startup()
	{
	}


	/**
	 * beforeRender buildin function
	 */
	public function build()
	{
		$this->isBuilt = TRUE;
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
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\Application\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);
		$this->startup(); // bc compatbiltiy

		if (!$this->isBuilt) {
			$this->build();
		}

		if($this->getContext()->hasService("translator")) { // automatic translator
			$this->translator = $this->getContext()->translator;
			$this->setTranslator($this->translator);
		}

		if ($presenter instanceof \Nette\Application\IPresenter) {
			$this->attachHandlers($presenter);
		}
	}


	/**
	 * Automatically attach methods
	 * @see: https://github.com/Kdyby/Framework/blob/master/libs/Kdyby/Application/UI/Form.php#L76
	 */
	protected function attachHandlers($presenter)
	{
		$formNameSent = lcfirst($this->getName())."Sent";

		// auto callback on submit
		if (method_exists($presenter, $formNameSent)) {
			$this->onSuccess[] = callback($presenter, $formNameSent);
		}

		// auto callback on submit (in control use)
		if (method_exists($this->parent, $formNameSent)) {
			$this->onSuccess[] = callback($this->parent, $formNameSent);
		}


		// auto callback on submit in form factory
		if (method_exists($this, "process")) {
			$this->onSuccess[] = callback($this, "process");
		}

		// auto callback on submit in form factory (in control use)
		if (method_exists($this->parent, "process")) {
			$this->onSuccess[] = callback($this->parent, "process");
		}
	}


	/**
	 * Returns values as array
	 * @param bool
	 * @param bool
	 */
	public function getValues($removeEmpty = FALSE, $serialize = FALSE)
	{
		$values = parent::getValues(TRUE);

		// complete empty values by httpData
		foreach ($this->getHttpData() as $key => $value) {
			if (empty($values[$key]) AND $value AND in_array(rtrim($key,"_"),$this->typeClass)) {
				$values[$key] = $value;
			}
		}

		// unset submit buttons
		foreach ($this->typeClass as $key => $value) {
			unset($values[$key]);
		}

		// convert data object and unserialized
		foreach ($values as $key => $value) { 
			if (is_object($value) AND (get_class($value) == "Nette\DateTime" OR get_class($value) == "DateTime")) { // object to date
				$values[$key] = $value->format("Y-m-d");
			}
			elseif (is_array($value) AND $serialize) { // serialize array to string
				$values[$key] = serialize($value);
			}
		}

		// removes empty/NULL values
		if ($removeEmpty) { 
			$values = ($serialize ? array_filter($values, "strlen") : array_filter($values)); 
		}

		return $values;
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
	 * Adds a range input control to the form.
	 */
	public function addRange($name, $label = NULL, $step = 1, $min = NULL, $max = NULL)
	{
		$item = $this->addNumber($name, $label, $step, $min, $max);
		return $item->setAttribute('type', "range");
	}


	/**
	 * Adds a radio list
	 */
	public function addRadioList($name, $label = NULL, array $items = NULL, $sep = NULL)
	{
		$item = parent::addRadioList($name, $label, $items);
		$item->getSeparatorPrototype()->setName($sep);
		return $item;
	}


	/**
	 * @return CheckboxList
	 */
	public function addCheckboxList($name, $label = NULL, $cols = NULL, $sep = NULL)
	{
		$item = $this[$name] = new Controls\CheckboxList($label, $cols, NULL);
		$sep = Html::el($sep);
		$item->setSeparator($sep);	
		return $item;
	}


	/**
	 * Add submit 
	 * @param string
	 */
	public function addSubmit($name = "send", $label = "Uložit", $class = "btn btn-primary")
	{
		if(isset($this->typeClass[$name])) {
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
	 * @return AntispamControl
	 */
	public function addAntispam($name = "antispam", $label = "Toto pole vymažte.", $msg = "Byl detekován pokus o spam")
	{
		return $this[$name] = new Controls\AntispamControl($label, NULL, NULL, $msg);
	}


	/**
	 * @return DependentSelectBox
	 */
	public function addDSelect($name, $label = NULL, $parents = NULL, $dataCallback)
	{
		return $this[$name] = new DependentSelectBox($label, $parents, $dataCallback);
	}


	/**
	 * @return JsonDependentSelectBox
	 */
	public function addJSelect($name, $label = NULL, $parents = NULL, $dataCallback)
	{
		return $this[$name] = new JsonDependentSelectBox($label, $parents, $dataCallback);
	}


	/**
	 * @return Control\Date
	 */
	public function addDate($name, $label = NULL, $cols = NULL)
	{
		return $this[$name] = new Controls\Date($label, $cols, NULL);
	}


	/**
	 * @return Controls\DateTime
	 */
	public function addDateTime($name, $label = NULL, $cols = NULL)
	{
		return $this[$name] = new Controls\DateTime($label, $cols, NULL);
	}


	/**
	 * @return Controls\Time
	 */
	public function addTime($name, $label = NULL, $cols = NULL)
	{
		return $this[$name] = new Controls\Time($label, $cols, NULL);
	}


	/** @buggy
	 * @return Controls\MultipleFileUpload
	 */
	public function addMultipleUpload($name, $label = NULL)
	{
		return $this[$name] = new Controls\MultipleFileUpload($label);
	}


	/**
	 * @return \Controls\Replicator
	 */
	public function addDynamic($name, $factory, $createDefault)
	{
		return $this[$name] = new Controls\Replicator($factory, $createDefault);
	}


	/**
	 * @param array
	 * @throws \Nette\InvalidStateException
	 */
	public function processErrors(array $errors)
	{
		foreach ($errors as $name => $messages) {
			if (!isset($this[$name])) {
				throw new \Nette\InvalidStateException("Invalid value '$name' with messages '" . implode("', '", $messages) . "'");
			}
			foreach ($messages as $error) {
				$this[$name]->addError($error);
			}
		}
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

\Schmutzka\Forms\Controls\AntispamControl::register();