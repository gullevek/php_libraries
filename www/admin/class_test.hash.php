<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-hash';
ob_end_flush();

use CoreLibs\Create\Hash;

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

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
