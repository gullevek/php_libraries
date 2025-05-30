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
$LOG_FILE_ID = 'classTest-hash';
ob_end_flush();

use CoreLibs\Create\Hash;
use CoreLibs\Security\CreateKey;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$hash_class = 'CoreLibs\Create\Hash';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: HASH';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$to_crc = 'Some text block';
// static
print "S::__CRC32B: $to_crc: " . Hash::__crc32b($to_crc) . "<br>";
// print "S::__SHA1SHORT(off): $to_crc: " . Hash::__sha1short($to_crc) . "<br>";
print "S::hashShort(__sha1Short replace): $to_crc: " . Hash::hashShort($to_crc) . "<br>";
// print "S::__SHA1SHORT(on): $to_crc: " . Hash::__sha1short($to_crc, true) . "<br>";
print "S::sha1Short(__sha1Short replace): $to_crc: " . Hash::sha1Short($to_crc) . "<br>";
// print "S::__hash(d): " . $to_crc . "/"
// 	. Hash::STANDARD_HASH_SHORT . ": " . $hash_class::__hash($to_crc) . "<br>";
$to_crc_list = [
	'Some text block',
	'Some String Text',
	'any string',
];
foreach ($to_crc_list as $__to_crc) {
	foreach (['adler32', 'fnv132', 'fnv1a32', 'joaat', 'ripemd160', 'sha256', 'sha512'] as $__hash_c) {
		print "Hash::hash($__hash_c): $__to_crc: " . Hash::hash($to_crc, $__hash_c) . "<br>";
	}
}
// static use
print "U-S::__CRC32B: $to_crc: " . Hash::__crc32b($to_crc) . "<br>";

echo "<hr>";
$text = 'Some String Text';
// $text = 'any string';
$type = 'crc32b';
print "Hash: " . $type . ": " . hash($type, $text) . "<br>";
// print "Class (old): " . $type . ": " . Hash::__hash($text, $type) . "<br>";
print "Class (new): " . $type . ": " . Hash::hash($text, $type) . "<br>";

echo "<hr>";
print "CURRENT STANDARD_HASH_SHORT: " . Hash::STANDARD_HASH_SHORT . "<br>";
print "CURRENT STANDARD_HASH_LONG: " . Hash::STANDARD_HASH_LONG . "<br>";
print "CURRENT STANDARD_HASH: " . Hash::STANDARD_HASH . "<br>";
print "HASH SHORT: " . $to_crc . ": " . Hash::hashShort($to_crc) . "<br>";
print "HASH LONG: " . $to_crc . ": " . Hash::hashLong($to_crc) . "<br>";
print "HASH DEFAULT: " . $to_crc . ": " . Hash::hashStd($to_crc) . "<br>";

echo "<hr>";
$key = CreateKey::generateRandomKey();
$key = "FIX KEY";
print "Secret Key: " . $key . "<br>";
print "HASHMAC DEFAULT (fix): " . $to_crc . ": " . Hash::hashHmac($to_crc, $key) . "<br>";
$key = CreateKey::generateRandomKey();
print "Secret Key: " . $key . "<br>";
print "HASHMAC DEFAULT (random): " . $to_crc . ": " . Hash::hashHmac($to_crc, $key) . "<br>";

echo "<hr>";
$hash_types = ['crc32b', 'sha256', 'invalid'];
foreach ($hash_types as $hash_type) {
	echo "<b>Checking $hash_type:</b><br>";
	if (Hash::isValidHashType($hash_type)) {
		echo "hash type: $hash_type is valid<br>";
	} else {
		echo "hash type: $hash_type is INVALID<br>";
	}
	if (Hash::isValidHashHmacType($hash_type)) {
		echo "hash hmac type: $hash_type is valid<br>";
	} else {
		echo "hash hmac type: $hash_type is INVALID<br>";
	}
}

// print "UNIQU ID SHORT : " . Hash::__uniqId() . "<br>";
// print "UNIQU ID LONG : " . Hash::__uniqIdLong() . "<br>";

print "</body></html>";

// __END__
