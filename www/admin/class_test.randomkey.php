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
$LOG_FILE_ID = 'classTest-randomkey';
ob_end_flush();

use CoreLibs\Create\RandomKey;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$array_class = 'CoreLibs\Create\RandomKey';

$PAGE_NAME = 'TEST CLASS: RANDOM KEY';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$output = RandomKey::randomKeyGen(
	12,
	[
		0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H', 8 => 'I', 9 => 'J', 10 => 'K',
		11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O', 15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U',
		21 => 'V', 22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b', 28 => 'c', 29 => 'd', 30 => 'e',
		31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i', 35 => 'j', 36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o',
		41 => 'p', 42 => 'q', 43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w', 49 => 'x', 50 => 'y',
		51 => 'z', 52 => '0', 53 => '1', 54 => '2', 55 => '3', 56 => '4', 57 => '5', 58 => '6', 59 => '7', 60 => '8',
		61 => '9'
	]
);
print "CUSTOM OUTPUT: " . $output . "<br>";

$key_length = 10;
$key_length_b = 5;
$key_lenght_long = 64;
print "S::RANDOMKEYGEN(auto): " . RandomKey::randomKeyGen() . "<br>";
// print "S::SETRANDOMKEYLENGTH($key_length): " . RandomKey::setRandomKeyLength($key_length) . "<br>";
print "S::RANDOMKEYGEN($key_length): " . RandomKey::randomKeyGen($key_length) . "<br>";
print "S::RANDOMKEYGEN($key_length_b): " . RandomKey::randomKeyGen($key_length_b) . "<br>";
print "S::RANDOMKEYGEN($key_length): " . RandomKey::randomKeyGen($key_length) . "<br>";
print "S::RANDOMKEYGEN($key_lenght_long): " . RandomKey::randomKeyGen($key_lenght_long) . "<br>";
print "S::RANDOMKEYGEN($key_lenght_long, list data): "
	. RandomKey::randomKeyGen($key_lenght_long, ['A', 'B', 'C'], ['7', '8', '9']) . "<br>";
print "S::RANDOMKEYGEN(auto): " . RandomKey::randomKeyGen() . "<br>";
print "===<Br>";
$_array = new CoreLibs\Create\RandomKey();
print "C->RANDOMKEYGEN(default): " . $_array->randomKeyGen() . "<br>";
print "===<Br>";
// CHANGE key characters
$_array = new CoreLibs\Create\RandomKey(['A', 'F', 'B'], ['1', '5', '9']);
print "C->RANDOMKEYGEN(pre set): " . $_array->randomKeyGen() . "<br>";


print "</body></html>";

// __END__
