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
$LOG_FILE_ID = 'classTest-email';
ob_end_flush();

use CoreLibs\Check\Email;
use CoreLibs\Debug\Support as DgS;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: HTML/ELEMENTS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

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

print "</body></html>";

// __END__
