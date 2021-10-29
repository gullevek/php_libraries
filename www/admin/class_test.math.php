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
$LOG_FILE_ID = 'classTest-math';
ob_end_flush();

$basic = new CoreLibs\Basic();
$_math = new CoreLibs\Convert\Math();
$math_class = 'CoreLibs\Convert\Math';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: MATH</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

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

// DEPRECATED
/* print "FCEIL: ".$basic->fceil(5.1234567890, 5)."<br>";
print "FLOORP: ".$basic->floorp(5123456, -3)."<br>";
print "INITNUMERIC: ".$basic->initNumeric('123')."<br>"; */

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
