<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

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

use CoreLibs\Check\Jason;

$basic = new CoreLibs\Basic();
$json_class = 'CoreLibs\Check\Jason';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: JSON</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$json = '{"foo": "bar"}';
$output = Jason::jsonConvertToArray($json);
print "S::JSON: $json: ".$basic->printAr($output)."<br>";
print "S::JSON ERROR: ".Jason::jsonGetLastError().": ".Jason::jsonGetLastError(true)."<br>";

$json = '["f: {b"""ar}]';
$output = Jason::jsonConvertToArray($json);
print "S::E-JSON: $json: ".$basic->printAr($output)."<br>";
print "S::E-JSON ERROR: ".Jason::jsonGetLastError().": ".Jason::jsonGetLastError(true)."<br>";

// direct
$json = '{"direct": "static function call"}';
$output = $json_class::jsonConvertToArray($json);
print "J/S::JSON: $json: ".$basic->printAr($output)."<br>";
print "J/S::JSON ERROR: ".$json_class::jsonGetLastError().": ".$json_class::jsonGetLastError(true)."<br>";

$json = '["f: {b"""ar}]';
$output = $json_class::jsonConvertToArray($json);
print "J/S::E-JSON: $json: ".$basic->printAr($output)."<br>";
print "J/S::E-JSON ERROR: ".$json_class::jsonGetLastError().": ".$json_class::jsonGetLastError(true)."<br>";

// DEPRECATE TEST
/* $json = '["f: {b"""ar}]';
$output = $basic->jsonConvertToArray($json);
print "E-JSON: $json: ".$basic->printAr($output)."<br>";
print "E-JSON ERROR: ".$basic->jsonGetLastError().": ".$basic->jsonGetLastError(true)."<br>"; */

// error message
print $basic->printErrorMsg();

print "</body></html>";

// __END__
