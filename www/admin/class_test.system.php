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
$LOG_FILE_ID = 'classTest-system';
ob_end_flush();

use CoreLibs\Get\System;
use CoreLibs\Debug\Support as DgS;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: SYSTEM';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "System::getHostName():<br>";
print "GETHOSTNAME: " . DgS::printAr(System::getHostName()) . "<br>";
print "System::getPageName():<br>";
print "GETPAGENAME(0): " . System::getPageName() . "<br>";
print "GETPAGENAME(1): " . System::getPageName(System::NO_EXTENSION) . "<br>";
print "GETPAGENAME(2): " . System::getPageName(System::FULL_PATH) . "<br>";
print "System::getPageNameArray():<br>";
print "GETPAGENAMEARRAY: " . \CoreLibs\Debug\Support::printAr(System::getPageNameArray()) . "<br>";
// seting errro codes file upload
print "System::fileUploadErrorMessage():<br>";
print "FILEUPLOADERRORMESSAGE(): " . System::fileUploadErrorMessage(-1) . "<br>";
print "FILEUPLOADERRORMESSAGE(UPLOAD_ERR_CANT_WRITE): "
	. System::fileUploadErrorMessage(UPLOAD_ERR_CANT_WRITE) . "<br>";

print "System::checkCLI():<br>";
print "Are we in an CLI: " . (System::checkCLI() ? 'Yes' : 'No') . "<br>";

print "</body></html>";
