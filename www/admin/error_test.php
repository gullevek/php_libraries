<?php

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
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
