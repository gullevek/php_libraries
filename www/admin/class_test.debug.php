<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 0;
$ECHO_ALL = true;
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
$LOG_FILE_ID = 'classTest-debug';
ob_end_flush();

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
print "C->PRINTERRORMSG: <br>".$debug->printErrorMsg()."<br>";

class TestL
{
	public $log;
	public function __construct()
	{
		$this->log = new CoreLibs\Debug\Logging();
	}
	public function test()
	{
		$this->log->debug('TESTL', 'Logging in class testL');
		print "IN TestL->test: <br>".$this->log->printErrorMsg()."<br>";
		return true;
	}
}
$tl = new TestL();
print "CLASS SUB: DEBUG: ".$tl->test()."<br>";

// fdebug
print "S::FSETFILENAME: ".FileWriter::fsetFilename('class_test_debug_file.log')."<br>";
print "S::FDEBUG: ".FileWriter::fdebug('CLASS TEST DEBUG FILE: '.date('Y-m-d H:i:s'))."<br>";

// error message
// future DEPRECATED
$basic->debug('BASIC CLASS', 'Debug test');
print "BASIC:<br>".$basic->log->printErrorMsg();

print "</body></html>";

// __END__
