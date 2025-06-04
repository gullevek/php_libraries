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

use CoreLibs\Convert\Strings;
use CoreLibs\Debug\Support as DgS;

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
$split_length = 4;
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
	print "A) Convert: $string with $split to: "
		. Strings::splitFormatString($string, $split)
		. "<br>";
	try {
		print "B) Convert: $string with $split_length to: "
			. Strings::splitFormatStringFixed($string, $split_length)
			. "<br>";
	} catch (Exception $e) {
		print "Split not possible: " . $e->getMessage() . "<br>";
	}
}

$split = '2_2';
$split_length = 2;
$string = '1234';
print "A) Convert: $string with $split to: "
	. Strings::splitFormatString($string, $split)
	. "<br>";
print "B) Convert: $string with $split_length to: "
	. Strings::splitFormatStringFixed($string, $split_length, "_")
	. "<br>";
$split = '2-2';
$string = 'あいうえ';
try {
	print "Convert: $string with $split to: "
		. Strings::splitFormatString($string, $split)
		. "<br>";
} catch (\Exception $e) {
	print "Cannot split string: " . $e->getMessage() . "<br>";
}
print "B) Convert: $string with $split_length to: "
	. Strings::splitFormatStringFixed($string, $split_length, "-")
	. "<br>";

$string = 'ABCD12345568ABC13';
$format = '2-4_5-2#4';
$output = 'AB-CD12_34556-8A#BC13';
print "A) Convert: $string with $format to: "
	. Strings::splitFormatString($string, $format)
	. "<br>";

// try other split calls
$string = "ABCDE";
$split_length = 2;
$split_char = "-=-";
print "Convert: $string with $split_length / $split_char to: "
	. Strings::splitFormatStringFixed($string, $split_length, $split_char)
	. "<br>";
$string = "あいうえお";
$split_length = 2;
$split_char = "-=-";
print "Convert: $string with $split_length / $split_char to: "
	. Strings::splitFormatStringFixed($string, $split_length, $split_char)
	. "<br>";

$test_splits = [
	'',
	'2',
	'2-2',
	'2-3-4',
];
foreach ($test_splits as $split) {
	print "$split with count: " . Strings::countSplitParts($split) . "<br>";
}

// check char list in list
$needle = "abc";
$haystack = "abcdefg";
print "Needle: " . $needle . ", Haysteck: " . $haystack . ": "
	. DgS::prBl(Strings::allCharsInSet($needle, $haystack)) . "<br>";
$needle = "abcz";
print "Needle: " . $needle . ", Haysteck: " . $haystack . ": "
	. DgS::prBl(Strings::allCharsInSet($needle, $haystack)) . "<br>";

print "Combined strings A: "
	. Strings::buildCharStringFromLists(['A', 'B', 'C'], ['0', '1', '2']) . "<br>";
print "Combined strings B: "
	. Strings::buildCharStringFromLists([['F'], ['G'], 'H'], [['5', ['6']], ['0'], '1', '2']) . "<br>";

$input_string = "AaBbCc";
print "Unique: " . Strings::removeDuplicates($input_string) . "<br>";
print "Unique: " . Strings::removeDuplicates(strtolower($input_string)) . "<br>";

$regex_string = "/^[A-z]$/";
print "Regex valid: " . $regex_string . ": "
	. DgS::prBl(Strings::isValidRegexSimple($regex_string)) . "<br>";
$regex_string = "/^[A-z";
print "Regex valid: " . $regex_string . ": "
	. DgS::prBl(Strings::isValidRegexSimple($regex_string)) . "<br>";

print "</body></html>";

// __END__
