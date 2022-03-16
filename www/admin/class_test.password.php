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
$LOG_FILE_ID = 'classTest-pass';
ob_end_flush();

use CoreLibs\Check\Password as PwdChk;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
$_password = new CoreLibs\Check\Password();
$password_class = 'CoreLibs\Check\Password';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: PASSWORD</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

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

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
