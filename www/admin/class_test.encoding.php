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
$LOG_FILE_ID = 'classTest-encoding';
ob_end_flush();

use CoreLibs\Language\Encoding;

$basic = new CoreLibs\Basic();
$_encoding = new CoreLibs\Language\Encoding();
$encoding_class = 'CoreLibs\Language\Encoding';

print "<html><head><title>TEST CLASS: ENCODING</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// print "Valid encoding: ".$basic->printAr(mb_list_encodings())."<br>";

$mime_encodes = [
	['Simple string UTF8', 'UTF-8'],
	['Simple string ISO-2022-JP-MS', 'ISO-2022-JP-MS'],
	['日本語ながい', 'UTF-8'],
	['日本語ながい', 'ISO-2022-JP-MS'],
	['日本語ながい日本語ながい日本語ながい日本語ながい日本語ながい日本語ながい日本語ながい', 'ISO-2022-JP-MS'],
];
foreach ($mime_encodes as $mime_encode) {
	print "__MBMIMEENCODE: $mime_encode[0]: " . Encoding::__mbMimeEncode($mime_encode[0], $mime_encode[1]) . "<br>";
}

$enc_strings = [
	'Normal Text',
	'日本語',
	// unworkable
	''
];
// class
$_encoding->setErrorChar('∴');
foreach ($enc_strings as $_string) {
	$string = $_encoding->checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
	print "ENC CHECK: $_string: " . ($string === false ? '-OK-' : $string) . "<br>";
	print "CONV ENCODING: $_string: " . $_encoding->convertEncoding($_string, 'ISO-2022-JP') . "<br>";
	print "CONV ENCODING (s): $_string: " . $_encoding->convertEncoding($_string, 'ISO-2022-JP', 'UTF-8') . "<br>";
	print "CONV ENCODING (s,a-false): $_string: "
		. $_encoding->convertEncoding($_string, 'ISO-2022-JP', 'UTF-8', false) . "<br>";
}
print "ERROR CHAR: " . $_encoding->getErrorChar() . "<br>";
// static
$encoding_class::setErrorChar('∴');
foreach ($enc_strings as $_string) {
	$string = $encoding_class::checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
	print "S::ENC CHECK: $_string: " . ($string === false ? '-OK-' : $string) . "<br>";
	print "S::CONV ENCODING: $_string: " . $encoding_class::convertEncoding($_string, 'ISO-2022-JP') . "<br>";
	print "S::CONV ENCODING (s): $_string: "
		. $encoding_class::convertEncoding($_string, 'ISO-2022-JP', 'UTF-8') . "<br>";
	print "S::CONV ENCODING (s,a-false): $_string: "
		. $encoding_class::convertEncoding($_string, 'ISO-2022-JP', 'UTF-8', false) . "<br>";
}
print "S::ERROR CHAR: " . $encoding_class::getErrorChar() . "<br>";
// static use
$_string = $enc_strings[1];
$string = Encoding::checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
print "S::ENC CHECK: $_string: " . ($string === false ? '-OK-' : $string) . "<br>";

// DEPRECATED
/* $string = $basic->checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
print "ENC CHECK: $_string: ".($string === false ? '-OK-' : $string)."<br>";
print "CONV ENCODING: $_string: ".$basic->convertEncoding($_string, 'ISO-2022-JP')."<br>";
print "D/__MBMIMEENCODE: ".$basic->__mbMimeEncode('Some Text', 'UTF-8')."<br>"; */

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
