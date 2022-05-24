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
$LOG_FILE_ID = 'classTest-encoding';
ob_end_flush();

use CoreLibs\Convert\Encoding as ConEnc;
use CoreLibs\Check\Encoding as ChkEnc;
use CoreLibs\Convert\MimeEncode;

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
// class type
$_chk_enc = new CoreLibs\Check\Encoding();
$_con_enc = new CoreLibs\Convert\Encoding();
$chk_enc = 'CoreLibs\Check\Encoding';

$PAGE_NAME = 'TEST CLASS: ENCODING (CHECK/CONVERT/MIME)';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// print "Valid encoding: ".$log->printAr(mb_list_encodings())."<br>";

$mime_encodes = [
	['Simple string UTF8', 'UTF-8'],
	['Simple string ISO-2022-JP-MS', 'ISO-2022-JP-MS'],
	['日本語ながい', 'UTF-8'],
	['日本語ながい', 'ISO-2022-JP-MS'],
	['日本語ながい日本語ながい日本語ながい日本語ながい日本語ながい日本語ながい日本語ながい', 'ISO-2022-JP-MS'],
];
foreach ($mime_encodes as $mime_encode) {
	print "__MBMIMEENCODE: $mime_encode[0]: " . MimeEncode::__mbMimeEncode($mime_encode[0], $mime_encode[1]) . "<br>";
}
echo "<br>";

$enc_strings = [
	'Normal Text',
	'日本語',
	// bad
	'❶',
	// unworkable
	''
];
// class
$_chk_enc->setErrorChar(0x2234);
$_chk_enc->setErrorChar('∴');
print "ERROR CHAR: " . $_chk_enc->getErrorChar() . "<br>";
foreach ($enc_strings as $_string) {
	$string = $_chk_enc->checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
	print "ENC CHECK: $_string: " . ($string === false ? '<b>-OK-</b>' : print_r($string, true)) . "<br>";
	print "CONV ENCODING: $_string: " . $_con_enc->convertEncoding($_string, 'ISO-2022-JP') . "<br>";
	print "CONV ENCODING (s): $_string: " . $_con_enc->convertEncoding($_string, 'ISO-2022-JP', 'UTF-8') . "<br>";
	print "CONV ENCODING (s,a-false): $_string: "
		. $_con_enc->convertEncoding($_string, 'ISO-2022-JP', 'UTF-8', false) . "<br>";
}
echo "<br>";
// static
// ChkEnc::setErrorChar('∴');
ChkEnc::setErrorChar(0x2234);
print "S::ERROR CHAR: " . ChkEnc::getErrorChar() . "<br>";
foreach ($enc_strings as $_string) {
	$string = ChkEnc::checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
	print "S::ENC CHECK: $_string: " . ($string === false ? '<b>-OK-</b>' : print_r($string, true)) . "<br>";
	print "S::CONV ENCODING: $_string: " . ConEnc::convertEncoding($_string, 'ISO-2022-JP') . "<br>";
	print "S::CONV ENCODING (s): $_string: "
		. ConEnc::convertEncoding($_string, 'ISO-2022-JP', 'UTF-8') . "<br>";
	print "S::CONV ENCODING (s,a-false): $_string: "
		. ConEnc::convertEncoding($_string, 'ISO-2022-JP', 'UTF-8', false) . "<br>";
}
// static use
$_string = $enc_strings[1];
$string = $chk_enc::checkConvertEncoding($_string, 'UTF-8', 'ISO-2022-JP-MS');
print "S::ENC CHECK: $_string: " . ($string === false ? '-OK-' : $string) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
