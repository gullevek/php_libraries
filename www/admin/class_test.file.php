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
$LOG_FILE_ID = 'classTest-file';
ob_end_flush();

use CoreLibs\Check\File;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: FILE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$file = '/some/path/to/some/file.txt';
print "GETFILENAMEENDING: $file: " . File::getFilenameEnding($file) . "<br>";
$file = getcwd() . DIRECTORY_SEPARATOR . 'class_test.file.php';
print "GETLINESFROMFILE: $file: " . File::getLinesFromFile($file) . "<br>";
print "MIMEINFO: $file: " . File::getMimeType($file) . "<br>";

print "</body></html>";

// __END__
