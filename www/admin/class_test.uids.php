<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-uids';
ob_end_flush();

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
$_uids = new CoreLibs\Create\Uids();
use CoreLibs\Create\Uids;
$uids_class = 'CoreLibs\Create\Uids';

print "<html><head><title>TEST CLASS: UIDS</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// class
print "UUIDV4: " . $_uids->uuidv4() . "<br>";
print "UNIQID (d): " . $_uids->uniqId() . "<br>";
print "UNIQID (md5): " . $_uids->uniqId('md5') . "<br>";
print "UNIQID (sha256): " . $_uids->uniqId('sha256') . "<br>";
// statc
print "S::UUIDV4: " . $uids_class::uuidv4() . "<br>";
print "S::UNIQID (d): " . $uids_class::uniqId() . "<br>";
print "S::UNIQID (md5): " . $uids_class::uniqId('md5') . "<br>";
print "S::UNIQID (sha256): " . $uids_class::uniqId('sha256') . "<br>";
// uniq ids
print "UNIQU ID SHORT : " . Uids::uniqIdShort() . "<br>";
print "UNIQU ID LONG : " . Uids::uniqIdLong() . "<br>";

// DEPRECATED
/* print "D/UUIDV4: ".$basic->uuidv4()."<br>";
print "/DUNIQID (d): ".$basic->uniqId()."<br>"; */

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
