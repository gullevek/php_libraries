<?php

    $DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
    $DEBUG_ALL = 1;
	$PRINT_ALL = 1;
	$DB_DEBUG = 1;

	if ($DEBUG_ALL)
		error_reporting(E_ALL);

	// sample config
	require("config.inc");
	require(LIBS."Error.Handling.inc");

	if ($var) {
		echo "OUT<br>";
	}
?>
