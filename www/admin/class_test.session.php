<?php // phpcs:ignore warning

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

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
use CoreLibs\Debug\Support;
use CoreLibs\Create\Session;

$PAGE_NAME = 'TEST CLASS: SESSION';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$session_name = 'class-test-session';
print "Valid session name static check for '" . $session_name . "': "
	. Support::prBl(Session::checkValidSessionName($session_name)) . "<br>";
$var = 'foo';
$value = 'bar';
$session = new Session($session_name);

foreach (['123', '123-123', '123abc'] as $_session_name) {
	print "[UNSET] Session Name valid for '" . $_session_name . "': "
		. ($session->checkValidSessionName($_session_name) ? 'Valid' : 'Invalid') . "<br>";
}

echo "Global session name: " . ($GLOBALS['SET_SESSION_NAME'] ?? '-') . "<br>";

print "[SET] Current session id: " . $session->getSessionId() . "<br>";
print "[SET] Current session name: " . $session->getSessionName() . "<br>";
print "[SET] Current session active: " . ($session->checkActiveSession() ? 'Yes' : 'No') . "<br>";
print "[SET] Current session auto write close: " . ($session->checkAutoWriteClose() ? 'Yes' : 'No') . "<br>";
print "[SET] Current session status: " . getSessionStatusString($session->getSessionStatus()) . "<br>";
if (isset($_SESSION)) {
	print "[SET] _SESSION is: set<br>";
} else {
	print "[SET] _SESSION is: not set<br>";
}
#
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
$session->set('setwrap', 'YES, method set _SESSION var');
print "[READ WRAP] A setwrap: " . $session->get('setwrap') . "<br>";
print "[READ WRAP] Isset: " . ($session->isset('setwrap') ? 'Yes' : 'No') . "<br>";
$session->unset('setwrap');
print "[READ WRAP] unset setwrap: " . $session->get('setwrap') . "<br>";
print "[READ WRAP] unset Isset: " . ($session->isset('setwrap') ? 'Yes' : 'No') . "<br>";
$session->set('foo 3', 'brause');
// set many
$session->setMany([
	'foo 1' => 'bar',
	'foo 2' => 'kamel',
]);
print "[READ MANY]: " . Support::printAr($session->getMany(['foo 1', 'foo 2'])) . "<br>";
try {
	$session->setMany([ /** @phpstan-ignore-line deliberate error */
		'ok' => 'ok',
		'a123' => 'bar',
		1 => 'bar',
	]);
} catch (\Exception $e) {
	print "FAILED] Session manySet failed:<br>" . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
}
try {
	$session->set('123', 'illigal');
} catch (\Exception $e) {
	print "FAILED] Session set failed:<br>" . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
}

print "<hr>";
// differnt session name
$session_name = 'class-test-session-ALT';
try {
	$session_alt = new Session($session_name);
	print "[3 SET] Current session id: " . $session_alt->getSessionId() . "<br>";
	print "[SET AGAIN] Current session id: " . $session_alt->getSessionId() . "<br>";
} catch (\Exception $e) {
	print "[3 FAILED] Session start failed:<br>" . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
}


print "[ALL SESSION]: " . Support::printAr($_SESSION) . "<br>";

// close session
$session->writeClose();
// will never be written
$_SESSION['will_never_be_written'] = 'empty';
// auto open session if closed to write
$session->set('auto_write_session', 'Some value');
// restart session
$session->restartSession();
$_SESSION['this_will_be_written'] = 'not empty';

// open again with same name
$session_name = 'class-test-session';
try {
	$session_alt = new Session($session_name, ['auto_write_close' => true]);
	print "[4 SET] Current session id: " . $session_alt->getSessionId() . "<br>";
	print "[4 SET] Current session auto write close: " . ($session_alt->checkAutoWriteClose() ? 'Yes' : 'No') . "<br>";
	print "[START AGAIN] Current session id: " . $session_alt->getSessionId() . "<br>";
	$session_alt->set('alt_write_auto_close', 'set auto');
	// below is deprecated
	// $session_alt->do_not_do_this = 'foo bar auto set';
} catch (\Exception $e) {
	print "[4 FAILED] Session start failed:<br>" . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
}
$_SESSION['will_be_written_again'] = 'Full';

print "[ALL SESSION]: " . Support::printAr($_SESSION) . "<br>";

// close session
$session->writeClose();
// invalid
$session_name = '123';
try {
	$session_bad = new Session($session_name);
	print "[5 SET] Current session id: " . $session_bad->getSessionId() . "<br>";
} catch (\Exception $e) {
	print "[5 FAILED] Session start failed:<br>" . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
}

print "</body></html>";

// __END__
