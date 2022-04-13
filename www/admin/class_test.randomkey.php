<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

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
$LOG_FILE_ID = 'classTest-randomkey';
ob_end_flush();

use CoreLibs\Create\RandomKey;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
$array_class = 'CoreLibs\Create\RandomKey';

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS: RANDOM KEY</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$key_length = 10;
$key_length_b = 5;
$key_lenght_long = 64;
print "S::RANDOMKEYGEN(auto): " . RandomKey::randomKeyGen() . "<br>";
print "S::SETRANDOMKEYLENGTH($key_length): " . RandomKey::setRandomKeyLength($key_length) . "<br>";
print "S::RANDOMKEYGEN($key_length): " . RandomKey::randomKeyGen() . "<br>";
print "S::RANDOMKEYGEN($key_length_b): " . RandomKey::randomKeyGen($key_length_b) . "<br>";
print "S::RANDOMKEYGEN($key_length): " . RandomKey::randomKeyGen() . "<br>";
print "S::RANDOMKEYGEN($key_lenght_long): " . RandomKey::randomKeyGen($key_lenght_long) . "<br>";
$_array = new CoreLibs\Create\RandomKey();
print "C->RANDOMKEYGEN(auto): " . $_array->randomKeyGen() . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
