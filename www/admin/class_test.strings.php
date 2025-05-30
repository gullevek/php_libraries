<?php // phpcs:ignore warning

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-string';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$byte_class = 'CoreLibs\Convert\Strings';

$PAGE_NAME = 'TEST CLASS: STRINGS CONVERT';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$split = '4-4-4';
$test_strings = [
	'13',
	'1234',
	'12341',
	'12341234',
	'123412341',
	'123412341234',
	'1234123412341234512345',
];

foreach ($test_strings as $string) {
	print "Convert: $string with $split to: "
		. \CoreLibs\Convert\Strings::splitFormatString($string, $split)
		. "<br>";
}

$split = '2_2';
$string = '1234';
print "Convert: $string with $split to: "
	. \CoreLibs\Convert\Strings::splitFormatString($string, $split)
	. "<br>";
$split = '2-2';
$string = 'あいうえ';
print "Convert: $string with $split to: "
	. \CoreLibs\Convert\Strings::splitFormatString($string, $split)
	. "<br>";

$test_splits = [
	'',
	'2',
	'2-2',
	'2-3-4',
];
foreach ($test_splits as $split) {
	print "$split with count: " . \CoreLibs\Convert\Strings::countSplitParts($split) . "<br>";
}

print "</body></html>";

// __END__
