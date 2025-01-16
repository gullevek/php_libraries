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
$LOG_FILE_ID = 'classTest-password';
ob_end_flush();

use CoreLibs\Security\Password as PwdChk;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$_password = new CoreLibs\Security\Password();
$password_class = 'CoreLibs\Security\Password';

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: PASSWORD';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "PHP Version: " . PHP_VERSION . "<br>";

$password = 'something1234';
$enc_password = $_password->passwordSet($password);
print "PASSWORD: $password: " . $enc_password . "<br>";
print "PASSWORD VERIFY: " . (string)$_password->passwordVerify($password, $enc_password) . "<br>";
print "PASSWORD REHASH: " . (string)$_password->passwordRehashCheck($enc_password) . "<br>";
// static verify
$password = 'othername7890';
$enc_password = $password_class::passwordSet($password);
print "PASSWORD: $password: " . $enc_password . "<br>";
print "S-PASSWORD VERIFY: " . (string)$password_class::passwordVerify($password, $enc_password) . "<br>";
print "PASSWORD REHASH: " . (string)$password_class::passwordRehashCheck($enc_password) . "<br>";
// direct static
print "S::PASSWORD VERFIY: " . (string)PwdChk::passwordVerify($password, $enc_password) . "<br>";

if (PHP_VERSION_ID < 80400) {
	$rehash_test = '$2y$10$EgWJ2WE73DWi.hIyFRCdpejLXTvHbmTK3LEOclO1tAvXAXUNuUS4W';
	$rehash_test_throw = '$2y$12$EgWJ2WE73DWi.hIyFRCdpejLXTvHbmTK3LEOclO1tAvXAXUNuUS4W';
} else {
	$rehash_test = '$2y$12$EgWJ2WE73DWi.hIyFRCdpejLXTvHbmTK3LEOclO1tAvXAXUNuUS4W';
	$rehash_test_throw = '$2y$10$EgWJ2WE73DWi.hIyFRCdpejLXTvHbmTK3LEOclO1tAvXAXUNuUS4W';
}
if (PwdChk::passwordRehashCheck($rehash_test)) {
	print "Bad password [BAD]<br>";
}
if (PwdChk::passwordRehashCheck($rehash_test_throw)) {
	print "Bad password [OK]<br>";
}

print "</body></html>";

// __END__
