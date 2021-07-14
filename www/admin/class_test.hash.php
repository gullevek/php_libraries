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
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-hash';
ob_end_flush();

use CoreLibs\Create\Hash;

$basic = new CoreLibs\Basic();
$hash_class = 'CoreLibs\Create\Hash';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: HASH</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$to_crc = 'Some text block';
// static
print "S::__CRC32B: $to_crc: " . $hash_class::__crc32b($to_crc) . "<br>";
print "S::__SHA1SHORT(off): $to_crc: " . $hash_class::__sha1short($to_crc) . "<br>";
print "S::__SHA1SHORT(on): $to_crc: " . $hash_class::__sha1short($to_crc, true) . "<br>";
print "S::__hash(d): $to_crc: " . $hash_class::__hash($to_crc) . "<br>";
foreach (['adler32', 'fnv132', 'fnv1a32', 'joaat'] as $__hash_c) {
	print "S::__hash($__hash_c): $to_crc: " . $hash_class::__hash($to_crc, $__hash_c) . "<br>";
}
// static use
print "U-S::__CRC32B: $to_crc: " . Hash::__crc32b($to_crc) . "<br>";

// DEPRECATED
/* print "D/__CRC32B: $to_crc: ".$basic->__crc32b($to_crc)."<br>";
print "D/__SHA1SHORT(off): $to_crc: ".$basic->__sha1short($to_crc)."<br>";
print "D/__hash(d): $to_crc: ".$basic->__hash($to_crc)."<br>"; */

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
