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
$LOG_FILE_ID = 'classTest-math';
ob_end_flush();

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
$_math = new CoreLibs\Convert\Math();
$math_class = 'CoreLibs\Convert\Math';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: MATH';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "FCEIL: " . $_math->fceil(5.1234567890, 5) . "<br>";
print "FLOORP: " . $_math->floorp(5123456, -3) . "<br>";
print "FLOORP: " . $_math->floorp(5123456, -10) . "<br>";
print "INITNUMERIC: " . $_math->initNumeric('123') . "<br>";

print "S-FCEIL: " . $math_class::fceil(5.1234567890, 5) . "<br>";
print "S-FLOORP: " . $math_class::floorp(5123456, -3) . "<br>";
print "S-INITNUMERIC: " . $math_class::initNumeric(123) . "<br>";
print "S-INITNUMERIC: " . $math_class::initNumeric(123.456) . "<br>";
print "S-INITNUMERIC: " . $math_class::initNumeric('123') . "<br>";
print "S-INITNUMERIC: " . $math_class::initNumeric('123.456') . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
