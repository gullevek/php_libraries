<?php // phpcs:ignore warning

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

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
// define log file id
$LOG_FILE_ID = 'classTest-session';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
use CoreLibs\Create\Session;
$session = new Session();

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
		. ($session->checkValidSessionName($_session_name) ? 'Valid' : 'Invalid') . "<br>";
}

echo "Global session name: " . ($GLOBALS['SET_SESSION_NAME'] ?? '-') . "<br>";

print "[UNSET] Current session id: " . $session->getSessionId() . "<br>";
print "[UNSET] Current session name: " . $session->getSessionName() . "<br>";
print "[UNSET] Current session active: " . ($session->checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[UNSET] Current session status: " . getSessionStatusString($session->getSessionStatus()) . "<br>";
if (isset($_SESSION)) {
	print "[UNSET] _SESSION is: set<br>";
} else {
	print "[UNSET] _SESSION is: not set<br>";
}
#
print "[UNSET] To set session name valid: "
	. ($session->checkValidSessionName($session_name) ? 'Valid' : 'Invalid') . "<br>";
if (false === ($session_id = $session->startSession($session_name))) {
	print "[FAILED] Session start failed: " . $session->getErrorStr() . "<br>";
} else {
	print "[SET] Current session id: " . $session_id . "<br>";
}
// set again
if (false === ($session_id = $session->startSession($session_name))) {
	print "[2 FAILED] Session start failed: " . $session->getErrorStr() . "<br>";
} else {
	print "[2 SET] Current session id: " . $session_id . "<br>";
}
print "[SET] Current session id: " . $session->getSessionId() . "<br>";
print "[SET] Current session name: " . $session->getSessionName() . "<br>";
print "[SET] Current session active: " . ($session->checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[SET] Current session status: " . getSessionStatusString($session->getSessionStatus()) . "<br>";
if (isset($_SESSION)) {
	print "[SET] _SESSION is: set<br>";
} else {
	print "[SET] _SESSION is: not set<br>";
}
if (!isset($_SESSION['counter'])) {
	$_SESSION['counter'] = 0;
}
$_SESSION['counter']++;
print "[READ] A " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
$_SESSION[$var] = $value;
print "[READ] B " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
print "[READ] Confirm " . $var . " is " . $value . ": "
	. (($_SESSION[$var] ?? '') == $value ? 'Matching' : 'Not matching') . "<br>";

// test set wrappers methods
$session->setS('setwrap', 'YES, method set _SESSION var');
print "[READ WRAP] A setwrap: " . $session->getS('setwrap') . "<br>";
print "[READ WRAP] Isset: " . ($session->issetS('setwrap') ? 'Yes' : 'No') . "<br>";
$session->unsetS('setwrap');
print "[READ WRAP] unset setwrap: " . $session->getS('setwrap') . "<br>";
print "[READ WRAP] unset Isset: " . ($session->issetS('setwrap') ? 'Yes' : 'No') . "<br>";
// test __get/__set
$session->setwrap = 'YES, magic set _SESSION var'; /** @phpstan-ignore-line GET/SETTER */
print "[READ MAGIC] A setwrap: " . ($session->setwrap ?? '') . "<br>";
print "[READ MAGIC] Isset: " . (isset($session->setwrap) ? 'Yes' : 'No') . "<br>";
unset($session->setwrap);
print "[READ MAGIC] unset setwrap: " . ($session->setwrap ?? '') . "<br>";
print "[READ MAGIC] unset Isset: " . (isset($session->setwrap) ? 'Yes' : 'No') . "<br>";

// differnt session name
$session_name = 'class-test-session-ALT';
if (false === ($session_id = $session->startSession($session_name))) {
	print "[3 FAILED] Session start failed: " . $session->getErrorStr() . "<br>";
} else {
	print "[3 SET] Current session id: " . $session_id . "<br>";
}
print "[SET AGAIN] Current session id: " . $session->getSessionId() . "<br>";

print "[ALL SESSION]: " . \CoreLibs\Debug\Support::printAr($_SESSION) . "<br>";

// close session
$session->writeClose();
// will never be written
$_SESSION['will_never_be_written'] = 'empty';

// open again
$session_name = 'class-test-session';
if (false === ($session_id = $session->startSession($session_name))) {
	print "[4 FAILED] Session start failed: " . $session->getErrorStr() . "<br>";
} else {
	print "[4 SET] Current session id: " . $session_id . "<br>";
}
print "[START AGAIN] Current session id: " . $session->getSessionId() . "<br>";
$_SESSION['will_be_written_again'] = 'Full';

// close session
$session->writeClose();
// invalid
$session_name = '123';
if (false === ($session_id = $session->startSession($session_name))) {
	print "[5 FAILED] Session start failed: " . $session->getErrorStr() . "<br>";
} else {
	print "[5 SET] Current session id: " . $session_id . "<br>";
}
print "[BAD NAME] Current session id: " . $session->getSessionId() . "<br>";
print "[BAD NAME] Current session name: " . $session->getSessionName() . "<br>";
print "[BAD NAME] Current session active: " . ($session->checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[BAD NAME] Current session status: " . getSessionStatusString($session->getSessionStatus()) . "<br>";

print "</body></html>";

// __END__
