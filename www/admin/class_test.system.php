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
$LOG_FILE_ID = 'classTest-system';
ob_end_flush();

use CoreLibs\Get\System;

$basic = new CoreLibs\Basic();

print "<html><head><title>TEST CLASS: SYSTEM</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "GETHOSTNAME: ".$basic->printAr(System::getHostName())."<br>";
print "GETPAGENAME(0): ".System::getPageName()."<br>";
print "GETPAGENAME(1): ".System::getPageName(1)."<br>";
print "GETPAGENAME(2): ".System::getPageName(2)."<br>";
// seting errro codes file upload
print "FILEUPLOADERRORMESSAGE(): ".System::fileUploadErrorMessage(-1)."<br>";
print "FILEUPLOADERRORMESSAGE(UPLOAD_ERR_CANT_WRITE): ".System::fileUploadErrorMessage(UPLOAD_ERR_CANT_WRITE)."<br>";

// error message
print $basic->printErrorMsg();

print "</body></html>";
