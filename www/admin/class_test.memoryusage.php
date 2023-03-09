<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-memoryusage';
ob_end_flush();

use CoreLibs\Debug\MemoryUsage;
use CoreLibs\Debug\Support;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL,
]);

$PAGE_NAME = 'TEST CLASS: MEMORY USAGE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

MemoryUsage::debugMemoryFlag(true);
print "Debug Flag: " . Support::printBool(MemoryUsage::debugMemoryFlag()) . "<br>";
MemoryUsage::setStartMemory();
MemoryUsage::setMemory();
$data = MemoryUsage::memoryUsage('first run');
print "Memory usage 1 array: " . Support::printAr($data) . "<br>";
print "Memory usage 1 string: " . MemoryUsage::printMemoryUsage($data) . "<br>";
print "Memory usage 1 string raw: " . MemoryUsage::printMemoryUsage($data, true) . "<br>";
$var = 'foo';
$out = '';
for ($i = 1; $i <= 100; $i++) {
	$out .= $var;
}
$data = MemoryUsage::memoryUsage('second run');
print "Memory usage 2 array: " . Support::printAr($data) . "<br>";
print "Memory usage 2 string: " . MemoryUsage::printMemoryUsage($data) . "<br>";
print "Memory usage 2 string raw: " . MemoryUsage::printMemoryUsage($data, true) . "<br>";
MemoryUsage::setMemory();
$var = 'foasdfasdfasdfasdfasdfo';
$out = '';
for ($i = 1; $i <= 10000; $i++) {
	$out .= $var;
}
$data = MemoryUsage::memoryUsage('third run');
print "Memory usage 3 array: " . Support::printAr($data) . "<br>";
print "Memory usage 3 string: " . MemoryUsage::printMemoryUsage($data) . "<br>";
print "Memory usage 3 string raw: " . MemoryUsage::printMemoryUsage($data, true) . "<br>";
$var = 'foasdfasdfasdasdfasdfasdfadfadfasdfasdfo';
$out = '';
for ($i = 1; $i <= 100000; $i++) {
	$out .= $var;
}
$data = MemoryUsage::memoryUsage('forth run');
print "Memory usage 4 array: " . Support::printAr($data) . "<br>";
print "Memory usage 4 string: " . MemoryUsage::printMemoryUsage($data) . "<br>";
print "Memory usage 4 string raw: " . MemoryUsage::printMemoryUsage($data, true) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
