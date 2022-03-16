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
$LOG_FILE_ID = 'classTest-phpv';
ob_end_flush();

use CoreLibs\Check\PhpVersion;

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
$_phpv = new CoreLibs\Check\PhpVersion();
$phpv_class = 'CoreLibs\Check\PhpVersion';

// define a list of from to color sets for conversion test

print "<html><head><title>TEST CLASS: PHP VERSION</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$min_version_s = '7';
$min_version_ss = '7.1';
$min_version = '7.1.0';
$max_version = '7.3.1';
$max_version_ss = '8.1';

print "CURRENT: " . phpversion() . "<br>";
// class
print "MIN: $min_version: " . (string)$_phpv->checkPHPVersion($min_version) . "<br>";
print "MIN/MAX: $min_version/$max_version: " . (string)$_phpv->checkPHPVersion($min_version, $max_version) . "<br>";
print "MIN/S: $min_version_s: " . (string)$_phpv->checkPHPVersion($min_version_s) . "<br>";
print "MIN/SS: $min_version_ss: " . (string)$_phpv->checkPHPVersion($min_version_ss) . "<br>";
// static
print "S::MIN: $min_version: " . (string)$phpv_class::checkPHPVersion($min_version) . "<br>";
print "S::MIN/MAX: $min_version/$max_version: "
	. (string)$phpv_class::checkPHPVersion($min_version, $max_version) . "<br>";
print "S::MIN/S: $min_version_s: " . (string)$phpv_class::checkPHPVersion($min_version_s) . "<br>";
print "S::MIN/SS: $min_version_ss: " . (string)$phpv_class::checkPHPVersion($min_version_ss) . "<br>";
print "S::MAX $max_version_ss: " . (string)$phpv_class::checkPHPVersion(null, $max_version_ss) . "<br>";
// use stats
print "U-S::MIN: $min_version: " . (string)PhpVersion::checkPHPVersion($min_version) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
