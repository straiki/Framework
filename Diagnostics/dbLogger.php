<?php

/**
 * Logs debug data into database
 * @table debug_logs (lazy sql creation dbLogger.sql)
 * @use {control dbLogger} at the end of @layout.latte
 * @2DO: panel do debugbaru se stats
 */

namespace Schmutzka\Diagnostics;

use Nette\Utils\Finder;

class dbLogger extends \Nette\Application\UI\Control
{

	/** @var context */
	private $context;

	/** @var \NotORM */
	private $db;


	/**
	 * Saves info into db
	 * @param context
	 * @param int
	 * @param string
	 * @param string
	 * @param object
	 */
	public function __construct($context, $time, $memory, $mode, $presenter)
	{	
		$this->context = $context;
		$this->db = $this->context->database;

		$time = (int) ($time * 1000);
		$memory =  $memory / 1000000;

		// module:presenter:view
		$presenterName = $presenter->name;
		$presenterView = $presenter->view;

		if(strpos($presenterName,":")) { // module version
			list($presenterModule, $presenterName) = explode(":", $presenterName);
		}
		else {
			$presenterModule = "Front";
		}

		// was the file being cached?
		$cacheDir = $this->context->params["tempDir"]."/cache/_Nette.FileTemplate/";
		$cachedFile = "_".$presenterName.".".$presenterView;

		$foundFiles = Finder::findFiles($cachedFile."*")
			->date(">", "- 5 seconds") // not older than 5 seconds (~ cache creation time)
			->in($cacheDir);

		$cacheBuilt = FALSE;
		foreach($foundFiles as $key => $file) {
			$cacheBuilt = TRUE; 

			$needle = array(
				"module" => $presenterModule,
				"presenter" => $presenterName,
				"view" => $presenterView,	
				"cacheBuilt" => 1
			);
			$cacheBuiltCheck = $this->db->debug_logs()
				->where($needle)
				->where("timestamp > ?", strtotime("-5 seconds"));
			$cacheBuiltCheck = $cacheBuiltCheck->fetchSingle("cacheBuilt");


			if($cacheBuiltCheck) {
				$cacheBuilt = FALSE;
			}
		
		}

		// data to save
		$array = array(
			"time" => $time,
			"memory" => $memory,
			"mode" => $mode,
			"module" => $presenterModule,
			"presenter" => $presenterName,
			"view" => $presenterView,	
			"cacheBuilt" => $cacheBuilt
		);

		// try insert data
		try {
			return $this->db->debug_logs()->insert($array);
		}
		catch (\PDOException $e) { // table does not exist -> create it
			$dbLoggerSql = file_get_contents(__DIR__."\dbLogger.sql");
			$this->context->pdo->query($dbLoggerSql);
			return $this->context->database->debug_logs()->insert($array);
		}	
	}

	// nutné pro fčnost komponenty
	public function render()
	{
	}

}