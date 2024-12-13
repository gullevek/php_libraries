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
$LOG_FILE_ID = 'classTest-login';
$SET_SESSION_NAME = EDIT_SESSION_NAME;

use CoreLibs\Debug\Support;

// init login & backend class
$session = new CoreLibs\Create\Session($SET_SESSION_NAME, [
	'regenerate' => 'interval',
	'regenerate_interval' => 10, // every 10 seconds
]);
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
print str_replace(
	'{PAGE_NAME}',
	$PAGE_NAME,
<<<HTML
<!DOCTYPE html>
<html><head>
<title>{PAGE_NAME}</title>
</head>
<body>
<div><a href="class_test.php">Class Test Master</a></div>
<div><h1>{PAGE_NAME}</h1></div>
HTML
);

// button logout
print <<<HTML
<script language="JavaScript">
function loginLogout()
{
	const form = document.createElement('form');
	form.method = 'post';
	const hiddenField = document.createElement('input');
	hiddenField.type = 'hidden';
	hiddenField.name = 'login_logout';
	hiddenField.value = 'Logout';
	form.appendChild(hiddenField);
	document.body.appendChild(form);
	form.submit();
}
</script>
<div style="margin: 20px 0;">
	<button onclick="loginLogout();" type="button">Logout</button>
</div>
HTML;
// string logout
print <<<HTML
<div style="margin: 20px 0;">
<form method="post" name="loginlogout">
<a href="javascript:document.loginlogout.login_logout.value=Logout;document.loginlogout.submit();">Logout</a>
<input type="hidden" name="login_logout" value="">
</form>
</div>
HTML;

echo "SESSION ID: " . $session->getSessionIdCall() . "<br>";

echo "CHECK PERMISSION: " . ($login->loginCheckPermissions() ? 'OK' : 'BAD') . "<br>";
echo "IS ADMIN: " . ($login->loginIsAdmin() ? 'OK' : 'BAD') . "<br>";
echo "MIN ACCESS BASE: " . ($login->loginCheckAccessBase('admin') ? 'OK' : 'BAD') . "<br>";
echo "MIN ACCESS PAGE: " . ($login->loginCheckAccessPage('admin') ? 'OK' : 'BAD') . "<br>";

echo "ACL: " . Support::printAr($login->loginGetAcl()) . "<br>";
echo "ACL (MIN): " . Support::printAr($login->loginGetAcl()['min'] ?? []) . "<br>";
echo "LOCALE: " . Support::printAr($login->loginGetLocale()) . "<br>";

echo "ECUID: " . $login->loginGetEuCuid() . "<br>";
echo "ECUUID: " . $login->loginGetEuCuuid() . "<br>";

echo "<hr>";
// set + check edit access id
$edit_access_cuid = 'buRW8Gu2Lkkf';
if (isset($login->loginGetAcl()['unit'])) {
	print "EDIT ACCESS CUID: " . $edit_access_cuid . "<br>";
	print "ACL UNIT: " . print_r(array_keys($login->loginGetAcl()['unit']), true) . "<br>";
	print "ACCESS CHECK: " . Support::prBl($login->loginCheckEditAccessCuid($edit_access_cuid)) . "<br>";
	if ($login->loginCheckEditAccessCuid($edit_access_cuid)) {
		print "Set new:" . $edit_access_cuid . "<br>";
	} else {
		print "Load default unit id: " . $login->loginGetAcl()['unit_id'] . "<br>";
	}
} else {
	print "Something went wrong with the login<br>";
}

// echo "<hr>";
// IP check: 'REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP' in _SERVER
// Agent check: 'HTTP_USER_AGENT'


echo "<hr>";
print "SESSION: " . Support::printAr($_SESSION) . "<br>";

$login->writeLog(
	'TEST LOG',
	[
		'test' => 'TEST A'
	],
	error:'No Error',
	write_type:'JSON'
);

print "</body></html>";
