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

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$ref_class = 'CoreLibs\Get\ReadEnvFile';

$PAGE_NAME = 'TEST CLASS: READ ENV FILE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "ALREADY from config.php: " . \CoreLibs\Debug\Support::printAr($_ENV) . "<br>";

// This is now in \gullevek\dotenv\DotEnv::readEnvFile(...)

// test .env in local
/* $status = \CoreLibs\Get\DotEnv::readEnvFile('.', 'test.env');
print "test.env: STATUS: " . $status . "<br>";
print "AFTER reading test.env file: " . \CoreLibs\Debug\Support::printAr($_ENV) . "<br>"; */

print "</body></html>";
// ;;

// __END__
