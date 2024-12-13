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
$LOG_FILE_ID = 'classTest-encryption';
ob_end_flush();

use CoreLibs\Security\SymmetricEncryption;
use CoreLibs\Security\CreateKey;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: ENCRYPTION';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$key = CreateKey::generateRandomKey();
print "Secret Key: " . $key . "<br>";

$string = "I a some deep secret";
//
$crypt = new SymmetricEncryption($key);
$encrypted = $crypt->encrypt($string);
$decrypted = $crypt->decrypt($encrypted);
print "[C] Encrypted: " . $encrypted . "<br>";
print "[C] Decrytped: " . $decrypted . "<br>";
$encrypted = SymmetricEncryption::getInstance($key)->encrypt($string);
$decrypted = SymmetricEncryption::getInstance($key)->decrypt($encrypted);
print "[S] Original: " . $string . "<br>";
print "[S] Encrypted: " . $encrypted . "<br>";
print "[S] Decrytped: " . $decrypted . "<br>";
$encrypted = SymmetricEncryption::encryptKey($string, $key);
$decrypted = SymmetricEncryption::decryptKey($encrypted, $key);
print "[SS] Encrypted: " . $encrypted . "<br>";
print "[SS] Decrytped: " . $decrypted . "<br>";

print "<br>INIT KEY MISSING<br>";
try {
	$crypt = new SymmetricEncryption();
	$encrypted = $crypt->decrypt($string);
} catch (Exception $e) {
	print("Error: " . $e->getMessage() . "<br>");
}

print "<br>WRONG CIPHERTEXT<br>";
try {
	$decrypted = SymmetricEncryption::decryptKey('flupper', $key);
} catch (Exception $e) {
	print "Error: " . $e->getMessage() . "<br>";
}

print "<br>SHORT and WRONG KEY<br>";
$key = 'wrong_key';
try {
	$encrypted = SymmetricEncryption::encryptKey($string, $key);
} catch (Exception $e) {
	print "Error: " . $e->getMessage() . "<br>";
}

print "<br>INVALID HEX KEY<br>";
$key = '1cabd5cba9e042f12522f4ff2de5c31d233b';
try {
	$encrypted = SymmetricEncryption::encryptKey($string, $key);
} catch (Exception $e) {
	print "Error: " . $e->getMessage() . "<br>";
}

print "<br>WRONG KEY TO DECRYPT<br>";
$key = CreateKey::generateRandomKey();
$string = "I a some deep secret";
$encrypted = SymmetricEncryption::encryptKey($string, $key);
$key = 'wrong_key';
try {
	$decrypted = SymmetricEncryption::decryptKey($encrypted, $key);
} catch (Exception $e) {
	print "Error: " . $e->getMessage() . "<br>";
}

// echo "<hr>";
// $key = CreateKey::generateRandomKey();
// $se = new SymmetricEncryption($key);
// $string = "I a some deep secret";
// $encrypted = $se->encrypt($string);
// $decrypted = $se->decrypt($encrypted);

print "</body></html>";

// __END__
