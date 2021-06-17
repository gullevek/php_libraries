<?php // phpcs:ignore warning
declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

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
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-debug';
ob_end_flush();
// override ECHO ALL FALSE
$ECHO_ALL = true;

use CoreLibs\Debug\Support as DebugSupport;
use CoreLibs\Debug\FileWriter;

$basic = new CoreLibs\Basic();
$debug = new CoreLibs\Debug\Logging();
$debug_support_class = 'CoreLibs\Debug\Support';
$debug_logging_class = 'CoreLibs\Debug\Logging';

print "<html><head><title>TEST CLASS: DEBUG</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

function test()
{
	return DebugSupport::getCallerMethod(1);
}

print "S::GETCALLERMETHOD: ".DebugSupport::getCallerMethod(0)."<br>";
print "S::GETCALLERMETHOD: ".test()."<br>";
print "S::PRINTAR: ".DebugSupport::printAr(['Foo', 'Bar'])."<br>";
print "V-S::PRINTAR: ".$debug_support_class::printAr(['Foo', 'Bar'])."<br>";

// debug
print "C->DEBUG: ".$debug->debug('CLASS-TEST-DEBUG', 'Class Test Debug')."<br>";
print "C->DEBUG(html): ".$debug->debug('CLASS-TEST-DEBUG', 'HTML TAG<br><b>BOLD</b>')."<br>";
print "C->DEBUG(html,strip): ".$debug->debug('CLASS-TEST-DEBUG', 'HTML TAG<br><b>BOLD</b>', true)."<br>";
print "C->PRINTERRORMSG: <br>".$debug->printErrorMsg()."<br>";
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
		print "* GETCALLERCLASS(INSIDE CLASS): ".\CoreLibs\Debug\Support::getCallerClass()."<br>";
		$this->log->debug('TESTL', 'Logging in class testL'.($ts !== null ? ': '.$ts : ''));
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
		print "** GETCALLERCLASS(INSIDE EXTND CLASS): ".\CoreLibs\Debug\Support::getCallerClass()."<br>";
		$this->log->debug('TESTR', 'Logging in class testR (extends testL)');
		$this->test('TESTR INSIDE');
		$this->log->debug('TESTR', 'Array: '.$this->log->prAr(['a', 'b']).', Other: '.$this->log->prAr(['a', 'b']));
		return true;
	}
}
$tl = new TestL();
print "CLASS: LOG ECHO: ".(string)$tl->log->getLogLevelAll('echo')."<br>";
print "CLASS: DEBUG: ".$tl->test()."<br>";
print "CLASS: PRINTERRORMSG: <br>".$tl->log->printErrorMsg()."<br>";
$tr = new TestR();
print "CLASS: LOG ECHO: ".(string)$tr->log->getLogLevelAll('echo')."<br>";
print "CLASS EXTEND: DEBUG/tl: ".$tr->test('TESTR OUTSIDE')."<br>";
print "CLASS EXTEND: DEBUG/tr: ".$tr->subTest()."<br>";
print "CLASS EXTEND: PRINTERRORMSG: <br>".$tr->log->printErrorMsg()."<br>";

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
$ao = new AttachOutside($basic->log);
print "AO-CLASS: DEBUG: ".$ao->test()."<br>";

print "GETCALLERCLASS(NON CLASS): ".\CoreLibs\Debug\Support::getCallerClass()."<br>";

// fdebug
print "S::FSETFILENAME: ".FileWriter::fsetFilename('class_test_debug_file.log')."<br>";
print "S::FDEBUG: ".FileWriter::fdebug('CLASS TEST DEBUG FILE: '.date('Y-m-d H:i:s'))."<br>";

// error message
// future DEPRECATED
// $basic->debug('BASIC CLASS', 'Debug test');
$basic->log->debug('BASIC CLASS', 'Debug test');
print "BASIC PRINTERRORMSG:<br>".$basic->log->printErrorMsg();

print "</body></html>";

// __END__
