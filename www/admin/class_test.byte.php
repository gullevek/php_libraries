<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-byte';
ob_end_flush();

use CoreLibs\Convert\Byte;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$byte_class = 'CoreLibs\Convert\Byte';

$PAGE_NAME = 'TEST CLASS: BYTE CONVERT';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
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
	print '</div>';
	print '<div style="width: 10%;">' . $_bytes . '</div>';
	print '<div style="width: 40%;">';
	try {
		print Byte::stringByteFormat($_bytes);
	} catch (\LengthException $e) {
		print "LengthException 1: " . $e->getMessage();
		try {
			print "<br>S: " . Byte::stringByteFormat($_bytes, Byte::RETURN_AS_STRING);
		} catch (\LengthException $e) {
			print "LengthException 2: " . $e->getMessage();
		} catch (\RuntimeException $e) {
			print "RuntimeException 1: " . $e->getMessage();
		}
	}
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
	print '</div><div style="width: 40%;">';
	try {
		print Byte::stringByteFormat($_bytes);
	} catch (\LengthException $e) {
		print "LengthException A: " . $e->getMessage();
		try {
			print "<br>Ssi: " . Byte::stringByteFormat($_bytes, Byte::RETURN_AS_STRING | Byte::BYTE_FORMAT_SI);
		} catch (\LengthException $e) {
			print "LengthException B: " . $e->getMessage();
		} catch (\RuntimeException $e) {
			print "RuntimeException A: " . $e->getMessage();
		}
	}
	print "</div>";
	//
	print "</div>";
}

$string_bytes = [
	'-117.42 MB',
	'242.98 MB',
	'254.78 MiB',
	'1 EiB',
	'8 EB',
	'867.36EB',
	'1000EB',
	'10000EB',
];
print "<b>BYTE STRING TO BYTES TESTS</b><br>";
foreach ($string_bytes as $string) {
	print '<div style="display: flex; border-bottom: 1px dashed gray;">';
	//
	print '<div style="width: 35%; text-align: right; padding-right: 2px;">';
	print "string byte ($string) to bytes :";
	try {
		$_bytes = Byte::stringByteFormat($string);
	} catch (\LengthException $e) {
		print "<br>LengthException A: " . $e->getMessage();
		$_bytes = 0;
	}
	try {
		$_bytes_string = Byte::stringByteFormat($string, Byte::RETURN_AS_STRING);
	} catch (\LengthException $e) {
		print "<br>LengthException B: " . $e->getMessage();
		$_bytes_string = '';
	} catch (\RuntimeException $e) {
		print "<br>RuntimeException: " . $e->getMessage();
		$_bytes_string = '';
	}
	try {
		$_bytes_si = Byte::stringByteFormat($string, Byte::BYTE_FORMAT_SI);
	} catch (\LengthException $e) {
		print "<br>LengthException A: " . $e->getMessage();
		$_bytes_si = 0;
	}
	try {
		$_bytes_string_si = Byte::stringByteFormat($string, Byte::RETURN_AS_STRING | Byte::BYTE_FORMAT_SI);
	} catch (\LengthException $e) {
		print "<br>LengthException B: " . $e->getMessage();
		$_bytes_string_si = '';
	} catch (\RuntimeException $e) {
		print "<br>RuntimeException: " . $e->getMessage();
		$_bytes_string_si = '';
	}
	print '</div>';
	print '<div style="width: 20%;">'
		. "F:" . number_format((int)$_bytes)
		. '<br>B: ' . $_bytes
		. '<br>S: ' . $_bytes_string
		. "<br>Fsi:" . number_format((int)$_bytes_si)
		. '<br>Bsi: ' . $_bytes_si
		. '<br>Ssi: ' . $_bytes_string_si;
	print '</div>';
	print '<div style="width: 10%;">';
	print "B: " . Byte::humanReadableByteFormat($_bytes) . "<br>";
	print "Bsi: " . Byte::humanReadableByteFormat($_bytes_si, Byte::BYTE_FORMAT_SI);
	print "</div>";
	print "</div>";
}
print "</body></html>";

// __END__
