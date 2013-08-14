<?php

// Load and init Nette Framework
if ( ! defined('NETTE')) {
	require_once __DIR__ . '/../Nette/loader.php';
	require_once __DIR__ . '/../Nette/DI/CompilerExtension.php';
	require_once __DIR__ . '/common/Configurator.php';
	require_once __DIR__ . '/../Schmutzka/Utils/Neon.php';
}

require_once __DIR__ . '/shortcuts.php';
