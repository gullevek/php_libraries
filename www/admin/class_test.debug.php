<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// all the settings are overruled by config
$DEBUG_ALL = true;
$PRINT_ALL = false;
$ECHO_ALL = true;
$DB_DEBUG = true;

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

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

$debug = new CoreLibs\Debug\LoggingLegacy([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	'debug_all' => $DEBUG_ALL,
	'print_all' => $PRINT_ALL,
	'echo_all' => $ECHO_ALL,
]);
$debug_support_class = 'CoreLibs\Debug\Support';
$debug_logging_class = 'CoreLibs\Debug\LoggingLegacy';

$PAGE_NAME = 'TEST CLASS: DEBUG';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

/**
 * Undocumented function
 *
 * @return string|null
 */
function test(): ?string
{
	return DebugSupport::getCallerMethod(1);
}

/**
 * Undocumented function
 *
 * @return array<mixed>
 */
function test2(): array
{
	return DebugSupport::getCallerMethodList(1);
}

print "S::GETCALLERMETHOD: " . DebugSupport::getCallerMethod(0) . "<br>";
print "S::GETCALLERMETHOD: " . test() . "<br>";
print "S::GETCALLERMETHODLIST: <pre>" . print_r(test2(), true) . "</pre><br>";
// printAr
print "S::PRINTAR: " . DebugSupport::printAr(['Foo', 'Bar']) . "<br>";
print "V-S::PRINTAR: " . $debug_support_class::printAr(['Foo', 'Bar']) . "<br>";
// printBool
print "S::PRINTBOOL(default): " . DebugSupport::printBool(true) . "<br>";
print "S::PRINTBOOL(name): " . DebugSupport::printBool(true, 'Name') . "<br>";
print "S::PRINTBOOL(name, ok): " . DebugSupport::printBool(true, 'Name', 'ok') . "<br>";
print "S::PRINTBOOL(name, ok, not): " . DebugSupport::printBool(false, 'Name', 'ok', 'not') . "<br>";
// debugString
print "S::DEBUSTRING(s): " . DebugSupport::debugString('SET') . "<br>";
print "S::DEBUSTRING(s): " . DebugSupport::debugString('SET') . "<br>";
print "S::DEBUSTRING(s&gt;): " . DebugSupport::debugString('<SET>') . "<br>";
print "S::DEBUSTRING(''): " . DebugSupport::debugString('') . "<br>";
print "S::DEBUSTRING(,s): " . DebugSupport::debugString(null, '{-}') . "<br>";
// dumpVar
print "S::DUMPVAR(s): " . DebugSupport::dumpVar('SET') . "<br>";
print "S::DUMPVAR(s,true): " . DebugSupport::dumpVar('SET', true) . "<br>";
print "S::DUMPVAR(s&gt;): " . DebugSupport::dumpVar('<SET>') . "<br>";
print "S::DUMPVAR(s&gt;,true): " . DebugSupport::dumpVar('<SET>', true) . "<br>";
print "S::DUMPVAR(''): " . DebugSupport::dumpVar('') . "<br>";
print "S::DUMPVAR(,s): " . DebugSupport::dumpVar(null) . "<br>";
print "S::DUMPVAR([a,b]): " . DebugSupport::dumpVar(['a', 'b']) . "<br>";
print "S::DUMPVAR([a,b],true): " . DebugSupport::dumpVar(['a', 'b'], true) . "<br>";
print "S::DUMPVAR([MIXED]): " . DebugSupport::dumpVar(<<<EOM
Line is
break
with
<html>block</html>
and > and <
EOM) . "<br>";
// exportVar
// print "S::EXPORTVAR(s): " . DebugSupport::exportVar('SET') . "<br>";
// print "S::EXPORTVAR(s&gt;): " . DebugSupport::exportVar('<SET>') . "<br>";
// print "S::EXPORTVAR(''): " . DebugSupport::exportVar('') . "<br>";
// print "S::EXPORTVAR(,s): " . DebugSupport::exportVar(null) . "<br>";
// print "S::EXPORTVAR([a,b]): " . DebugSupport::exportVar(['a', 'b']) . "<br>";
// print "S::EXPORTVAR([a,b],true): " . DebugSupport::exportVar(['a', 'b']) . "<br>";

