<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

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

$basic = new CoreLibs\Basic();

print "<html><head><title>TEST CLASS: HTML/ELEMENTS</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// regex get
print "S::GETEMAILREGEX(0): ".Email::getEmailRegex(0)."<br>";
print "S::GETEMAILREGEX(2): ".Email::getEmailRegex(2)."<br>";
print "S::GETEMAILREGEX(7): ".Email::getEmailRegex(7)."<br>";
print "S::GETEMAILREGEX(8 invalid): ".Email::getEmailRegex(8)."<br>";
print "S::GETEMAILREGEXCHECK: ".$basic->printAr(Email::getEmailRegexCheck())."<br>";

$email = [
	'foo@bar.org',
	'foo@i.softbank.ne.jp'
];
foreach ($email as $s_email) {
	print "S::EMAIL: $s_email: ".Email::getEmailType($s_email)."<br>";
	print "S::EMAIL SHORT: $s_email: ".Email::getEmailType($s_email, true)."<br>";
}
// DEPRECATED
/* foreach ($email as $s_email) {
	print "D/S-EMAIL: $s_email: ".$basic->getEmailType($s_email)."<br>";
	print "D/S-EMAIL SHORT: $s_email: ".$basic->getEmailType($s_email, true)."<br>";
} */

// error message
print $basic->printErrorMsg();

print "</body></html>";

// __END__
