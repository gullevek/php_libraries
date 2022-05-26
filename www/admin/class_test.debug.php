<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// all the settings are overruled by config
$DEBUG_ALL_OVERRIDE = true; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = false;
$ECHO_ALL = true;
$DB_DEBUG = true;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
$GLOBALS['SET_SESSION_NAME'] = EDIT_SESSION_NAME;
// define log file id
$LOG_FILE_ID = 'classTest-debug';
ob_end_flush();
// override ECHO ALL FALSE
$ECHO_ALL = true;

use CoreLibs\Debug\Support as DebugSupport;
use CoreLibs\Debug\FileWriter;

$debug = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	'debug_all' => $DEBUG_ALL,
	'print_all' => $PRINT_ALL,
	'echo_all' => $ECHO_ALL,
]);
$debug_support_class = 'CoreLibs\Debug\Support';
$debug_logging_class = 'CoreLibs\Debug\Logging';

$PAGE_NAME = 'TEST CLASS: DEBUG';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

function test()
{
	return DebugSupport::getCallerMethod(1);
}

function test2()
{
	return DebugSupport::getCallerMethodList(1);
}

print "S::GETCALLERMETHOD: " . DebugSupport::getCallerMethod(0) . "<br>";
print "S::GETCALLERMETHOD: " . test() . "<br>";
print "S::GETCALLERMETHODLIST: <pre>" . print_r(test2(), true) . "</pre><br>";
print "S::PRINTAR: " . DebugSupport::printAr(['Foo', 'Bar']) . "<br>";
print "V-S::PRINTAR: " . $debug_support_class::printAr(['Foo', 'Bar']) . "<br>";
print "S::DEBUSTRING(s): " . DebugSupport::debugString('SET') . "<br>";
print "S::DEBUSTRING(''): " . DebugSupport::debugString('') . "<br>";
print "S::DEBUSTRING(,s): " . DebugSupport::debugString(null, '{-}') . "<br>";

// get test
print "LOG FOLDER: " . $debug->getSetting('log_folder') . "<br>";

// debug
print "C->DEBUG: " . $debug->debug('CLASS-TEST-DEBUG', 'Class Test Debug') . "<br>";
print "C->DEBUG(html): " . $debug->debug('CLASS-TEST-DEBUG', 'HTML TAG<br><b>BOLD</b>') . "<br>";
print "C->DEBUG(html,strip): " . $debug->debug('CLASS-TEST-DEBUG', 'HTML TAG<br><b>BOLD</b>', true) . "<br>";
print "C->PRINTERRORMSG: <br>" . $debug->printErrorMsg() . "<br>";

echo "<b>OPTIONS DEBUG CALL</b><br>";

// new log type with options
$new_log = new CoreLibs\Debug\Logging([
	'log_folder' => '../log/',
	'file_id' => 'DebugTestNewLogger',
	// add file date
	'print_file_date' => true,
	// split into level (debug code)
	'per_level' => false,
	// per class called
	'per_class' => false,
	// per page
	'per_page' => false,
	// for each page call
	'per_run' => false,
	// set debug and print flags
	'debug_all' => true,
	'echo_all' => true,
	'print_all' => true,
]);
$new_log->debug('OPTIONS TYPE', 'New Type error');
print "OPTIONS LOGGER:<br>" . $new_log->printErrorMsg();
$new_log->setLogLevel('debug', 'on', ['A', 'B', 'C' => false]);
print "LOG LEVEL: " .  DebugSupport::printAr($new_log->getLogLevel('debug', 'on')) . "<br>";

echo "<b>CLASS DEBUG CALL</b><br>";

// @codingStandardsIgnoreLine
class TestL
{
	public $log;
	public function __construct()
	{
		$this->log = new CoreLibs\Debug\Logging();
	}
	public function test(string $ts = null)
	{
		print "* GETCALLERCLASS(INSIDE CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";
		$this->log->debug('TESTL', 'Logging in class testL' . ($ts !== null ? ': ' . $ts : ''));
		$this->log->debug('TESTL', 'Some other message');
		return true;
	}
}
// @codingStandardsIgnoreLine
class TestR extends TestL
{
	public $foo;
	public function __construct()
	{
		parent::__construct();
	}
	public function subTest()
	{
		print "** GETCALLERCLASS(INSIDE EXTND CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";
		$this->log->debug('TESTR', 'Logging in class testR (extends testL)');
		$this->test('TESTR INSIDE');
		$this->log->debug('TESTR', 'Array: '
			. $this->log->prAr(['a', 'b']) . ', Other: ' . $this->log->prAr(['a', 'b']));
		return true;
	}
}
$tl = new TestL();
print "CLASS: LOG ECHO: " . (string)$tl->log->getLogLevelAll('echo') . "<br>";
print "CLASS: DEBUG: " . $tl->test() . "<br>";
print "CLASS: PRINTERRORMSG: <br>" . $tl->log->printErrorMsg() . "<br>";
$tr = new TestR();
print "CLASS: LOG ECHO: " . (string)$tr->log->getLogLevelAll('echo') . "<br>";
print "CLASS EXTEND: DEBUG/tl: " . $tr->test('TESTR OUTSIDE') . "<br>";
print "CLASS EXTEND: DEBUG/tr: " . $tr->subTest() . "<br>";
print "CLASS EXTEND: PRINTERRORMSG: <br>" . $tr->log->printErrorMsg() . "<br>";

// test attaching a logger from outside
// @codingStandardsIgnoreLine
class AttachOutside
{
	public $log;
	public function __construct(object $logger_class)
	{
		$this->log = $logger_class;
	}
	public function test()
	{
		$this->log->debug('ATTACHOUTSIDE', 'A test');
		return get_class($this);
	}
}
$ao = new AttachOutside($debug);
print "AO-CLASS: DEBUG: " . $ao->test() . "<br>";

print "GETCALLERCLASS(NON CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";

// fdebug
print "S::FSETFILENAME: " . FileWriter::fsetFilename('class_test_debug_file.log') . "<br>";
print "S::FDEBUG: " . FileWriter::fdebug('CLASS TEST DEBUG FILE: ' . date('Y-m-d H:i:s')) . "<br>";

// test per level
$debug->setLogPer('level', true);
$debug->debug('TEST PER LEVEL', 'Per level test');
$debug->debug('()', 'Per level test: invalid chars');
$debug->setLogPer('level', false);

$ar = ['A', 1, ['B' => 'D']];
$debug->debug('ARRAY', 'Array: ' . $debug->prAr($ar));
$debug->debug('BOOL', 'True: ' . $debug->prBl(true) . ', False: ' . $debug->prBl(false));

// error message
// future DEPRECATED
// $debug->debug('BASIC CLASS', 'Debug test');
$debug->debug('BASIC CLASS', 'Debug test');
print "BASIC PRINTERRORMSG:<br>" . $debug->printErrorMsg();

print "</body></html>";

// __END__
