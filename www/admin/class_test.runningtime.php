<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-runningtime';
ob_end_flush();

use CoreLibs\Debug\RunningTime;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);

$PAGE_NAME = 'TEST CLASS: RUNNING TIME';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

RunningTime::hrRunningTime();
RunningTime::runningTime();
echo "RANDOM KEY [50]: " . \CoreLibs\Create\RandomKey::randomKeyGen(50) . "<br>";
echo "TIMED [hr]: " . RunningTime::hrRunningTime() . "<br>";
echo "TIMED [def]: " . RunningTime::runningTime() . "<br>";
echo "TIMED [string]: " . RunningTime::runningTimeString() . "<br>";
RunningTime::hrRunningTime();
echo "TIMED [hr-end] " . RunningTime::hrRunningTimeFromStart() . "<br>";
echo "<br>";
echo "RANDOM KEY [default]: " . \CoreLibs\Create\RandomKey::randomKeyGen() . "<br>";
echo "TIMED 1 [hr]: " . RunningTime::hrRunningTime() . "<br>";
echo "TIMED 1 [hr-n]: " . RunningTime::hrRunningTime() . "<br>";
echo "TIMED 1 [hr-b]: " . RunningTime::hrRunningTime() . "<br>";
echo "TIMED 1 [hr-end]: " . RunningTime::hrRunningTimeFromStart() . "<br>";
RunningTime::hrRunningTimeReset();
RunningTime::hrRunningTime();
echo "TIMED 2 [hr]: " . RunningTime::hrRunningTime() . "<br>";
echo "TIMED 2 [hr-end]: " . RunningTime::hrRunningTimeFromStart() . "<br>";

print "</body></html>";

// __END__
