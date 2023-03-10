<?php // phpcs:ignore warning

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
$LOG_FILE_ID = 'classTest-string';
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
$byte_class = 'CoreLibs\Convert\Strings';

$PAGE_NAME = 'TEST CLASS: STRINGS CONVERT';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
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

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
