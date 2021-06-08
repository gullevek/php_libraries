<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
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
$LOG_FILE_ID = 'classTest-runningtime';
ob_end_flush();

use CoreLibs\Debug\RunningTime;

$basic = new CoreLibs\Basic();

print "<html><head><title>TEST CLASS: RUNNING IMTE</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

RunningTime::hrRunningTime();
RunningTime::runningTime();
echo "RANDOM KEY [50]: ".\CoreLibs\Create\RandomKey::randomKeyGen(50)."<br>";
echo "TIMED [hr]: ".RunningTime::hrRunningTime()."<br>";
echo "TIMED [def]: ".RunningTime::runningTime()."<br>";
echo "TIMED [string]: ".RunningTime::runningTimeString()."<br>";
RunningTime::hrRunningTime();
echo "RANDOM KEY [default]: ".\CoreLibs\Create\RandomKey::randomKeyGen()."<br>";
echo "TIMED [hr]: ".RunningTime::hrRunningTime()."<br>";

// DEPRECATED
/* $basic->hrRunningTime();
$basic->runningTime();
echo "RANDOM KEY [50]: ".$basic->randomKeyGen(50)."<br>";
echo "TIMED [hr]: ".$basic->hrRunningTime()."<br>";
echo "TIMED [def]: ".$basic->runningTime()."<br>";
echo "TIMED [string]: ".$basic->runningtime_string."<br>";
$basic->hrRunningTime();
echo "RANDOM KEY [default]: ".$basic->randomKeyGen()."<br>";
echo "TIMED [hr]: ".$basic->hrRunningTime()."<br>"; */

// error message
print $basic->printErrorMsg();

print "</body></html>";

// __END__
