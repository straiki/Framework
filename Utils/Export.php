<?php

namespace Schmutzka\Utils; 

use Nette\Object,
	Nette\Diagnostics\Debugger;

class Export extends Object
{

	/** @var \DI\Container */
	private $context;
	
	/** @var \UI\Presenter */
	private $presenter;
	
	/** @var string */
	public $sep = ";";

	/** @var string */
	public $fileName;


	public function __construct($context, $presenter)
	{
		$this->context = $context;
		$this->presenter = $presenter;
	}


	/**
	 * CVS export
	 * @param array
	 * @param array
	 */
	public function cvs(array $fields, array $data)
	{
		$file = $this->context->parameters["appDir"]."/../libs/Schmutzka/Utils/Export/csv.latte";

		header('Content-Type: application/csv, windows-1250');
		header('Content-Disposition: attachment;filename="'.$this->fileName.'.csv"');
		header('Cache-Control: max-age=0');

		$template = $this->presenter->createTemplate()->setFile($file);
		$template->fields = $fields;
		$template->data = $data;
		$template->sep = $this->sep;

		$template->render();
		$this->presenter->terminate();
	}


	/**
	 * Iconv shortcut - alternative to helper
	 */
	private function ic($value, $from = "utf-8", $to = "windows-1250") { 
		return iconv($from, $to, $value);
	}
	 

}