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
$LOG_FILE_ID = 'classTest-byte';
ob_end_flush();

use CoreLibs\Convert\Byte;

$basic = new CoreLibs\Basic();
$byte_class = 'CoreLibs\Convert\Byte';

print "<html><head><title>TEST CLASS: BYTE CONVERT</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// class
$byte = 254779258;
$string = '242.98 MB';
// static
print "S::BYTE TO: $byte: ".$byte_class::humanReadableByteFormat($byte)."<br>";
print "S::BYTE FROM: $string: ".$byte_class::stringByteFormat($string)."<br>";

// *** BYTES TEST ***
$bytes = array(
	-123123123,
	999999, // KB-1
	999999999, // MB-1
	254779258, // MB-n
	999999999999999, // TB-1
	588795544887632, // TB-n
	999999999999999999, // PB-1
	9223372036854775807, // MAX INT
	999999999999999999999, // EB-1
);
print "<b>BYTE FORMAT TESTS</b><br>";
foreach ($bytes as $byte) {
	print '<div style="display: flex; border-bottom: 1px dashed gray;">';
	//
	print '<div style="width: 35%; text-align: right; padding-right: 2px;">';
	print "(".number_format($byte)."/".$byte.") bytes :";
	$_bytes = Byte::humanReadableByteFormat($byte);
	print '</div><div style="width: 10%;">'.$_bytes;
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
	print '</div><div style="width: 10%;">'.$_bytes;
	print '</div><div style="width: 10%;">';
	print Byte::stringByteFormat($_bytes);
	print "</div>";
	//
	print "</div>";
}

// DEPRECATED
/* $byte = 254779258;
$string = '242.98 MB';
print "BYTE TO: $byte: ".$basic->humanReadableByteFormat($byte)."<br>";
print "BYTE FROM: $string: ".$basic->stringByteFormat($string)."<br>"; */

// error message
print $basic->printErrorMsg();

print "</body></html>";

// __END__
