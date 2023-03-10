<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-VarIsType';
ob_end_flush();

use CoreLibs\Convert\SetVarType;
use CoreLibs\Convert\SetVarTypeNull;
use CoreLibs\Debug\Support;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);

$PAGE_NAME = 'TEST CLASS: CONVERT\VARISTYPE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';


print "Test A str set: " . SetVarType::setStr(5, 'set int') . "<br>";
print "Test A str make int: " . SetVarType::makeStr(5, 'make int') . "<br>";
print "Test A str make object: " . SetVarType::makeStr($log, 'Object') . "<br>";
print "Test A str make null: " . SetVarType::makeStr(null, 'null') . "<br>";
print "Test B int set: " . SetVarType::setInt("5", -1) . "<br>";
print "Test B int make string: " . SetVarType::makeInt("5", -1) . "<br>";
print "Test B' int make float: " . SetVarType::makeInt("5.5", -1) . "<br>";
print "Test B'' int make class: " . SetVarType::makeInt($log, -1) . "<br>";
print "Test B''' int make hex: " . SetVarType::makeInt("0x55", -1) . "<br>";
print "Test B''' int make hex: " . SetVarType::makeInt(0x55, -1) . "<br>";
print "Test C float make: " . SetVarType::makeFloat("13,232.95", -1) . "<br>";
print "Test D floatval: " . floatval("13,232.95") . "<br>";
print "Test E filter_var: "
	. filter_var("13,232.95", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . "<br>";
print "Test F filter_var: "
	. filter_var("string", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . "<br>";

print "Test G make bool: " . SetVarType::makeBool("on") . "<br>";
print "Test G make bool: " . SetVarType::makeBool(null) . "<br>";
print "Test G make bool: " . SetVarType::makeBool("null") . "<br>";
print "Test G make bool: " . SetVarType::makeBool($log) . "<br>";
print "Test G make bool: " . SetVarTypeNull::makeBool($log) . "<br>";

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
		. Support::printToString(SetVarType::setStr($string)) . "-<br>";
	print "Int: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarType::setInt($string)) . "-<br>";
	print "Float: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarType::setFloat($string)) . "-<br>";
	print "Bool: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarType::setBool($string)) . "-<br>";
	print "Array: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarType::setArray($string)) . "-<br>";
	print "<hr>";
}

foreach ($checks as $string) {
	print "** SET NULL: (" . gettype($string) . ")<br>";
	print "Str: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarTypeNull::setStr($string)) . "-<br>";
	print "Int: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarTypeNull::setInt($string)) . "-<br>";
	print "Float: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarTypeNull::setFloat($string)) . "-<br>";
	print "Bool: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarTypeNull::setBool($string)) . "-<br>";
	print "Array: " . Support::printToString($string) . ": -"
		. Support::printToString(SetVarTypeNull::setArray($string)) . "-<br>";
	print "<hr>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
