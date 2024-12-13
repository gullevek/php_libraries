<?php // phpcs:ignore PSR1.Files.SideEffects

// this is an empty test page for login tests only

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest';
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
		'auto_login' => true,
		'default_acl_level' => DEFAULT_ACL_LEVEL,
		'logout_target' => '',
		'site_locale' => SITE_LOCALE,
		'site_domain' => SITE_DOMAIN,
		'site_encoding' => SITE_ENCODING,
		'locale_path' => BASE . INCLUDES . LOCALE,
	]
);
$locale = $login->loginGetLocale();
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
	$locale['encoding']
);

print "<!DOCTYPE html>";
print "<html><head><title>GROUP TESTER</title></head>";
print "<body>";

print '<form method="post" name="loginlogout">';
print '<a href="javascript:document.loginlogout.login_logout.value=\'Logou\';'
	. 'document.loginlogout.submit();">Logout</a>';
print '<input type="hidden" name="login_logout" value="">';
print '</form>';

print "<h1>TEST Login</h1>";

print "</body></html>";

// __END__
