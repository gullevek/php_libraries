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
/** @phan-suppress-next-line PhanRedefineFunction */
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
// if (!defined('SET_SESSION_NAME')) {
// 	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
// }
// define log file id
$LOG_FILE_ID = 'classTest-session';
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

$PAGE_NAME = 'TEST CLASS: SESSION';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$session_name = 'class-test-session';
$var = 'foo';
$value = 'bar';

foreach (['123', '123-123', '123abc'] as $_session_name) {
	print "[UNSET] Session Name valid for " . $_session_name . ": "
		. (Session::checkValidSessionName($_session_name) ? 'Valid' : 'Invalid') . "<br>";
}

echo "Global session name: " . ($GLOBALS['SET_SESSION_NAME'] ?? '-') . "<br>";

print "[UNSET] Current session id: " . Session::getSessionId() . "<br>";
print "[UNSET] Current session name: " . Session::getSessionName() . "<br>";
print "[UNSET] Current session active: " . (Session::checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[UNSET] Current session status: " . getSessionStatusString(Session::getSessionStatus()) . "<br>";
if (isset($_SESSION)) {
	print "[UNSET] _SESSION is: set<br>";
} else {
	print "[UNSET] _SESSION is: not set<br>";
}
#
print "[UNSET] To set session name valid: "
	. (Session::checkValidSessionName($session_name) ? 'Valid' : 'Invalid') . "<br>";
if (false === ($session = Session::startSession($session_name))) {
	print "[FAILED] Session start failed: " . Session::getErrorStr() . "<br>";
} else {
	print "[SET] Current session id: " . $session . "<br>";
}
// set again
if (false === ($session = Session::startSession($session_name))) {
	print "[2 FAILED] Session start failed: " . Session::getErrorStr() . "<br>";
} else {
	print "[2 SET] Current session id: " . $session . "<br>";
}
print "[SET] Current session id: " . Session::getSessionId() . "<br>";
print "[SET] Current session name: " . Session::getSessionName() . "<br>";
print "[SET] Current session active: " . (Session::checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[SET] Current session status: " . getSessionStatusString(Session::getSessionStatus()) . "<br>";
if (isset($_SESSION)) {
	print "[SET] _SESSION is: set<br>";
} else {
	print "[SET] _SESSION is: not set<br>";
}
if (!isset($_SESSION['counter'])) {
	$_SESSION['counter'] = 0;
}
$_SESSION['counter']++;
print "[READ] " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
$_SESSION[$var] = $value;
print "[READ] " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
print "[READ] Confirm " . $var . " is " . $value . ": "
	. (($_SESSION[$var] ?? '') == $value ? 'Matching' : 'Not matching') . "<br>";

// differnt session name
$session_name = 'class-test-session-ALT';
if (false === ($session = Session::startSession($session_name))) {
	print "[3 FAILED] Session start failed: " . Session::getErrorStr() . "<br>";
} else {
	print "[3 SET] Current session id: " . $session . "<br>";
}
print "[SET AGAIN] Current session id: " . Session::getSessionId() . "<br>";

print "[ALL SESSION]: " . \CoreLibs\Debug\Support::printAr($_SESSION) . "<br>";

// close session
Session::writeClose();
// will never be written
$_SESSION['will_never_be_written'] = 'empty';

// open again
$session_name = 'class-test-session';
if (false === ($session = Session::startSession($session_name))) {
	print "[4 FAILED] Session start failed: " . Session::getErrorStr() . "<br>";
} else {
	print "[4 SET] Current session id: " . $session . "<br>";
}
print "[START AGAIN] Current session id: " . Session::getSessionId() . "<br>";
$_SESSION['will_be_written_again'] = 'Full';

// close session
Session::writeClose();
// invalid
$session_name = '123';
if (false === ($session = Session::startSession($session_name))) {
	print "[5 FAILED] Session start failed: " . Session::getErrorStr() . "<br>";
} else {
	print "[5 SET] Current session id: " . $session . "<br>";
}
print "[BAD NAME] Current session id: " . Session::getSessionId() . "<br>";
print "[BAD NAME] Current session name: " . Session::getSessionName() . "<br>";
print "[BAD NAME] Current session active: " . (Session::checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[BAD NAME] Current session status: " . getSessionStatusString(Session::getSessionStatus()) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
