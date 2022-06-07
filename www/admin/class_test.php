<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

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
$login = new CoreLibs\ACL\Login($db, $log, $session);
$locale = \CoreLibs\Language\GetLocale::setLocale();
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
);
$backend = new CoreLibs\Admin\Backend($db, $log, $session, $l10n, $locale);
$backend->db->dbInfo(true);
ob_end_flush();

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS</title><head>";
print "<body>";

print '<div><a href="class_test.db.php">Class Test: DB</a></div>';
print '<div><a href="class_test.db.DbReturn.php">Class Test: DB dbReturn</a></div>';
print '<div><a href="class_test.colors.php">Class Test: COLORS</a></div>';
print '<div><a href="class_test.mime.php">Class Test: MIME</a></div>';
print '<div><a href="class_test.json.php">Class Test: JSON</a></div>';
print '<div><a href="class_test.token.php">Class Test: FORM TOKEN</a></div>';
print '<div><a href="class_test.password.php">Class Test: PASSWORD</a></div>';
print '<div><a href="class_test.math.php">Class Test: MATH</a></div>';
print '<div><a href="class_test.html.php">Class Test: HTML/ELEMENTS</a></div>';
print '<div><a href="class_test.email.php">Class Test: EMAIL</a></div>';
print '<div><a href="class_test.uids.php">Class Test: UIDS</a></div>';
print '<div><a href="class_test.phpv.php">Class Test: PHP VERSION</a></div>';
print '<div><a href="class_test.hash.php">Class Test: HASH</a></div>';
print '<div><a href="class_test.encoding.php">Class Test: ENCODING (CHECK/CONVERT/MIME)</a></div>';
print '<div><a href="class_test.image.php">Class Test: IMAGE</a></div>';
print '<div><a href="class_test.byte.php">Class Test: BYTE CONVERT</a></div>';
print '<div><a href="class_test.datetime.php">Class Test: DATE/TIME</a></div>';
print '<div><a href="class_test.array.php">Class Test: ARRAY HANDLER</a></div>';
print '<div><a href="class_test.file.php">Class Test: FILE</a></div>';
print '<div><a href="class_test.randomkey.php">Class Test: RANDOM KEY</a></div>';
print '<div><a href="class_test.system.php">Class Test: SYSTEM</a></div>';
print '<div><a href="class_test.readenvfile.php">Class Test: READ ENV FILE</a></div>';
print '<div><a href="class_test.runningtime.php">Class Test: RUNNING TIME</a></div>';
print '<div><a href="class_test.memoryusage.php">Class Test: MEMORY USAGE</a></div>';
print '<div><a href="class_test.debug.php">Class Test: DEBUG</a></div>';
print '<div><a href="class_test.output.form.php">Class Test: OUTPUT FORM</a></div>';
print '<div><a href="class_test.admin.backend.php">Class Test: BACKEND ADMIN CLASS</a></div>';
print '<div><a href="class_test.lang.php">Class Test: LANG/L10n</a></div>';
print '<div><a href="class_test.session.php">Class Test: SESSION</a></div>';
print '<div><a href="class_test.smarty.php">Class Test: SMARTY</a></div>';
print '<div><a href="class_test.login.php">Class Test: LOGIN</a></div>';
print '<div><a href="class_test.autoloader.php">Class Test: AUTOLOADER</a></div>';
print '<div><a href="class_test.config.link.php">Class Test: CONFIG LINK</a></div>';
print '<div><a href="class_test.config.direct.php">Class Test: CONFIG DIRECT</a></div>';
print '<div><a href="subfolder/class_test.config.direct.php">Class Test: CONFIG DIRECT SUB</a></div>';

print "<hr>";
print "L: " . CoreLibs\Debug\Support::printAr($locale) . "<br>";
// print all _ENV vars set
print "<div>READ _ENV ARRAY:</div>";
print CoreLibs\Debug\Support::printAr(array_map('htmlentities', $_ENV));
// set + check edit access id
$edit_access_id = 3;
if (is_object($login) && isset($login->loginGetAcl()['unit'])) {
	print "ACL UNIT: " . print_r(array_keys($login->loginGetAcl()['unit']), true) . "<br>";
	print "ACCESS CHECK: " . (string)$login->loginCheckEditAccess($edit_access_id) . "<br>";
	if ($login->loginCheckEditAccess($edit_access_id)) {
		$backend->edit_access_id = $edit_access_id;
	} else {
		$backend->edit_access_id = $login->loginGetAcl()['unit_id'];
	}
} else {
	print "Something went wrong with the login<br>";
}

//	$backend->log->debug('SESSION', \CoreLibs\Debug\Support::printAr($_SESSION));

print '<form method="post" name="loginlogout">';
print '<a href="javascript:document.loginlogout.login_logout.value=\'Logou\';'
	. 'document.loginlogout.submit();">Logout</a>';
print '<input type="hidden" name="login_logout" value="">';
print '</form>';

// print the debug core vars
foreach (['on', 'off'] as $flag) {
	foreach (['debug', 'echo', 'print'] as $type) {
		$prefix = $flag == 'off' ? 'NOT ' : '';
		print $prefix . strtoupper($type) . ' OUT: '
			. \CoreLibs\Debug\Support::printAr($backend->log->getLogLevel($type, $flag)) . '<br>';
	}
}
foreach (['debug', 'echo', 'print'] as $type) {
	print strtoupper($type) . ' OUT ALL: ' . $backend->log->getLogLevelAll($type) . '<br>';
}

$log->debug('SOME MARK', 'Some error output');

// INTERNAL SET
print "EDIT ACCESS ID: " . $backend->edit_access_id . "<br>";
if (is_object($login)) {
	//	print "ACL: <br>".$backend->print_ar($login->loginGetAcl())."<br>";
	$log->debug('ACL', "ACL: " . \CoreLibs\Debug\Support::printAr($login->loginGetAcl()));
	//	print "DEFAULT ACL: <br>".$backend->print_ar($login->default_acl_list)."<br>";
	//	print "DEFAULT ACL: <br>".$backend->print_ar($login->default_acl_list)."<br>";
	// $result = array_flip(
	// 	array_filter(
	// 		array_flip($login->default_acl_list),
	// 		function ($key) {
	// 			if (is_numeric($key)) {
	// 				return $key;
	// 			}
	// 		}
	// 	)
	// );
	//	print "DEFAULT ACL: <br>".$backend->print_ar($result)."<br>";
	// DEPRICATED CALL
	//	$backend->adbSetACL($login->loginGetAcl());
}

print "THIS HOST: " . HOST_NAME . ", with PROTOCOL: " . HOST_PROTOCOL . " is running SSL: " . HOST_SSL . "<br>";
print "DIR: " . DIR . "<br>";
print "BASE: " . BASE . "<br>";
print "ROOT: " . ROOT . "<br>";
print "HOST: " . HOST_NAME . " => DB HOST: " . DB_CONFIG_NAME . " => " . print_r(DB_CONFIG, true) . "<br>";

print "DS is: " . DIRECTORY_SEPARATOR . "<br>";
print "SERVER HOST: " . $_SERVER['HTTP_HOST'] . "<br>";

// print error messages
// print $login->log->printErrorMsg();
print $log->printErrorMsg();

print "</body></html>";

# __END__
