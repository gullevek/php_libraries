<?php

declare(strict_types=1);

$DEBUG_LEVEL = \CoreLibs\Logging\Logger\Level::Debug;

if ($DEBUG_LEVEL->name == 'Debug') {
	error_reporting(E_ALL);
}

// sample config
require 'config.php';
echo "FILE: " . BASE . LIB . "Error.Handling.php<br>";
require(BASE . LIB . "Error.Handling.php");

if ($var) {
	echo "OUT<br>";
}
// this wll throw an error and also write
// asdfa(09);
