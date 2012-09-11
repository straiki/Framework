<?php

// http://j.maxmind.com/app/geoip.js - detection

/**
 * All the joints saver
 * d - dump
 * dd - dump; die;
 * bd - barDump
 * wc - where called?
 * ss - Scite shortcut creator - 2DO, possilbity to safe 
	* 1. Možnost doplnit zkratku do daného souboru
	* addSc("makro", $code); -> zkontroluje, zda už daná zkratka není obsazená, pokud ne, přidá ji na nový řádek (určeno staticky, např. na první a odřádkje); pokud obsahuje, zařve! tím spíše, poud obsahouje stejný kód! :) mrtě luxus sakra
 */

use Nette\Callback,
	Nette\Diagnostics\Debugger,
	Nette\Diagnostics\Helpers,
	Nette\Utils\MimeTypeDetector;

/**
 * Converts file to base64
 * @param string
 */
function base64($image) 
{
	$mime = MimeTypeDetector::fromFile($image);
	$imageContent = file_get_contents((string)$image);
	echo "data:" . $mime . ';base64,' . base64_encode($imageContent);

	die;
}


/**	
 * @shortens dump 
 */
function d()
{
	foreach(func_get_args() as $var) {
		dump($var);
	}
}


/**
 * @shortens dump;die;
 */
function dd()
{
	foreach(func_get_args() as $var) {
		dump($var);
	}
	die;
}


/**
 * echo;die
 */
function ed($value)
{
	echo $value;
	die;
}


/**	
 * Foreach dump;
 */
function fd($values) {
	foreach($values as $key => $value) {
		dump("key: $key");
		if(!is_array($value)) {
			$value = iterator_to_array($value);
		}
		dump($value);
		echo "<hr style='border:0px;border-top:1px solid #DDD;height:0px;'>";
	}
}


/**	
 * Foreach dump;die;
 */
function fdd($values) {
	fd($values);
	die;
}


/**	
 * Table dump;
 * @param mixed
 * @2DO: for 2 dimensional values only
 */
function td($values, $depth = NULL) {
	d("test");
	echo "<table border=1 style='border-color:#DDD;border-collapse:collapse; font-family:Courier New; color:#222; font-size:13px' cellspacing=0 cellpadding=5>";
	$th = FALSE;
	foreach($values as $key => $value) {
		if(!$th) {
			echo "<tr>";
			foreach($value as $key2 => $value2) {
				echo "<th>".$key2."</th>";
			}
			echo "</tr>";
		}
		$th = TRUE;

		echo "<tr>";
		foreach($value as $key2 => $value2) {
			echo "<td>".$value2."</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}


/**	
 * Table dump;die;
 * @param mixed
 */
function tdd($values, $deph = NULL)
{
	td($values);
	die;
}


/**
 * Bar dump shortcut
 * @see Nette\Diagnostics\Debugger::barDump
 * @param mixed
 * @param string
 */
function bd($var, $title = NULL)
{
	Debugger::barDump($var, $title);
}


/**
 * Function prints from where were method/function called
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @param int $level
 * @param bool $return
 * @param bool $fullTrace
 */
function wc($level = 1, $return = FALSE, $fullTrace = FALSE) {
	if (Debugger::$productionMode) { return; }

	$o = function ($t) { return (isset($t->class) ? htmlspecialchars($t->class) . "->" : NULL) . htmlspecialchars($t->function) . '()'; };
	$f = function ($t) {
		$file = defined('APP_DIR') ? 'app' . str_replace(realpath(APP_DIR), '', realpath($t->file)) : $t->file;
		return Helpers::editorLink($t->file, $t->line);
	};

	$trace = debug_backtrace();
	$target = (object)$trace[$level];
	$caller = (object)$trace[$level+1];
	$message = NULL;

	if ($fullTrace) {
		array_shift($trace);
		foreach ($trace as $call) {
			$message .= $o((object)$call) . " \n";
		}

	} else {
		$message = $o($target) . " called from " . $o($caller) . " (" . $f($caller) . ")";
	}

	if ($return) {
		return strip_tags($message);
	}
	echo "<pre class='nette-dump'>" . nl2br($message) . "</pre>";
}


/**
 * Convert script into shortcut
 * @param mixed
 * @return string
 */
function ss($code)
{
	$array = array(
		"\t" => "\\t",
		"\n" => "\\n",
	);

	echo strtr($code, $array);	
	exit();
}