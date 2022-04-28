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
$LOG_FILE_ID = 'classTest-system';
ob_end_flush();

use CoreLibs\Get\System;
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
print "<html><head><title>TEST CLASS: SYSTEM</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "GETHOSTNAME: " . DgS::printAr(System::getHostName()) . "<br>";
print "GETPAGENAME(0): " . System::getPageName() . "<br>";
print "GETPAGENAME(1): " . System::getPageName(System::NO_EXTENSION) . "<br>";
print "GETPAGENAME(2): " . System::getPageName(System::FULL_PATH) . "<br>";
print "GETPAGENAMEARRAY: " . \CoreLibs\Debug\Support::printAr(System::getPageNameArray()) . "<br>";
// seting errro codes file upload
print "FILEUPLOADERRORMESSAGE(): " . System::fileUploadErrorMessage(-1) . "<br>";
print "FILEUPLOADERRORMESSAGE(UPLOAD_ERR_CANT_WRITE): "
	. System::fileUploadErrorMessage(UPLOAD_ERR_CANT_WRITE) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";
