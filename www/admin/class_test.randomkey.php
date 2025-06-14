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
