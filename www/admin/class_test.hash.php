<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-hash';
ob_end_flush();

use CoreLibs\Create\Hash;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$hash_class = 'CoreLibs\Create\Hash';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HASH';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$to_crc = 'Some text block';
// static
print "S::__CRC32B: $to_crc: " . $hash_class::__crc32b($to_crc) . "<br>";
print "S::__SHA1SHORT(off): $to_crc: " . $hash_class::__sha1short($to_crc) . "<br>";
print "S::__SHA1SHORT(on): $to_crc: " . $hash_class::__sha1short($to_crc, true) . "<br>";
print "S::__hash(d): " . $to_crc . "/"
	. Hash::STANDARD_HASH_SHORT . ": " . $hash_class::__hash($to_crc) . "<br>";
foreach (['adler32', 'fnv132', 'fnv1a32', 'joaat'] as $__hash_c) {
	print "S::__hash($__hash_c): $to_crc: " . $hash_class::__hash($to_crc, $__hash_c) . "<br>";
}
// static use
print "U-S::__CRC32B: $to_crc: " . Hash::__crc32b($to_crc) . "<br>";

print "<br>CURRENT STANDARD_HASH_SHORT: " . Hash::STANDARD_HASH_SHORT . "<br>";
print "<br>CURRENT STANDARD_HASH_LONG: " . Hash::STANDARD_HASH_LONG . "<br>";
print "HASH SHORT: " . $to_crc . ": " . Hash::__hash($to_crc) . "<br>";
print "HASH LONG: " . $to_crc . ": " . Hash::__hashLong($to_crc) . "<br>";

// print "UNIQU ID SHORT : " . Hash::__uniqId() . "<br>";
// print "UNIQU ID LONG : " . Hash::__uniqIdLong() . "<br>";

print "</body></html>";

// __END__
