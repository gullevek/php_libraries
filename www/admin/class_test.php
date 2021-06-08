<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest';

// init login & backend class
$login = new CoreLibs\ACL\Login(DB_CONFIG);
$basic = new CoreLibs\Admin\Backend(DB_CONFIG);
$basic->dbInfo(true);
ob_end_flush();

print "<html><head><title>TEST CLASS</title><head>";
print "<body>";

print '<div><a href="class_test.db.php">Class Test: DB</a></div>';
print '<div><a href="class_test.colors.php">Class Test: Colors</a></div>';
print '<div><a href="class_test.mime.php">Class Test: MIME</a></div>';
print '<div><a href="class_test.json.php">Class Test: Json</a></div>';
print '<div><a href="class_test.token.php">Class Test: Form Token</a></div>';
print '<div><a href="class_test.password.php">Class Test: Password</a></div>';
print '<div><a href="class_test.math.php">Class Test: Math</a></div>';
print '<div><a href="class_test.html.php">Class Test: HTML/ELEMENTS</a></div>';
print '<div><a href="class_test.email.php">Class Test: EMAIL</a></div>';
print '<div><a href="class_test.uids.php">Class Test: UIDS</a></div>';
print '<div><a href="class_test.phpv.php">Class Test: PHP VERSION</a></div>';
print '<div><a href="class_test.hash.php">Class Test: HASH</a></div>';
print '<div><a href="class_test.encoding.php">Class Test: ENCODING</a></div>';
print '<div><a href="class_test.image.php">Class Test: IMAGE</a></div>';
print '<div><a href="class_test.byte.php">Class Test: BYTE CONVERT</a></div>';
print '<div><a href="class_test.datetime.php">Class Test: DATE/TIME</a></div>';
print '<div><a href="class_test.array.php">Class Test: ARRAY HANDLER</a></div>';
print '<div><a href="class_test.file.php">Class Test: FILE</a></div>';
print '<div><a href="class_test.randomkey.php">Class Test: RANDOM KEY</a></div>';
print '<div><a href="class_test.system.php">Class Test: SYSTEM</a></div>';
print '<div><a href="class_test.runningtime.php">Class Test: RUNNING TIME</a></div>';
print '<div><a href="class_test.debug.php">Class Test: DEBUG</a></div>';

// set + check edit access id
$edit_access_id = 3;
if (is_object($login) && isset($login->acl['unit'])) {
	print "ACL UNIT: ".print_r(array_keys($login->acl['unit']), true)."<br>";
	print "ACCESS CHECK: ".(string)$login->loginCheckEditAccess($edit_access_id)."<br>";
	if ($login->loginCheckEditAccess($edit_access_id)) {
		$basic->edit_access_id = $edit_access_id;
	} else {
		$basic->edit_access_id = $login->acl['unit_id'];
	}
} else {
	print "Something went wrong with the login<br>";
}

//	$basic->debug('SESSION', $basic->print_ar($_SESSION));

print '<form method="post" name="loginlogout">';
print '<a href="javascript:document.loginlogout.login_logout.value=\'Logou\';document.loginlogout.submit();">Logout</a>';
print '<input type="hidden" name="login_logout" value="">';
print '</form>';

// print the debug core vars
print "DEBUG OUT: ".$basic->debug_output."<br>";
print "ECHO OUT: ".$basic->echo_output."<br>";
print "PRINT OUT: ".$basic->print_output."<br>";
print "NOT DEBUG OUT: ".$basic->debug_output_not."<br>";
print "NOT ECHO OUT: ".$basic->echo_output_not."<br>";
print "NOT PRINT OUT: ".$basic->print_output_not."<br>";
print "DEBUG OUT ALL: ".$basic->debug_output_all."<br>";
print "ECHO OUT ALL: ".$basic->echo_output_all."<br>";
print "PRINT OUT ALL: ".$basic->print_output_all."<br>";

$basic->debug('SOME MARK', 'Some error output');

print "EDIT ACCESS ID: ".$basic->edit_access_id."<br>";
if (is_object($login)) {
	//	print "ACL: <br>".$basic->print_ar($login->acl)."<br>";
	$basic->debug('ACL', "ACL: ".$basic->printAr($login->acl));
	//	print "DEFAULT ACL: <br>".$basic->print_ar($login->default_acl_list)."<br>";
	//	print "DEFAULT ACL: <br>".$basic->print_ar($login->default_acl_list)."<br>";
	//	$result = array_flip(array_filter(array_flip($login->default_acl_list), function ($key) { if (is_numeric($key)) return $key; }));
	//	print "DEFAULT ACL: <br>".$basic->print_ar($result)."<br>";
	// DEPRICATED CALL
	//	$basic->adbSetACL($login->acl);
}

// print error messages
// print $login->printErrorMsg();
print $basic->printErrorMsg();

print "</body></html>";

# __END__
