<?php

namespace Schmutzka\Forms\Controls;

use Nette\Forms\IControl;
use Nette\Utils\Html;

class UploadControl extends \Nette\Forms\Controls\UploadControl
{
	/** @var string */
	public $imagePath;

	/** @var string */
	public $filePath;

	/** @var string */
	private $basePath;


	/**
	 * @param string
	 */
	public function __construct($label = NULL, $basePath)
	{
		parent::__construct($label);
		$this->basePath = $basePath;
	}


	/**
	 * Generates control and suggets script handler
	 */
	public function getControl()
	{
		$control = parent::getControl();

		if ($this->imagePath) {
			$wrapper = Html::el("p");
			$image = Html::el("img")->setSrc($this->basePath . $this->imagePath);			

			$wrapper->add($control);
			$wrapper->add(Html::el("br"));
			$wrapper->add(Html::el("br"));
			$wrapper->add($image);
			return $wrapper;

		} elseif ($this->filePath) {
			$wrapper = Html::el("p");
			$wrapper->add($control);
			$wrapper->add(Html::el("br"));
			$wrapper->add(Html::el("br"));

			$filePath = $this->basePath . $this->filePath;

			if (file_exists(WWW_DIR . $filePath)) {
				$file = Html::el("a")->href($this->basePath . $this->filePath)->setText($this->filePath)->setTarget("blank");
				$wrapper->add($file);
			}

			return $wrapper;
		}


		return $control;
	}

}