// get test
print "LOG FOLDER: " . $debug->getSetting('log_folder') . "<br>";

// debug
print "C->DEBUG: " . $debug->debug('CLASS-TEST-DEBUG', 'Class Test Debug') . "<br>";
print "C->DEBUG(html): " . $debug->debug('CLASS-TEST-DEBUG', 'HTML TAG<br><b>BOLD</b>') . "<br>";
print "C->DEBUG(html,strip): " . $debug->debug('CLASS-TEST-DEBUG', 'HTML TAG<br><b>BOLD</b>', true) . "<br>";
print "C->PRINTERRORMSG: <br>" . $debug->printErrorMsg() . "<br>";

echo "<b>OPTIONS DEBUG CALL</b><br>";

// new log type with options
$new_log = new CoreLibs\Debug\LoggingLegacy([
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
print "LOG LEVEL: " .  DebugSupport::printAr(\CoreLibs\Convert\SetVarType::setArray(
	$new_log->getLogLevel('debug', 'on')
)) . "<br>";

echo "<b>CLASS DEBUG CALL</b><br>";

// @codingStandardsIgnoreLine
class TestL
{
	/** @var \CoreLibs\Debug\LoggingLegacy */
	public $log;
	public function __construct()
	{
		$this->log = new CoreLibs\Debug\LoggingLegacy([
			'log_folder' => '../log/',
			'file_id' => 'DebugTestTestLLogger',
		]);
	}
	/**
	 * Undocumented function
	 *
	 * @param  string|null $ts
	 * @return bool
	 */
	public function test(?string $ts = null): bool
	{
		print "* GETCALLERCLASS(INSIDE CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";
		print "* GETCALLERTOPCLASS(INSIDE CLASS): " . \CoreLibs\Debug\Support::getCallerTopLevelClass() . "<br>";
		print "* GETCALLSTACK(INSIDE CLASS): <pre>"
			. DebugSupport::prAr(\CoreLibs\Debug\Support::getCallStack()) . "</pre><br>";
		$this->log->debug('TESTL', 'Logging in class testL' . ($ts !== null ? ': ' . $ts : ''));
		$this->log->debug('TESTL', 'Some other message');
		return true;
	}
}
// @codingStandardsIgnoreLine
class TestR extends TestL
{
	/** @var string */
	public $foo;
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function subTest(): bool
	{
		print "** GETCALLERCLASS(INSIDE EXTND CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";
		print "** GETCALLERTOPCLASS(INSIDE EXTND CLASS): " . \CoreLibs\Debug\Support::getCallerTopLevelClass() . "<br>";
		print "** GETCALLSTACK(INSIDE EXTND CLASS): <pre>"
			. DebugSupport::prAr(\CoreLibs\Debug\Support::getCallStack()) . "</pre><br>";
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
	/** @var \CoreLibs\Debug\LoggingLegacy */
	public $log;
	public function __construct(\CoreLibs\Debug\LoggingLegacy $logger_class)
	{
		$this->log = $logger_class;
	}
	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function test(): string
	{
		$this->log->debug('ATTACHOUTSIDE', 'A test');
		return get_class($this);
	}
}
$ao = new AttachOutside($debug);
print "AO-CLASS: DEBUG: " . $ao->test() . "<br>";

print "GETCALLERCLASS(NON CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";

// fdebug
print "S::FSETFILENAME: " . FileWriter::fsetFolder(BASE . LOG) . "<br>";
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
$debug->debug('MIXED', 'ANYTHING: ' . DebugSupport::dumpVar([
	'foo' => 1,
	'bar' => null,
	'self' => [1, 2, 3],
	'other' => false
]));

// error message
// future DEPRECATED
// $debug->debug('BASIC CLASS', 'Debug test');
$debug->debug('BASIC CLASS', 'Debug test');
print "BASIC PRINTERRORMSG:<br>" . $debug->printErrorMsg();

print "</body></html>";

// __END__
