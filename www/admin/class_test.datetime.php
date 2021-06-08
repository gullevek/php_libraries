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
$LOG_FILE_ID = 'classTest-datetime';
ob_end_flush();

use CoreLibs\Combined\DateTime;

$basic = new CoreLibs\Basic();
$datetime_class = 'CoreLibs\Combination\DateTime';

print "<html><head><title>TEST CLASS: DATE/TIME</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// class
$timestamp = 1622788315.123456;
// static
print "S::DATESTRINGFORMAT(sm:0): $timestamp: ".$datetime_class::dateStringFormat($timestamp)."<br>";

// time string thest
$timestamp = 5887998.33445;
$time_string = DateTime::timeStringFormat($timestamp);
print "PLANE TIME STRING: ".$timestamp."<br>";
print "TIME STRING TEST: ".$time_string."<br>";
print "REVERSE TIME STRING: ".DateTime::stringToTime($time_string)."<br>";
if (round($timestamp, 4) == DateTime::stringToTime($time_string)) {
	print "REVERSE TIME STRING MATCH<br>";
} else {
	print "REVERSE TRIME STRING DO NOT MATCH<br>";
}
print "ZERO TIME STRING: ".DateTime::timeStringFormat(0, true)."<br>";
print "ZERO TIME STRING: ".DateTime::timeStringFormat(0.0, true)."<br>";
print "ZERO TIME STRING: ".DateTime::timeStringFormat(1.005, true)."<br>";

$timestamps = [
	1622788315.123456,
	-1622788315.456789
];
foreach ($timestamps as $timestamp) {
	print "DATESTRINGFORMAT(sm:0): $timestamp: ".DateTime::dateStringFormat($timestamp)."<br>";
	print "DATESTRINGFORMAT(sm:1): $timestamp: ".DateTime::dateStringFormat($timestamp, true)."<br>";
}
$intervals = [
	788315.123456,
	-123.456
];
foreach ($intervals as $interval) {
	print "TIMESTRINGFORMAT(sm:0): $interval: ".DateTime::timeStringFormat($interval, false)."<br>";
	$reverse_interval = DateTime::timeStringFormat($interval);
	print "TIMESTRINGFORMAT(sm:1): $interval: ".$reverse_interval."<br>";
	print "STRINGTOTIME: $reverse_interval: ".DateTime::stringToTime($reverse_interval)."<br>";
}
$check_dates = [
	'2021-05-01',
	'2021-05-40'
];
foreach ($check_dates as $check_date) {
	print "CHECKDATE: $check_date: ".(string)DateTime::checkDate($check_date)."<br>";
}
$check_datetimes = [
	'2021-05-01',
	'2021-05-40',
	'2021-05-01 12:13:14',
	'2021-05-40 12:13:14',
	'2021-05-01 25:13:14',
];
foreach ($check_datetimes as $check_datetime) {
	print "CHECKDATETIME: $check_datetime: ".(string)DateTime::checkDateTime($check_datetime)."<br>";
}
$compare_dates = [
	[ '2021-05-01', '2021-05-02', ],
	[ '2021-05-02', '2021-05-01', ],
	[ '2021-05-02', '2021-05-02', ],
	[ '2017/1/5', '2017-01-05', ],
];
// compareDate
foreach ($compare_dates as $compare_date) {
	print "COMPAREDATE: $compare_date[0] = $compare_date[1]: ".(string)DateTime::compareDate($compare_date[0], $compare_date[1])."<br>";
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
	print "COMPAREDATE: $compare_datetime[0] = $compare_datetime[1]: ".(string)DateTime::compareDateTime($compare_datetime[0], $compare_datetime[1])."<br>";
}
$compare_dates = [
	[ '2021-05-01', '2021-05-10', ],
	[ '2021-05-10', '2021-05-01', ],
	[ '2021-05-02', '2021-05-01', ],
	[ '2021-05-02', '2021-05-02', ],
];
foreach ($compare_dates as $compare_date) {
	print "CALCDAYSINTERVAL: $compare_date[0] = $compare_date[1]: ".$basic->printAr(DateTime::calcDaysInterval($compare_date[0], $compare_date[1]))."<br>";
	print "CALCDAYSINTERVAL(named): $compare_date[0] = $compare_date[1]: ".$basic->printAr(DateTime::calcDaysInterval($compare_date[0], $compare_date[1], true))."<br>";
}

// DEPRECATED
/* $timestamp = 1622788315.123456;
print "C->DATESTRINGFORMAT(sm:0): $timestamp: ".$basic->dateStringFormat($timestamp)."<br>";
$interval = 788315.123456;
$reverse_interval = $basic->timeStringFormat($interval);
print "TIMESTRINGFORMAT(sm:1): $interval: ".$reverse_interval."<br>";
print "STRINGTOTIME: $reverse_interval: ".$basic->stringToTime($reverse_interval)."<br>";
$check_date = '2021-05-01';
print "CHECKDATE: $check_date: ".(string)$basic->checkDate($check_date)."<br>";
$check_datetime = '2021-05-01 12:13:14';
print "CHECKDATETIME: $check_datetime: ".(string)$basic->checkDateTime($check_datetime)."<br>";
$compare_date = ['2021-05-01', '2021-05-02'];
print "COMPAREDATE: $compare_date[0] = $compare_date[1]: ".(string)$basic->compareDate($compare_date[0], $compare_date[1])."<br>";
$compare_datetime = ['2021-05-01 10:00:00', '2021-05-01 11:00:00'];
print "COMPAREDATE: $compare_datetime[0] = $compare_datetime[1]: ".(string)$basic->compareDateTime($compare_datetime[0], $compare_datetime[1])."<br>";
$compare_date = ['2021-05-01', '2021-05-10'];
print "CALCDAYSINTERVAL(named): $compare_date[0] = $compare_date[1]: ".$basic->printAr($basic->calcDaysInterval($compare_date[0], $compare_date[1], true))."<br>"; */

// error message
print $basic->printErrorMsg();

print "</body></html>";

// __END__
