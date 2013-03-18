<?php

namespace Schmutzka\Forms;

use Nette;
use Nette\Utils\Html;
use Schmutzka\Forms\Controls;
use MultipleFileUpload;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;

class Form extends Nette\Application\UI\Form
{
	/** validators */
	const RC = "Schmutzka\Forms\Rules::validateRC";
	const IC = "Schmutzka\Forms\Rules::validateIC";
	const PHONE = "Schmutzka\Forms\Rules::validatePhone";
	const ZIP = "Schmutzka\Forms\Rules::validateZip";
	const DATE = "Schmutzka\Forms\Rules::validateDate";
	const TIME = "Schmutzka\Forms\Rules::validateTime";
	const EXTENSION = "Schmutzka\Forms\Rules::extension";


	/** @var string */
	public $id;

	/** @var string */
	public $target;

	/** @var string */
	public $csrfProtection = "Prosím odešlete formulář znovu, vypršel bezpečnostní token.";

	/** @var bool */
	public $useBootstrap = TRUE;

	/** @var \Translator */
	protected $translator = NULL;

	/** @var callable */
	protected $processor;

	/** @var array */
	private $typeClass = array(
		"send" => "btn btn-primary",
		"cancel" => "btn",
		"reset" => "btn",
		"remove" => "btn btn-danger",
		"delete" => "btn btn-danger",
		"add" => "btn btn-success",
	);

	/** @var bool */
	private $isBuilt = FALSE;


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
	 * Custom form render for separate form
	 * @seeks APP_DIR/Forms/{formName}.latte
	 * @seeks LIBS_DIR/Schmutzka/Modules/{moduleName}/Forms/{formName}.latte
	 */
	public function renderTemplate()
	{
		$className = strtr($this->getReflection()->name, array("\\" => "/"));
		$className = strtr($className, array("Forms" => "forms"));
		$files[] = APP_DIR . "/" . lcfirst($className) . ".latte";
		$files[] = LIBS_DIR . "/Schmutzka/Modules/" . $className . ".latte";

		foreach ($files as $file) {
			if (file_exists($file)) {
				$template = $this->createTemplate($file);
				foreach ($this as $key => $value) {
					if (is_array($value) || is_string($value) || is_int($value)) {
						$template->{$key} = $value;
					}
				}

				return $template->render();
			}
		}

		throw new \Exception("$file not found.");
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
	 * @param mixed
	 */
	public function setDefaults($defaults, $erase = FALSE)
	{
		if (is_array($defaults)) {
			parent::setDefaults($defaults, $erase);
		}

		if (is_object($defaults)) {
			parent::setDefaults(get_object_vars($defaults), $erase);
		}

		return $this;
	}


	/**
	 * Flash message error
	 * @param string
	 */
	public function addError($message)
	{
		$this->valid = FALSE;

		if ($message) {
			$this->flashMessage($message, "error");
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

		if (method_exists($this, "afterBuild")) {
			$this->afterBuild();
		}

		if ($presenter->context->hasService("translator")) { // automatic translator
			$this->translator = $presenter->context->translator;
			$this->setTranslator($this->translator);
		}

		if ($presenter instanceof Nette\Application\IPresenter) {
			$this->attachHandlers($presenter);
		}

		if ($presenter->module != "front" && $this->useBootstrap) {
			$this->setRenderer(new BootstrapRenderer($presenter->template));
		}
	}


	/**
	 * Automatically attach methods
	 * @param Nette\Application\UI\Presenter
	 */
	protected function attachHandlers($presenter)
	{
		// $formNameSent = lcfirst($this->getName())."Sent";
		$formNameSent = "process" . lcfirst($this->getName());

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
	
		if ($this->getHttpData()) {
			foreach ($this->getHttpData() as $key => $value) {
				if (empty($values[$key]) && $value && !isset($this->typeClass[rtrim($key,"_")]) && $key != "_token_") {
					$values[$key] = $value;
				}
			}
		}

		foreach ($this->typeClass as $key => $value) { 
			unset($values[$key]);
		}

		if ($this->processor && is_callable($this->processor)) {
			$values = call_user_func($this->processor, $values);

		} elseif (method_exists($this->parent, lcfirst($this->getName()) . "Processor") && is_callable($this->processor)) { // find and use values processor if exists
			$values = call_user_func($this->processor, $values);
		}
		if ($removeEmpty) { 
			$values = array_filter($values); 
		}

		return $values;
	}


	/**
	 * Get submit control name
	 * @return string
	 */
	public function getSubmitName()
	{
		return $this->isSubmitted()->name;
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

		$sep = Html::el($sep);
		$item->setSeparator($sep);	

		return $item;
	}


	/**
	 * @return UploadControl

	 */
	public function addUpload($name, $label = NULL)
	{
		$basePath = $this->getHttpRequest()->url->scriptPath;
		$item = $this[$name] = new Controls\UploadControl($label, $basePath);

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
	 * @return AntispamControl
	 */
	public function addAntispam($name = "antispam", $label = "Toto pole vymažte.", $msg = "Byl detekován pokus o spam")
	{
		return $this[$name] = new Controls\AntispamControl($label, NULL, NULL, $msg);
	}


	/** 
	 * Adds suggest 
	 * @param string
	 * @param string
	 * @param array
	 */
	public function addSuggest($name, $label = NULL, $suggestList)
	{
		return $this[$name] = new Controls\SuggestControl($label, $suggestList);
	}


	/**
	 * Adds datepicker time
	 * @param string
	 * @param string
	 */
	public function addDateTimePicker($name, $label = NULL)
	{
		return $this[$name] = new Controls\DateTimePicker($label);
	}


	/**
	 * Adds multipel file upload
	 * @param string
	 * @param string
	 * @param int
	 */
	public function addMultipleFileUpload($name, $label = NULL, $maxFiles=999)
	{
		return $this[$name] = new MultipleFileUpload($label, $maxFiles);
	}


	/********************** helpers **********************/


	/**
	 * Translate shortuct
	 */
	public function translate($string)
	{
		return $this->translator->translate($string);
	}


	/**
	 * Create template
	 * @param string
	 */
	public function createTemplate($file = NULL)
	{
		$template = $this->getPresenter()->createTemplate();
		if ($file) {
			$template->setFile($file);
		}

		return $template;
	}


	/**
	 * @param callable
	 * @return self
	 */
	public function setProcessor($processor)
	{
		$this->processor = $processor;
		return $this;
	}

}