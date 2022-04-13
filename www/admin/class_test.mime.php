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
$LOG_FILE_ID = 'classTest-mime';
ob_end_flush();

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
$_mime = new CoreLibs\Convert\MimeAppName();

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS: MIME</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$mime = 'application/illustrator';
print "MIME $mime: " . $_mime->mimeGetAppName($mime) . "<br>";
$mime = 'fake/mime';
$_mime->mimeSetAppName($mime, 'This is a fake mime');
print "MIME $mime: " . $_mime->mimeGetAppName($mime) . "<br>";

// mime test
$mime = 'application/vnd.ms-excel';
print "App for mime $mime: " . $_mime->mimeGetAppName($mime) . "<br>";
$_mime->mimeSetAppName($mime, 'Microsoft (better) Excel');
print "App for mime changed $mime: " . $_mime->mimeGetAppName($mime) . "<br>";

// static call test
$mime = 'application/x-indesign';
print "S::App for mime $mime: " . \CoreLibs\Convert\MimeAppName::mimeGetAppName($mime) . "<br>";
$mime = 'application/vnd.ms-excel';
print "S::App for mime $mime: " . \CoreLibs\Convert\MimeAppName::mimeGetAppName($mime) . "<br>";

print $log->printErrorMsg();

print "</body></html>";

// __END__
