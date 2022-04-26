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
require '../configs/config.php';
// define log file id
$LOG_FILE_ID = 'classTest-config-direct';
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

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS: CONFIG DIRECT</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><b>CONFIG DIRECT</b></div>';

print "DIR: " . DIR . "<br>";
print "BASE: " . BASE . "<br>";
print "ROOT: " . ROOT . "<br>";
print "BASE NAME: " . BASE_NAME . "<br>";
echo "Config path prefix: " . $CONFIG_PATH_PREFIX . "<br>";
print "DB Name: " . DB_CONFIG_NAME . "<br>";
print "DB Config: " . \CoreLibs\Debug\Support::printAr(DB_CONFIG) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
