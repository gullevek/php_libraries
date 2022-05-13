<?php // phpcs:ignore warning

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

/**
 * Undocumented function
 *
 * @param  int    $status
 * @return string
 */
function getSessionStatusString(int $status): string
{
	switch ($status) {
		case PHP_SESSION_DISABLED:
			$status = 'PHP_SESSION_DISABLED';
			break;
		case PHP_SESSION_NONE:
			$status = 'PHP_SESSION_NONE';
			break;
		case PHP_SESSION_ACTIVE:
			$status = 'PHP_SESSION_ACTIVE';
			break;
		default:
			$status = '[!] UNDEFINED';
			break;
	}
	return $status;
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
$LOG_FILE_ID = 'classTest-session.read';
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
use CoreLibs\Create\Session;

$PAGE_NAME = 'TEST CLASS: SESSION (READ)';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$session_name = 'class-test-session';
// $session_name = '';
$var = 'foo';
$value = 'bar';

echo "Global session name: " . ($GLOBALS['SET_SESSION_NAME'] ?? '-') . "<br>";

print "[UNSET] Current session id: " . Session::getSessionId() . "<br>";
print "[UNSET] Current session name: " . Session::getSessionName() . "<br>";
print "[UNSET] Current session active: " . Session::checkActiveSession() . "<br>";
print "[UNSET] Current session status: " . getSessionStatusString(Session::getSessionStatus()) . "<br>";

print "[READ] " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
// start
$session = Session::startSession($session_name);
if ($session === false) {
	print "Session start failed: " . Session::getErrorStr() . "<br>";
} else {
	print "Current session id: " . $session . "<br>";
}
// set again
$session = Session::startSession($session_name);
if ($session === false) {
	print "[2] Session start failed<br>";
} else {
	print "[2] Current session id: " . $session . "<br>";
}
print "[SET] Current session id: " . Session::getSessionId() . "<br>";
print "[SET] Current session name: " . Session::getSessionName() . "<br>";
print "[SET] Current session active: " . Session::checkActiveSession() . "<br>";
print "[SET] Current session status: " . getSessionStatusString(Session::getSessionStatus()) . "<br>";
print "[READ] " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
print "[READ] Confirm " . $var . " is " . $value . ": "
	. (($_SESSION[$var] ?? '') == $value ? 'Matching' : 'Not matching') . "<br>";

print "[ALL SESSION]: " . \CoreLibs\Debug\Support::printAr($_SESSION) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__