<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', false);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-datetime';
ob_end_flush();

use CoreLibs\Combined\DateTime;
use CoreLibs\Debug\Support as DgS;

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
$datetime_class = 'CoreLibs\Combined\DateTime';

$PAGE_NAME = 'TEST CLASS: DATE/TIME';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

// class
$timestamp = 1622788315.123456;
// static
print "S::DATESTRINGFORMAT(sm:0): $timestamp: " . $datetime_class::dateStringFormat($timestamp) . "<br>";

// time string thest
$timestamp = 5887998.33445;
$time_string = DateTime::timeStringFormat($timestamp);
print "PLANE TIME STRING: " . $timestamp . "<br>";
print "TIME STRING TEST: " . $time_string . "<br>";
print "REVERSE TIME STRING: " . DateTime::stringToTime($time_string) . "<br>";
if (round($timestamp, 4) == DateTime::stringToTime($time_string)) {
	print "REVERSE TIME STRING MATCH<br>";
} else {
	print "REVERSE TRIME STRING DO NOT MATCH<br>";
}
print "ZERO TIME STRING: " . DateTime::timeStringFormat(0, true) . "<br>";
print "ZERO TIME STRING: " . DateTime::timeStringFormat(0.0, true) . "<br>";
print "ZERO TIME STRING: " . DateTime::timeStringFormat(1.005, true) . "<br>";

$timestamps = [
	1622788315.123456,
	-1622788315.456789
];
foreach ($timestamps as $timestamp) {
	print "DATESTRINGFORMAT(sm:0:0): $timestamp: " . DateTime::dateStringFormat($timestamp) . "<br>";
	print "DATESTRINGFORMAT(sm:1:0): $timestamp: " . DateTime::dateStringFormat($timestamp, true) . "<br>";
	print "DATESTRINGFORMAT(sm:1:1): $timestamp: " . DateTime::dateStringFormat($timestamp, true, true) . "<br>";
}
$intervals = [
	788315.123456,
	-123.456
];
foreach ($intervals as $interval) {
	print "TIMESTRINGFORMAT(sm:0): $interval: " . DateTime::timeStringFormat($interval, false) . "<br>";
	$reverse_interval = DateTime::timeStringFormat($interval);
	print "TIMESTRINGFORMAT(sm:1): $interval: " . $reverse_interval . "<br>";
	print "STRINGTOTIME: $reverse_interval: " . DateTime::stringToTime($reverse_interval) . "<br>";
}
$check_dates = [
	'2021-05-01',
	'2021-05-40'
];
foreach ($check_dates as $check_date) {
	print "CHECKDATE: $check_date: " . (string)DateTime::checkDate($check_date) . "<br>";
}
$check_datetimes = [
	'2021-05-01',
	'2021-05-40',
	'2021-05-01 12:13:14',
	'2021-05-40 12:13:14',
	'2021-05-01 25:13:14',
];
foreach ($check_datetimes as $check_datetime) {
	print "CHECKDATETIME: $check_datetime: " . (string)DateTime::checkDateTime($check_datetime) . "<br>";
}
$compare_dates = [
	[ '2021-05-01', '2021-05-02', ],
	[ '2021-05-02', '2021-05-01', ],
	[ '2021-05-02', '2021-05-02', ],
	[ '2017/1/5', '2017-01-05', ],
];
// compareDate
foreach ($compare_dates as $compare_date) {
	print "COMPAREDATE: $compare_date[0] = $compare_date[1]: "
		. (string)DateTime::compareDate($compare_date[0], $compare_date[1]) . "<br>";
}
$compare_datetimes = [
	[ '2021-05-01', '2021-05-02', ],
	[ '2021-05-02', '2021-05-01', ],
	[ '2021-05-02', '2021-05-02', ],
	[ '2021-05-01 10:00:00', '2021-05-01 11:00:00', ],
	[ '2021-05-01 11:00:00', '2021-05-01 10:00:00', ],
	[ '2021-05-01 10:00:00', '2021-05-01 10:00:00', ],
];
foreach ($compare_datetimes as $compare_datetime) {
	print "COMPAREDATE: $compare_datetime[0] = $compare_datetime[1]: "
		. (string)DateTime::compareDateTime($compare_datetime[0], $compare_datetime[1]) . "<br>";
}
$compare_dates = [
	[ '2021-05-01', '2021-05-10', ],
	[ '2021-05-10', '2021-05-01', ],
	[ '2021-05-02', '2021-05-01', ],
	[ '2021-05-02', '2021-05-02', ],
];
foreach ($compare_dates as $compare_date) {
	print "CALCDAYSINTERVAL: $compare_date[0] = $compare_date[1]: "
		. DgS::printAr(DateTime::calcDaysInterval($compare_date[0], $compare_date[1])) . "<br>";
	print "CALCDAYSINTERVAL(named): $compare_date[0] = $compare_date[1]: "
		. DgS::printAr(DateTime::calcDaysInterval($compare_date[0], $compare_date[1], true)) . "<br>";
}

// test date conversion
$dow = 2;
print "DOW[$dow]: " . DateTime::setWeekdayNameFromIsoDow($dow) . "<br>";
print "DOW[$dow],long: " . DateTime::setWeekdayNameFromIsoDow($dow, true) . "<br>";
$date = '2022-7-22';
print "DATE-dow[$date]: " . DateTime::setWeekdayNameFromDate($date) . "<br>";
print "DATE-dow[$date],long: " . DateTime::setWeekdayNameFromDate($date, true) . "<br>";
print "DOW-date[$date]: " . DateTime::setWeekdayNumberFromDate($date) . "<br>";
$dow = 11;
print "DOW[$dow];invalid: " . DateTime::setWeekdayNameFromIsoDow($dow) . "<br>";
print "DOW[$dow],long;invalid: " . DateTime::setWeekdayNameFromIsoDow($dow, true) . "<br>";
$date = '2022-70-242';
print "DATE-dow[$date];invalid: " . DateTime::setWeekdayNameFromDate($date) . "<br>";
print "DATE-dow[$date],long;invalid: " . DateTime::setWeekdayNameFromDate($date, true) . "<br>";
print "DOW-date[$date];invalid: " . DateTime::setWeekdayNumberFromDate($date) . "<br>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__
