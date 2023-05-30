<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
$GLOBALS['SET_SESSION_NAME'] = EDIT_SESSION_NAME;
// define log file id
$LOG_FILE_ID = 'classTest-logging';
ob_end_flush();
// override ECHO ALL FALSE
$ECHO_ALL = true;

// use CoreLibs\Debug\Support;
use CoreLibs\Logging\Logger\Level;
use CoreLibs\Logging\Logger\Flag;
// use CoreLibs\Debug\Support;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_level' => Level::Debug,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: DEBUG LOGGING';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$log->logger2Debug();

print "Level 250: " . Level::fromValue(250)->getName() . "<br>";
print "Flag: per_run (from int): " . Flag::fromValue(2)->getName() . "<br>";
print "Flag: per_run getName(): " . Flag::per_class->getName() . "<br>";
print "Flag: per_run ->name: " . Flag::per_class->name . "<br>";
print "Flag: per_run ->value: " . Flag::per_class->value . "<br>";

print "DUMP: " . $log->dV(['something' => 'error']) . "<br>";

$log->debug('LEGACY', 'Some legacy shit here');
$log->debug('ARRAY', 'Dump some data: ' . $log->dV(['something' => 'error']));
$log->debug('MIXED', 'Dump mixed: ' . $log->dV(<<<EOM
Line is
break
with
<html>block</html>
and > and <
EOM));
$log->info('Info message', ['info' => 'log']);
$log->error('Cannot process data', ['error' => 'log']);

print "Log File: " . $log->getLogFile() . "<br>";

// init empty
unset($LOG_FILE_ID);
$ll = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
]);
print "LFI: " . $ll->getLogFileId() . "<br>";
try {
	$ll->setLoggingLevel('Invalid');
} catch (\Psr\Log\InvalidArgumentException $e) {
	print "Invalid option: " . $e->getMessage() . "<br>";
}
/* $ll = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => 'a',
	'log_file_id' => 'a',
]); */

print '<hr>'
	. '<div style="width:100%; font-family: monospace;">'
	// . '<pre>'
	. nl2br(htmlentities(file_get_contents($log->getLogFolder() . $log->getLogFile()) ?: ''))
	// . '</pre>'
	. '</div>';

print "</body></html>";

// __END__
