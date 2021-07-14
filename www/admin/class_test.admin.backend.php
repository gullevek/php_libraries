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
// override ECHO ALL FALSE
$ECHO_ALL = true;
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-admin';
ob_end_flush();

$basic = new CoreLibs\Basic();
$backend = new CoreLibs\Admin\Backend(DB_CONFIG);

print "<html><head><title>TEST CLASS: ADMIN BACKEND</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// set acl, from eg login acl
print "SETACL[]: " . $backend->setACL([]) . "<br>";
print "ADBEDITLOG: " . $backend->adbEditLog('CLASSTEST-ADMIN', 'Some info stirng') . "<br>";
print "ADBTOPMENU(0): " . \CoreLibs\Debug\Support::printAr($backend->adbTopMenu()) . "<br>";
print "ADBMSG: " . $backend->adbMsg('info', 'Message: %1$d', [1]) . "<br>";
print "Messaes: " . \CoreLibs\Debug\Support::printAr($this->messages) . "<br>";
print "ADBPRINTDATETIME:<br>" . $backend->adbPrintDateTime(2021, 6, 21, 6, 38, '_test') . "<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
