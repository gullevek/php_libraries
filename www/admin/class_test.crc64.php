<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-crc64';
ob_end_flush();

use CoreLibs\Create\Crc64;
use CoreLibs\Debug\RunningTime;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: CRC64';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';


$file = getcwd() . DIRECTORY_SEPARATOR . 'class_test.file.php';
print "CRC64: $file: " . Crc64::crc64($file) . "<br>";
print "CRC64HEX: $file: " . Crc64::crc64hex($file) . "<br>";

// big file check including speed
$base_path = __DIR__ . DIRECTORY_SEPARATOR
	. '..' . DIRECTORY_SEPARATOR
	. '..' . DIRECTORY_SEPARATOR
	. '4dev' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR .
	'Create' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
$max_size = 1024 * 1024 * 10;
# open the base path and loop over each file in there
$files = glob($base_path . '*');
if (!empty($files)) {
	foreach ($files as $file) {
		if (!is_file($file)) {
			continue;
		}
		if (filesize($file) > $max_size) {
			print "Skipping large file: $file<br>";
			continue;
		}
		RunningTime::runningTime();
		print "CRC64: $file: " . Crc64::crc64($file) . "<br>";
		RunningTime::runningTime();
		print "Running Time: $file: " . RunningTime::runningTimeString() . "<br>";
		print "Running Time: $file: " . RunningTime::hrRunningTime() . "<br>";
		print "CRC64HEX: $file: " . Crc64::crc64hex($file) . "<br>";
		RunningTime::runningTime();
		print "Running Time: $file: " . RunningTime::runningTimeString() . "<br>";
		print "Running Time: $file: " . RunningTime::hrRunningTime() . "<br>";
		RunningTime::hrRunningTimeReset();
	}
} else {
	print "No files found in base path: $base_path<br>";
}

print "</body></html>";

// __END__
