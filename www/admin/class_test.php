<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest';
$SET_SESSION_NAME = EDIT_SESSION_NAME;

use CoreLibs\Logging;
use CoreLibs\Debug\Support;

// init login & backend class
$session = new CoreLibs\Create\Session($SET_SESSION_NAME);
$log = new Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	// add file date
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
	$locale['encoding'],
);

$backend = new CoreLibs\Admin\Backend(
	$db,
	$log,
	$session,
	$l10n,
	DEFAULT_ACL_LEVEL
);
$backend->db->dbInfo(true);
ob_end_flush();

print "<!DOCTYPE html>";
print "<html><head><title>TEST CLASS</title></head>";
print "<body>";

// key: file name, value; name
$test_files = [
	'class_test.db.php' => 'Class Test: DB',
	'class_test.db.types.php' => 'Class Test: DB column type convert',
	'class_test.db.query-placeholder.php' => 'Class Test: DB query placeholder convert',
	'class_test.db.dbReturn.php' => 'Class Test: DB dbReturn',
	'class_test.db.single.php' => 'Class Test: DB single query tests',
	'class_test.db.sqlite.php' => 'Class Test: DB: SqLite',
	'class_test.convert.colors.php' => 'Class Test: CONVERT COLORS',
	'class_test.check.colors.php' => 'Class Test: CHECK COLORS',
	'class_test.mime.php' => 'Class Test: MIME',
	'class_test.json.php' => 'Class Test: JSON',
	'class_test.token.php' => 'Class Test: FORM TOKEN',
	'class_test.password.php' => 'Class Test: PASSWORD',
	'class_test.encryption.php' => 'Class Test: ENCRYPTION',
	'class_test.math.php' => 'Class Test: MATH',
	'class_test.html.php' => 'Class Test: HTML/ELEMENTS',
	'class_test.html_build.element.php' => 'Class Test: HTML BUILDER: ELEMENT',
	'class_test.html_build.block.php' => 'Class Test: HTML BUILDER: BLOCK',
	'class_test.html_build.replace.php' => 'Class Test: HTML BUILDER: STRING REPLACE',
	'class_test.email.php' => 'Class Test: EMAIL',
	'class_test.create_email.php' => 'Class Test: CREATE EMAIL',
	'class_test.uids.php' => 'Class Test: UIDS',
	'class_test.phpv.php' => 'Class Test: PHP VERSION',
	'class_test.hash.php' => 'Class Test: HASH',
	'class_test.encoding.php' => 'Class Test: ENCODING (CHECK/CONVERT/MIME)',
	'class_test.image.php' => 'Class Test: IMAGE',
	'class_test.byte.php' => 'Class Test: BYTE CONVERT',
	'class_test.strings.php' => 'Class Test: STRING CONVERT',
	'class_test.datetime.php' => 'Class Test: DATE/TIME',
	'class_test.array.php' => 'Class Test: ARRAY HANDLER',
	'class_test.file.php' => 'Class Test: FILE',
	'class_test.randomkey.php' => 'Class Test: RANDOM KEY',
	'class_test.system.php' => 'Class Test: SYSTEM',
	'class_test.readenvfile.php' => 'Class Test: READ ENV FILE',
	'class_test.runningtime.php' => 'Class Test: RUNNING TIME',
	'class_test.memoryusage.php' => 'Class Test: MEMORY USAGE',
	'class_test.debug.php' => 'Class Test: DEBUG',
	'class_test.logging.php' => 'Class Test: LOGGING',
	'class_test.output.form.php' => 'Class Test: OUTPUT FORM',
	'class_test.admin.backend.php' => 'Class Test: BACKEND ADMIN CLASS',
	'class_test.lang.php' => 'Class Test: LANG/L10n',
	'class_test.varistype.php' => 'Class Test: SET VAR TYPE',
	'class_test.session.php' => 'Class Test: SESSION',
	'class_test.session.read.php' => 'Class Test: SESSION: READ',
	'class_test.smarty.php' => 'Class Test: SMARTY',
	'class_test.login.php' => 'Class Test: LOGIN',
	'class_test.autoloader.php' => 'Class Test: AUTOLOADER',
	'class_test.config.link.php' => 'Class Test: CONFIG LINK',
	'class_test.config.direct.php' => 'Class Test: CONFIG DIRECT',
	'class_test.class-calls.php' => 'Class Test: CLASS CALLS',
	'class_test.error_msg.php' => 'Class Test: ERROR MSG',
	'class_test.url-requests.curl.php' => 'Class Test: URL REQUESTS: CURL',
	'subfolder/class_test.config.direct.php' => 'Class Test: CONFIG DIRECT SUB',
];

asort($test_files);

foreach ($test_files as $file => $name) {
	print '<div><a href="' . $file . '">' . $name . '</a></div>';
}

print "<hr>";
print "L: " . Support::dumpVar($locale) . "<br>";
// print all _ENV vars set
print "<div>READ _ENV ARRAY:</div>";
print Support::dumpVar(array_map('htmlentities', $_ENV));
// set + check edit access id
$edit_access_id = 3;
if (isset($login->loginGetAcl()['unit'])) {
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

//	$backend->log->debug('SESSION', \CoreLibs\Debug\Support::dumpVar($_SESSION));

print '<form method="post" name="loginlogout">';
print '<a href="javascript:document.loginlogout.login_logout.value=\'Logou\';'
	. 'document.loginlogout.submit();">Logout</a>';
print '<input type="hidden" name="login_logout" value="">';
print '</form>';

print "Log Level: " . $backend->log->getLoggingLevel()->getName() . "<br>";
print "Log ID: " . $backend->log->getLogFileId() . "<br>";
print "Log Date: " . $backend->log->getLogDate() . "<br>";
print "Log Max File Size: " . $backend->log->getLogMaxFileSize() . " bytes<br>";
print "Log Flags: " . $backend->log->getLogFlags() . "<br>";
foreach (
	[
		Logging\Logger\Flag::per_run,
		Logging\Logger\Flag::per_date,
		Logging\Logger\Flag::per_group,
		Logging\Logger\Flag::per_page,
		Logging\Logger\Flag::per_class,
		Logging\Logger\Flag::per_level
	] as $flag
) {
	print "Log Flag: " . $flag->name . ": "
		. CoreLibs\Debug\Support::printBool($backend->log->getLogFlag($flag)) . "<br>";
}

$log->debug('SOME MARK', 'Some error output');

// INTERNAL SET
print "EDIT ACCESS ID: " . $backend->edit_access_id . "<br>";
//	print "ACL: <br>".$backend->print_ar($login->loginGetAcl())."<br>";
// $log->debug('ACL', "ACL: " . \CoreLibs\Debug\Support::dumpVar($login->loginGetAcl()));
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

print "THIS HOST: " . HOST_NAME . ", with PROTOCOL: " . HOST_PROTOCOL . " is running SSL: " . HOST_SSL . "<br>";
print "DIR: " . DIR . "<br>";
print "BASE: " . BASE . "<br>";
print "ROOT: " . ROOT . "<br>";
print "HOST: " . HOST_NAME . " => DB HOST: " . DB_CONFIG_NAME . " => " . Support::dumpVar(DB_CONFIG) . "<br>";

print "DS is: " . DIRECTORY_SEPARATOR . "<br>";
print "SERVER HOST: " . $_SERVER['HTTP_HOST'] . "<br>";

print "</body></html>";

# __END__
