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
// define log file id
$LOG_FILE_ID = 'classTest-datetime';
ob_end_flush();

use CoreLibs\Combined\DateTime;
use CoreLibs\Debug\Support as DgS;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
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
print "<hr>";
$timestamps = [
	1622788315.123456,
	-1622788315.456789
];
foreach ($timestamps as $timestamp) {
	print "DATESTRINGFORMAT(sm:0:0): $timestamp: " . DateTime::dateStringFormat($timestamp) . "<br>";
	print "DATESTRINGFORMAT(sm:1:0): $timestamp: " . DateTime::dateStringFormat($timestamp, true) . "<br>";
	print "DATESTRINGFORMAT(sm:1:1): $timestamp: " . DateTime::dateStringFormat($timestamp, true, true) . "<br>";
}
print "<hr>";
// $interval = 0;
// $interval = 1000000;
// $interval = 123456;
// $interval = 3600;
// $interval = 3601;
// $interval = 86400;
// $interval = 86401;
$interval = (86400 * 606) + 16434.5;
// $interval = 1.5;
// $interval = 123456;
// $interval = 120.1;
// $interval = 1641515890;
// $interval = 0.123456;
// $interval = 1641515890;
// $interval = 999999999999999999;
// $interval = 60;
try {
	print "Test-A: [$interval] "
		. DateTime::intervalStringFormatDeprecated(
			$interval,
			truncate_after: 'd',
			natural_seperator: false,
			name_space_seperator: false,
			show_microseconds: true,
			short_time_name: true,
			skip_last_zero: true,
			skip_zero: false,
			show_only_days: false,
			auto_fix_microseconds: false,
			truncate_nanoseconds: false,
			truncate_zero_seconds_if_microseconds: true,
		)
		// . " => "
		// . DateTime::intervalStringFormat($interval)
		. "<br>";
	print "Test-B: [$interval] "
		. DateTime::intervalStringFormat(
			$interval,
			truncate_after: 'd',
			natural_seperator: false,
			name_space_seperator: false,
			show_microseconds: true,
			short_time_name: true,
			skip_last_zero: true,
			skip_zero: false,
			show_only_days: false,
			auto_fix_microseconds: false,
			truncate_nanoseconds: false,
			truncate_zero_seconds_if_microseconds: true,
		)
		// . " => "
		// . DateTime::intervalStringFormat($interval)
		. "<br>";
	print "DEFAULT-A: " . DateTime::intervalStringFormatDeprecated($interval) . "<br>";
	print "DEFAULT-B: " . DateTime::intervalStringFormat($interval) . "<br>";
	$show_micro = true;
	print "COMPATIBLE Test-A: " .
	DateTime::intervalStringFormatDeprecated(
		$interval,
		show_microseconds: $show_micro,
		show_only_days: true,
		skip_zero: false,
		skip_last_zero: false,
		truncate_nanoseconds: true,
		truncate_zero_seconds_if_microseconds: false
	) . "<br>";
	print "COMPATIBLE Test-B: " .
	DateTime::intervalStringFormat(
		$interval,
		show_microseconds: $show_micro,
		show_only_days: true,
		skip_zero: false,
		skip_last_zero: false,
		truncate_nanoseconds: true,
		truncate_zero_seconds_if_microseconds: false
	) . "<br>";
	print "ORIGINAL: " . DateTime::timeStringFormat($interval, $show_micro) . "<br>";
} catch (\UnexpectedValueException $e) {
	print "ERROR: " . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
} catch (\LengthException $e) {
	print "ERROR interval: " . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
}
print "<hr>";
$intervals = [
	['i' => 0, 'sm' => true],
	['i' => 0.0, 'sm' => true],
	['i' => 1.5, 'sm' => true],
	['i' => 1.05, 'sm' => true],
	['i' => 1.005, 'sm' => true],
	['i' => 1.0005, 'sm' => true],
];
foreach ($intervals as $int) {
	$info = 'ts:' . $int['i'] . '|' . 'sm:' . $int['sm'];
	print "[tsf] ZERO TIME STRING [$info]: "
		. DateTime::timeStringFormat($int['i'], $int['sm']) . "<br>";
	print "[isf] ZERO TIME STRING [$info]: "
		. DateTime::intervalStringFormat($int['i'], show_microseconds:$int['sm']) . "<br>";
}
print "<hr>";
$intervals = [
	[
		'i' => 788315.123456,
		'truncate_after' => '',
		'natural_seperator' => false,
		'name_space_seperator' => false,
		'show_microseconds' => true,
		'short_time_name' => true,
		'skip_last_zero' => false,
		'skip_zero' => true,
		'show_only_days' => false,
		'auto_fix_microseconds' => false,
		'truncate_nanoseconds' => false
	],
	[
		'i' => 788315.123456,
		'truncate_after' => '',
		'natural_seperator' => true,
		'name_space_seperator' => true,
		'show_microseconds' => true,
		'short_time_name' => true,
		'skip_last_zero' => false,
		'skip_zero' => true,
		'show_only_days' => false,
		'auto_fix_microseconds' => false,
		'truncate_nanoseconds' => false
	],
];
foreach ($intervals as $int) {
	$info = $int['i'];
	try {
		print "INTRVALSTRINGFORMAT(sm:0): $info: "
			. DateTime::intervalStringFormat(
				$int['i'],
				truncate_after: (string)$int['truncate_after'],
				natural_seperator: $int['natural_seperator'],
				name_space_seperator: $int['name_space_seperator'],
				show_microseconds: $int['show_microseconds'],
				short_time_name: $int['short_time_name'],
				skip_last_zero: $int['skip_last_zero'],
				skip_zero: $int['skip_zero'],
				show_only_days: $int['show_only_days'],
				auto_fix_microseconds: $int['auto_fix_microseconds'],
				truncate_nanoseconds: $int['truncate_nanoseconds'],
			) . "<br>";
	} catch (\UnexpectedValueException $e) {
		print "ERROR: " . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
	} catch (\LengthException $e) {
		print "ERROR interval: " . $e->getMessage() . "<br><pre>" . $e . "</pre><br>";
	}
}
print "<hr>";
// convert and reverste tests
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
print "<hr>";
$check_dates = [
	'2021-05-01',
	'2021-05-40'
];
foreach ($check_dates as $check_date) {
	print "CHECKDATE: $check_date: " . (string)DateTime::checkDate($check_date) . "<br>";
}
print "<hr>";
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
print "<hr>";
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
print "<hr>";
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
print "<hr>";
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
print "<hr>";
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
print "<hr>";
// check date range includes a weekend
// does not:
$start_date = '2023-07-03';
$end_date = '2023-07-05';
print "Has Weekend: " . $start_date . " ~ " . $end_date . ": "
	. Dgs::prBl(DateTime::dateRangeHasWeekend($start_date, $end_date)) . "<br>";
$start_date = '2023-07-03';
$end_date = '2023-07-10';
print "Has Weekend: " . $start_date . " ~ " . $end_date . ": "
	. Dgs::prBl(DateTime::dateRangeHasWeekend($start_date, $end_date)) . "<br>";
$start_date = '2023-07-03';
$end_date = '2023-07-31';
print "Has Weekend: " . $start_date . " ~ " . $end_date . ": "
	. Dgs::prBl(DateTime::dateRangeHasWeekend($start_date, $end_date)) . "<br>";
$start_date = '2023-07-01';
$end_date = '2023-07-03';
print "Has Weekend: " . $start_date . " ~ " . $end_date . ": "
	. Dgs::prBl(DateTime::dateRangeHasWeekend($start_date, $end_date)) . "<br>";


print "</body></html>";

// __END__
