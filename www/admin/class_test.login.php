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
$LOG_FILE_ID = 'classTest-login';
$SET_SESSION_NAME = EDIT_SESSION_NAME;
// init login & backend class
$session = new CoreLibs\Create\Session($SET_SESSION_NAME);
$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$login = new CoreLibs\ACL\Login(
	$db,
	$log,
	$session,
	[
		'auto_login' => false,
		'default_acl_level' => DEFAULT_ACL_LEVEL,
		'logout_target' => '',
		'site_locale' => SITE_LOCALE,
		'site_domain' => SITE_DOMAIN,
		'site_encoding' => SITE_ENCODING,
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
echo "LOCALE: " . \CoreLibs\Debug\Support::printAr($login->loginGetLocale()) . "<br>";

print "</body></html>";
