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
$LOG_FILE_ID = 'classTest-mime';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$_mime = new CoreLibs\Convert\MimeAppName();

$PAGE_NAME = 'TEST CLASS: MIME';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

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

print "</body></html>";

// __END__
