<?php // phpcs:ignore warning
declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

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
$LOG_FILE_ID = 'classTest-form';
ob_end_flush();

$basic = new CoreLibs\Basic();
$form = new CoreLibs\Output\Form\Generate(DB_CONFIG);
// $db = new CoreLibs\DB\IO(DB_CONFIG, $basic->log);

print "<html><head><title>TEST CLASS: FORM GENERATE</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "TODO<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
