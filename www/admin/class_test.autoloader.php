<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();
// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-autoloader';
ob_end_flush();

# Test if composer autoloader works here

use CoreLibs\Convert\Byte;

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS: AUTOLOADER</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$bytes = 10242424;
$_bytes = Byte::humanReadableByteFormat($bytes);
print "BYTES: " . $_bytes  . "<br>";

print "</body></html>";

// __END__
