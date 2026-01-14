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
print "Combined strings C: "
	. Strings::buildCharStringFromLists([
		0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H', 8 => 'I', 9 => 'J', 10 => 'K',
		11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O', 15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U',
		21 => 'V', 22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b', 28 => 'c', 29 => 'd', 30 => 'e',
		31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i', 35 => 'j', 36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o',
		41 => 'p', 42 => 'q', 43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w', 49 => 'x', 50 => 'y',
		51 => 'z', 52 => '0', 53 => '1', 54 => '2', 55 => '3', 56 => '4', 57 => '5', 58 => '6', 59 => '7', 60 => '8',
		61 => '9'
	]) . "<br>";

$input_string = "AaBbCc";
print "Unique: " . Strings::removeDuplicates($input_string) . "<br>";
print "Unique: " . Strings::removeDuplicates(strtolower($input_string)) . "<br>";

$regex_string = "/^[A-z]$/";
print "Regex is: " . $regex_string . ": " . DgS::prBl(Strings::isValidRegex($regex_string)) . "<br>";
$regex_string = "'//test{//'";
print "Regex is: " . $regex_string . ": " . DgS::prBl(Strings::isValidRegex($regex_string)) . "<br>";
print "Regex is: " . $regex_string . ": " . DgS::printAr(Strings::validateRegex($regex_string)) . "<br>";
$regex_string = "/^[A-z";
print "Regex is: " . $regex_string . ": " . DgS::prBl(Strings::isValidRegex($regex_string)) . "<br>";
print "[A] LAST PREGE ERROR: " . preg_last_error() . " -> "
	. (Strings::PREG_ERROR_MESSAGES[preg_last_error()] ?? '-') . "<br>";
$preg_error = Strings::isValidRegex($regex_string);
print "[B] LAST PREGE ERROR: " . preg_last_error() . " -> "
	. Strings::getLastRegexErrorString() . " -> " . preg_last_error_msg() . "<br>";

$base_strings = [
	'abcddfff',
	'A-Z',
	'a-z',
	'A-Za-z',
	'A-Df-g',
	'A-D0-9',
	'D-A7-0',
	'A-FB-G',
	'0-9',
	'あ-お',
	'ア-オ',
];
foreach ($base_strings as $string) {
	try {
		$parsed = Strings::parseCharacterRanges($string);
		print "Parsed ranges for '$string': " . DgS::printAr($parsed) . "<br>";
	} catch (\InvalidArgumentException $e) {
		print "Error parsing ranges for '$string': " . $e->getMessage() . "<br>";
	}
}

print "</body></html>";

// __END__
