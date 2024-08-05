<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest_class_calls';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);

// define a list of from to color sets for conversion test

$PAGE_NAME = 'TEST CLASS: CLASS CALLS';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$test = new \TestCalls\Test($db, $log);

$test->testDbCall();

print "</body></html>";

// __END__
