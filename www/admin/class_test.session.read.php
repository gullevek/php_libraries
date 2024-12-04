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
$LOG_FILE_ID = 'classTest-session.read';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
use CoreLibs\Create\Session;

$PAGE_NAME = 'TEST CLASS: SESSION (READ)';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$session_name = 'class-test-session';
$session = new Session($session_name);
// $session_name = '';
$var = 'foo';
$value = 'bar';

echo "Global session name: " . ($GLOBALS['SET_SESSION_NAME'] ?? '-') . "<br>";

print "[SET] Current session id: " . $session->getSessionId() . "<br>";
print "[SET] Current session name: " . $session->getSessionName() . "<br>";
print "[SET] Current session active: " . ($session->checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[SET] Current session status: " . getSessionStatusString($session->getSessionStatus()) . "<br>";

print "[READ] " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";

// set again
print "[2] Restarted session: " . \CoreLibs\Debug\Support::prBl($session->restartSession()) . "<br>";
print "[SET] Current session id: " . $session->getSessionId() . "<br>";
print "[SET] Current session name: " . $session->getSessionName() . "<br>";
print "[SET] Current session active: " . ($session->checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[SET] Current session status: " . getSessionStatusString($session->getSessionStatus()) . "<br>";
print "[READ] " . $var . ": " . ($_SESSION[$var] ?? '{UNSET}') . "<br>";
print "[READ] Confirm " . $var . " is " . $value . ": "
	. (($_SESSION[$var] ?? '') == $value ? 'Matching' : 'Not matching') . "<br>";

print "[ALL SESSION]: " . \CoreLibs\Debug\Support::printAr($_SESSION) . "<br>";

print "</body></html>";

// __END__
