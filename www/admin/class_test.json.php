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
$LOG_FILE_ID = 'classTest-json';
ob_end_flush();

use CoreLibs\Convert\Json;
use CoreLibs\Debug\Support as DgS;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$json_class = 'CoreLibs\Convert\Json';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: JSON';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$json = '{"foo": "bar"}';
$output = Json::jsonConvertToArray($json);
print "S::JSON: $json: " . DgS::printAr($output) . "<br>";
print "S::JSON ERROR: " . Json::jsonGetLastError() . ": " . Json::jsonGetLastError(true) . "<br>";

$json = '["f: {b"""ar}]';
$output = Json::jsonConvertToArray($json);
print "S::E-JSON: $json: " . DgS::printAr($output) . "<br>";
print "S::E-JSON ERROR: " . Json::jsonGetLastError() . ": " . Json::jsonGetLastError(true) . "<br>";
print "S::E Validate: " . Json::jsonValidate($json) . ": " . Json::jsonGetLastError(true) . "<br>";

// direct
$json = '{"direct": "static function call"}';
$output = $json_class::jsonConvertToArray($json);
print "J/S::JSON: $json: " . DgS::printAr($output) . "<br>";
print "J/S::JSON ERROR: " . $json_class::jsonGetLastError() . ": " . $json_class::jsonGetLastError(true) . "<br>";

$json = '["f: {b"""ar}]';
$output = $json_class::jsonConvertToArray($json);
print "J/S::E-JSON: $json: " . DgS::printAr($output) . "<br>";
print "J/S::E-JSON ERROR: " . $json_class::jsonGetLastError() . ": " . $json_class::jsonGetLastError(true) . "<br>";

echo "<hr>";
$json = '{"valid":"json","invalid":"\xB1\x31"}';
$json = '{"valid":"json","invalid":"abc\x80def"}';
$output_no_flag = Json::jsonConvertToArray($json);
print "No Flag JSON: $json: " . DgS::printAr($output_no_flag) . "<br>";
print "No Flag JSON ERROR: " . Json::jsonGetLastError() . ": " . Json::jsonGetLastError(true) . "<br>";
$output_flag = Json::jsonConvertToArray($json, flags:JSON_INVALID_UTF8_IGNORE);
print "No Flag JSON: $json: " . DgS::printAr($output_flag) . "<br>";
print "No Flag JSON ERROR: " . Json::jsonGetLastError() . ": " . Json::jsonGetLastError(true) . "<br>";
$output_raw = json_decode($json, true, flags:JSON_INVALID_UTF8_IGNORE);
print "No Flag JSON RAW (F-1): $json: " . DgS::printAr($output_raw) . "<br>";
$output_raw = json_decode($json, true, flags:JSON_INVALID_UTF8_SUBSTITUTE);
print "No Flag JSON RAW (F-2): $json: " . DgS::printAr($output_raw) . "<br>";
$output_raw = json_decode($json, true);
print "No Flag JSON RAW: $json: " . DgS::printAr($output_raw) . "<br>";
echo "<hr>";


// $json = '{"foo": "bar"}';
// $output = Jason::jsonConvertToArray($json);
// print "S::JSON: $json: " . DgS::printAr($output) . "<br>";
// print "S::JSON ERROR: " . Jason::jsonGetLastError() . ": " . Jason::jsonGetLastError(true) . "<br>";

// convert an array to json
$array = ['foo' => 'bar'];
$output = Json::jsonConvertArrayTo($array);
print "S::JSON: " . DgS::printAr($array) . " => " . $output . "<br>";
$array = ['foo' => 'bar', 'sub' => ['other' => 'this', 'foo' => 'bar', 'set' => [12, 34, true]]];
print "Pretty: <pre>" . Json::jsonPrettyPrint($array) . "</pre><br>";

print "</body></html>";

// __END__
