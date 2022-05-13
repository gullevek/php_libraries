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
$LOG_FILE_ID = 'classTest-login';
// init login & backend class
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
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$login = new CoreLibs\ACL\Login($db, $log);
ob_end_flush();

$PAGE_NAME = 'TEST CLASS: LOGIN';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

echo "CHECK PERMISSION: " . ($login->loginCheckPermissions() ? 'OK' : 'BAD') . "<br>";
echo "IS ADMIN: " . ($login->loginIsAdmin() ? 'OK' : 'BAD') . "<br>";
echo "MIN ACCESS BASE: " . ($login->loginCheckAccessBase('admin') ? 'OK' : 'BAD') . "<br>";
echo "MIN ACCESS PAGE: " . ($login->loginCheckAccessPage('admin') ? 'OK' : 'BAD') . "<br>";

echo "ACL: " . \CoreLibs\Debug\Support::printAr($login->loginGetAcl()) . "<br>";
echo "ACL (MIN): " . \CoreLibs\Debug\Support::printAr($login->loginGetAcl()['min']) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";
