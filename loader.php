<?php

define('ROOT_DIR', __DIR__ . '/../..');
define('WWW_DIR', ROOT_DIR . '/www');
define('APP_DIR', ROOT_DIR . '/app');
define('LIBS_DIR', ROOT_DIR . '/libs');
define('MODULES_DIR', LIBS_DIR . '/Schmutzka/Modules');

// Load and init Nette Framework
if (!defined('NETTE')) {
	require_once __DIR__ . "/../Nette/loader.php";
	require_once __DIR__ . '/common/Configurator.php';
}

require_once __DIR__ . "/shortcuts.php";
