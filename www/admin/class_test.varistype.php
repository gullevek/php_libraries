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
// define log file id
$LOG_FILE_ID = 'classTest-VarIsType';
ob_end_flush();

use CoreLibs\Convert\VarSetType;
use CoreLibs\Convert\VarSetTypeNull;
use CoreLibs\Debug\Support;

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

$PAGE_NAME = 'TEST CLASS: CONVERT\VARISTYPE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';


print "Test A str set: " . VarSetType::setStr(5, 'set int') . "<br>";
print "Test A str make int: " . VarSetType::makeStr(5, 'make int') . "<br>";
print "Test A str make object: " . VarSetType::makeStr($log, 'Object') . "<br>";
print "Test A str make null: " . VarSetType::makeStr(null, 'null') . "<br>";
print "Test B int set: " . VarSetType::setInt("5", -1) . "<br>";
print "Test B int make string: " . VarSetType::makeInt("5", -1) . "<br>";
print "Test B' int make float: " . VarSetType::makeInt("5.5", -1) . "<br>";
print "Test B'' int make class: " . VarSetType::makeInt($log, -1) . "<br>";
print "Test B''' int make hex: " . VarSetType::makeInt("0x55", -1) . "<br>";
print "Test B''' int make hex: " . VarSetType::makeInt(0x55, -1) . "<br>";
print "Test C float make: " . VarSetType::makeFloat("13,232.95", -1) . "<br>";
print "Test D floatval: " . floatval("13,232.95") . "<br>";
print "Test E filter_var: "
	. filter_var("13,232.95", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . "<br>";
print "Test F filter_var: "
	. filter_var("string", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . "<br>";

print "Test G make bool: " . VarSetType::makeBool("on") . "<br>";
print "Test G make bool: " . VarSetType::makeBool(null) . "<br>";
print "Test G make bool: " . VarSetType::makeBool("null") . "<br>";
print "Test G make bool: " . VarSetType::makeBool($log) . "<br>";
print "Test G make bool: " . VarSetTypeNull::makeBool($log) . "<br>";

print "<hr>";

$checks = [
	'string',
	5,
	1.2,
	['array'],
	true,
	// resource?
];
foreach ($checks as $string) {
	print "** SET NOT NULL: (" . gettype($string) . ")<br>";
	print "Str: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetType::setStr($string)) . "-<br>";
	print "Int: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetType::setInt($string)) . "-<br>";
	print "Float: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetType::setFloat($string)) . "-<br>";
	print "Bool: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetType::setBool($string)) . "-<br>";
	print "Array: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetType::setArray($string)) . "-<br>";
	print "<hr>";
}

foreach ($checks as $string) {
	print "** SET NULL: (" . gettype($string) . ")<br>";
	print "Str: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetTypeNull::setStr($string)) . "-<br>";
	print "Int: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetTypeNull::setInt($string)) . "-<br>";
	print "Float: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetTypeNull::setFloat($string)) . "-<br>";
	print "Bool: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetTypeNull::setBool($string)) . "-<br>";
	print "Array: " . Support::printToString($string) . ": -"
		. Support::printToString(VarSetTypeNull::setArray($string)) . "-<br>";
	print "<hr>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__