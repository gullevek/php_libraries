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
$LOG_FILE_ID = 'classTest-email';
ob_end_flush();

use CoreLibs\Check\Email;
use CoreLibs\Debug\Support as DgS;

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

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS: HTML/ELEMENTS</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// regex get
print "S::GETEMAILREGEX(0): " . Email::getEmailRegex(0) . "<br>";
print "S::GETEMAILREGEX(2): " . Email::getEmailRegex(2) . "<br>";
print "S::GETEMAILREGEX(7): " . Email::getEmailRegex(7) . "<br>";
print "S::GETEMAILREGEX(8 invalid): " . Email::getEmailRegex(8) . "<br>";
print "S::GETEMAILREGEXCHECK: " . DgS::printAr(Email::getEmailRegexCheck()) . "<br>";
print "S::GETEMAILREGEXERRORMESSAGE " . Dgs::printAr(Email::getEmailRegexErrorMessage(1)) . "<br>";

$email = [
	'foo@bar.org',
	'foo@i.softbank.ne.jp'
];
foreach ($email as $s_email) {
	print "S::EMAIL: $s_email: " . Email::getEmailType($s_email) . "<br>";
	print "S::EMAIL SHORT: $s_email: " . Email::getEmailType($s_email, true) . "<br>";
}
$email = [
	'test@test.com',
	'',
	'-@-',
	'.test@test.com',
	'test@t_est.com',
	'test@@test.com',
	'test@test..com',
	'test@@test..com',
	'test@test.',
	'test@test.j',
];
foreach ($email as $s_email) {
	print "S::CHECKEMAIL: " . $s_email . ": " . (Email::checkEmail($s_email) ? 'Yes' : 'No') . "<br>";
	print "S::CHECKEMAILFULL: " . $s_email . ": " . Dgs::printAr(Email::checkEmailFull($s_email)) . "<br>";
	print "S::CHECKEMAILFULL(true): " . $s_email . ": " . Dgs::printAr(Email::checkEmailFull($s_email, true)) . "<br>";
}

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
