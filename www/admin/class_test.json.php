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
$LOG_FILE_ID = 'classTest-json';
ob_end_flush();

use CoreLibs\Convert\Json;
use CoreLibs\Debug\Support as DgS;

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
$basic = new CoreLibs\Basic($log);
$json_class = 'CoreLibs\Convert\Json';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: JSON</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$json = '{"foo": "bar"}';
$output = Json::jsonConvertToArray($json);
print "S::JSON: $json: " . DgS::printAr($output) . "<br>";
print "S::JSON ERROR: " . Json::jsonGetLastError() . ": " . Json::jsonGetLastError(true) . "<br>";

$json = '["f: {b"""ar}]';
$output = Json::jsonConvertToArray($json);
print "S::E-JSON: $json: " . DgS::printAr($output) . "<br>";
print "S::E-JSON ERROR: " . Json::jsonGetLastError() . ": " . Json::jsonGetLastError(true) . "<br>";

// direct
$json = '{"direct": "static function call"}';
$output = $json_class::jsonConvertToArray($json);
print "J/S::JSON: $json: " . DgS::printAr($output) . "<br>";
print "J/S::JSON ERROR: " . $json_class::jsonGetLastError() . ": " . $json_class::jsonGetLastError(true) . "<br>";

$json = '["f: {b"""ar}]';
$output = $json_class::jsonConvertToArray($json);
print "J/S::E-JSON: $json: " . DgS::printAr($output) . "<br>";
print "J/S::E-JSON ERROR: " . $json_class::jsonGetLastError() . ": " . $json_class::jsonGetLastError(true) . "<br>";

// DEPRECATE TEST
/* $json = '["f: {b"""ar}]';
$output = $basic->jsonConvertToArray($json);
print "E-JSON: $json: ".DgS::printAr($output)."<br>";
print "E-JSON ERROR: ".$basic->jsonGetLastError().": ".$basic->jsonGetLastError(true)."<br>"; */

// $json = '{"foo": "bar"}';
// $output = Jason::jsonConvertToArray($json);
// print "S::JSON: $json: " . DgS::printAr($output) . "<br>";
// print "S::JSON ERROR: " . Jason::jsonGetLastError() . ": " . Jason::jsonGetLastError(true) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__