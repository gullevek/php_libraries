<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-login';
$SET_SESSION_NAME = EDIT_SESSION_NAME;
// init login & backend class
$session = new CoreLibs\Create\Session($SET_SESSION_NAME);
$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$login = new CoreLibs\ACL\Login(
	$db,
	$log,
	$session,
	[
		'auto_login' => false,
		'default_acl_level' => DEFAULT_ACL_LEVEL,
		'logout_target' => LOGOUT_TARGET,
		'site_locale' => SITE_LOCALE,
		'site_domain' => SITE_DOMAIN,
		'locale_path' => BASE . INCLUDES . LOCALE,
	]
);
ob_end_flush();
$login->loginMainCall();

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
echo "ACL (MIN): " . \CoreLibs\Debug\Support::printAr($login->loginGetAcl()['min'] ?? []) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";
