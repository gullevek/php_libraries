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
$LOG_FILE_ID = 'classTest-readEnvFile';
ob_end_flush();

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? true,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? true,
]);
$ref_class = 'CoreLibs\Get\ReadEnvFile';

$PAGE_NAME = 'TEST CLASS: READ ENV FILE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "ALREADY from config.php: " . \CoreLibs\Debug\Support::printAr($_ENV) . "<br>";

// test .env in local
$status = \CoreLibs\Get\DotEnv::readEnvFile('.', 'test.env');
print "test.env: STATUS: " . $status . "<br>";
print "AFTER reading test.env file: " . \CoreLibs\Debug\Support::printAr($_ENV) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
