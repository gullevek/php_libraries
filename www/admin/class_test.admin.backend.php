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
// override ECHO ALL FALSE
$ECHO_ALL = true;
// define log file id
$LOG_FILE_ID = 'classTest-admin';
$SET_SESSION_NAME = EDIT_SESSION_NAME;
ob_end_flush();

$session = new CoreLibs\Create\Session($SET_SESSION_NAME);
$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db config with logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$l10n = new \CoreLibs\Language\L10n(
	SITE_LOCALE,
	SITE_DOMAIN,
	BASE . INCLUDES . LOCALE,
	SITE_ENCODING
);
$backend = new CoreLibs\Admin\Backend(
	$db,
	$log,
	$session,
	$l10n,
	DEFAULT_ACL_LEVEL
);
use CoreLibs\Debug\Support;

$PAGE_NAME = 'TEST CLASS: ADMIN BACKEND';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// set acl, from eg login acl
print "SETACL[]: <br>";
$backend->setACL(['EMPTY' => 'EMPTY']);
print "ADBEDITLOG: <br>";
$backend->adbEditLog('CLASSTEST-ADMIN', 'Some info string');
print "ADBTOPMENU(0): " . Support::printAr($backend->adbTopMenu(CONTENT_PATH)) . "<br>";
print "ADBMSG: <br>";
$backend->adbMsg('info', 'Message: %1$d', [1]);
print "Messaes: " . Support::printAr($backend->messages) . "<br>";
print "ADBPRINTDATETIME:<br>" . $backend->adbPrintDateTime(2021, 6, 21, 6, 38, '_test') . "<br>";

print "</body></html>";

// __END__
