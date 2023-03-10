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
$LOG_FILE_ID = 'classTest-byte';
ob_end_flush();

use CoreLibs\Convert\Byte;

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
$byte_class = 'CoreLibs\Convert\Byte';

$PAGE_NAME = 'TEST CLASS: BYTE CONVERT';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// class
$byte = 254779258;
$string = '242.98 MB';
// static
print "S::BYTE TO (calls as var): $byte: " . $byte_class::humanReadableByteFormat($byte) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_SI) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_ADJUST) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_NOSPACE) . "<br>";
print "S::BYTE FROM: $string: " . Byte::stringByteFormat($string) . "<br>";
//
$byte = 314572800;
$string = '300 MB';
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_SI) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_ADJUST) . "<br>";
print "S::BYTE TO: $byte: "
	. Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_ADJUST | Byte::BYTE_FORMAT_NOSPACE) . "<br>";
print "S::BYTE TO: $byte: " . Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_NOSPACE) . "<br>";
print "S::BYTE FROM: $string: " . Byte::stringByteFormat($string) . "<br>";

// *** BYTES TEST ***
$bytes = [
	-123123123,
	999999, // KB-1
	999999999, // MB-1
	254779258, // MB-n
	999999999999999, // TB-1
	588795544887632, // TB-n
	999999999999999999, // PB-1
	9223372036854775807, // MAX INT
	999999999999999999999, // EB-1
];
print "<b>BYTE FORMAT TESTS</b><br>";
foreach ($bytes as $byte) {
	print '<div style="display: flex; border-bottom: 1px dashed gray;">';
	//
	print '<div style="width: 35%; text-align: right; padding-right: 2px;">';
	print "(" . number_format($byte) . "/" . $byte . ") bytes :";
	$_bytes = Byte::humanReadableByteFormat($byte);
	print '</div><div style="width: 10%;">' . $_bytes;
	print '</div><div style="width: 10%;">';
	print Byte::stringByteFormat($_bytes);
	print "</div>";
	//
	print "</div>";
	//
	print '<div style="display: flex; border-bottom: 1px dotted red;">';
	//
	print '<div style="width: 35%; text-align: right; padding-right: 2px;">';
	print "bytes [si]:";
	$_bytes = Byte::humanReadableByteFormat($byte, Byte::BYTE_FORMAT_SI);
	print '</div><div style="width: 10%;">' . $_bytes;
	print '</div><div style="width: 10%;">';
	print Byte::stringByteFormat($_bytes);
	print "</div>";
	//
	print "</div>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
