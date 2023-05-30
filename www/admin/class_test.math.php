<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-math';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
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

print "</body></html>";

// __END__
