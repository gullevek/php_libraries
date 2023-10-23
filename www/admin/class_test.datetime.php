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
$interval = 3601;
// $interval = 86400;
// $interval = 86401;
// $interval = (86400 * 606) + 16434.5;
// $interval = 1.5;
// $interval = 123456;
// $interval = 120.1;
// $interval = 1641515890;
// $interval = 0.123456;
// $interval = 1641515890;
// $interval = 999999999999999999;
// $interval = 60;
try {
	// print "Test-DEP: [$interval] "
	// 	. intervalStringFormatDeprecated(
	// 		$interval,
	// 		truncate_after: '',
	// 		natural_seperator: false,
	// 		name_space_seperator: false,
	// 		show_microseconds: true,
	// 		short_time_name: true,
	// 		skip_last_zero: true,
	// 		skip_zero: false,
	// 		show_only_days: false,
	// 		auto_fix_microseconds: false,
	// 		truncate_nanoseconds: false,
	// 		truncate_zero_seconds_if_microseconds: true,
	// 	)
	// 	// . " => "
	// 	// . DateTime::intervalStringFormat($interval)
	// 	. "<br>";
	print "Test-ACT: [$interval] "
		. DateTime::intervalStringFormat(
			$interval,
			truncate_after: '',
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
		. " => "
		. DateTime::intervalStringFormat($interval)
		. "<br>";
	print "DEFAULT-DEP: " . intervalStringFormatDeprecated($interval) . "<br>";
	print "DEFAULT-ACT: " . DateTime::intervalStringFormat($interval) . "<br>";
	$show_micro = true;
	// print "COMPATIBLE Test-DEP: " .
	// intervalStringFormatDeprecated(
	// 	$interval,
	// 	show_microseconds: $show_micro,
	// 	show_only_days: true,
	// 	skip_zero: false,
	// 	skip_last_zero: false,
	// 	truncate_nanoseconds: true,
	// 	truncate_zero_seconds_if_microseconds: false
	// ) . "<br>";
	print "COMPATIBLE Test-ACT: " .
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

/**
 * DEPREACTED, original rewrite, do not use
 *
 * update timeStringFormat with year and month support
 *
 * The following flags have to be set to be timeStringFormat compatible.
 * Not that on seconds overflow this method will throw an exception, timeStringFormat returned -1s
 * show_only_days: true,
 * skip_zero: false,
 * skip_last_zero: false,
 * truncate_nanoseconds: true,
 * truncate_zero_seconds_if_microseconds: false
 *
 * @param  int|float $seconds                        Seconds to convert, maxium 6 decimals,
 *                                                   else \UnexpectedValueException will be thrown
 *                                                   if days too large or years too large \LengthException is thrown
 * @param  string    $truncate_after [='']           Truncate after which time name, will not round, hard end
 *                                                   values are parts names or interval short names (y, d, f, ...)
 *                                                   if illegal value \UnexpectedValueException is thrown
 * @param  bool      $natural_seperator [=false]     use ',' and 'and', if off use space
 * @param  bool      $name_space_seperator [=false]  add a space between the number and the time name
 * @param  bool      $show_microseconds [=true]      show microseconds
 * @param  bool      $short_time_name [=true]        use the short time names (eg s instead of seconds)
 * @param  bool      $skip_last_zero [=true]         skip all trailing zero values, eg 5m 0s => 5m
 * @param  bool      $skip_zero [=true]              do not show zero values anywhere, eg 1h 0m 20s => 1h 20s
 * @param  bool      $show_only_days [=false]        do not show years or months, show only days
 *                                                   if truncate after is set to year or month
 *                                                   throws \UnexpectedValueException
 * @param  bool      $auto_fix_microseconds [=false] if the micro seconds decimals are more than 6, round them
 *                                                   on defaul throw \UnexpectedValueException
 * @param  bool      $truncate_nanoseconds [=false]  if microseconds decimals >3 then normal we show 123.4ms
 *                                                   cut the .4 is set to true
 * @param  bool      $truncate_zero_seconds_if_microseconds [=true] if we have 0.123 seconds then if true no seconds
 *                                                   will be shown
 * @return string
 * @throws \UnexpectedValueException if seconds has more than 6 decimals
 *                                   if truncate has an illegal value
 *                                   if truncate is set to year or month and show_only_days is turned on
 * @throws \LengthException          if seconds is too large and show_days_only is selected and days is negetive
 *                                   or if years is negativ
 */
function intervalStringFormatDeprecated(
	int|float $seconds,
	string $truncate_after = '',
	bool $natural_seperator = false,
	bool $name_space_seperator = false,
	bool $show_microseconds = true,
	bool $short_time_name = true,
	bool $skip_last_zero = true,
	bool $skip_zero = true,
	bool $show_only_days = false,
	bool $auto_fix_microseconds = false,
	bool $truncate_nanoseconds = false,
	bool $truncate_zero_seconds_if_microseconds = true,
): string {
	// auto fix long seconds, else \UnexpectedValueException will be thrown on error
	// check if we have float and -> round to 6
	if ($auto_fix_microseconds === true && is_float($seconds)) {
		$seconds = round($seconds, 6);
	}
	// flag negative + set abs
	$negative = $seconds < 0 ? '-' : '';
	$seconds = abs($seconds);
	// create base time
	$date_now = new \DateTime("@0");
	try {
		$date_seconds = new \DateTime("@$seconds");
	} catch (\Exception $e) {
		throw new \UnexpectedValueException(
			'Seconds value is invalid, too large or more than six decimals: ' . $seconds,
			1,
			$e
		);
	}
	$interval = date_diff($date_now, $date_seconds);
	// if show_only_days and negative but input postive alert that this has to be done in y/m/d ...
	if ($interval->y < 0) {
		throw new \LengthException('Input seconds value is too large for years output: ' . $seconds, 2);
	} elseif ($interval->days < 0 && $show_only_days === true) {
		throw new \LengthException('Input seconds value is too large for days output: ' . $seconds, 3);
	}
	// array order is important, small too large
	$parts = [
		'microseconds' => 'f',
		'seconds' => 's', 'minutes' => 'i', 'hours' => 'h',
		'days' => 'd', 'months' => 'm', 'years' => 'y',
	];
	$short_name = [
		'years' => 'y', 'months' => 'm', 'days' => 'd',
		'hours' => 'h', 'minutes' => 'm', 'seconds' => 's',
		'microseconds' => 'ms'
	];
	$skip = false;
	if (!empty($truncate_after)) {
		// if truncate after not in key or value in parts
		if (!in_array($truncate_after, array_keys($parts)) && !in_array($truncate_after, array_values($parts))) {
			throw new \UnexpectedValueException(
				'truncate_after has an invalid value: ' . $truncate_after,
				4
			);
		}
		// if truncate after is y or m and we have show_only_days, throw exception
		if ($show_only_days === true && in_array($truncate_after, ['y', 'years', 'm', 'months'])) {
			throw new \UnexpectedValueException(
				'If show_only_days is turned on, the truncate_after cannot be years or months: '
					. $truncate_after,
				5
			);
		}
		$skip = true;
	}
	$formatted = [];
	$zero_list = [];
	$zero_last_list = [];
	$add_zero_seconds = false;
	foreach ($parts as $time_name => $part) {
		// end for micro seconds
		if ($show_microseconds === false && $time_name == 'microseconds') {
			continue;
		}
		// skip at this time position
		if ($part == $truncate_after || $truncate_after == $time_name) {
			$skip = false;
		}
		if ($skip === true) {
			continue;
		}
		if ($show_only_days === true && $part == 'd') {
			$value = $interval->days;
			$skip = true;
		} else {
			$value = $interval->$part;
		}
		if ($value == 0 && $skip_last_zero === true) {
			continue;
		}
		// print "-> V: $value | $part, $time_name | I: " . is_int($value) . " | F: " . is_float($value)
		// 	. " | " . ($value != 0 ? 'Not zero' : 'ZERO') . "<br>";
		// var_dump($skip_last_zero);
		if ($value != 0 || $skip_zero === false || $skip_last_zero === false) {
			if ($part == 'f') {
				if ($truncate_nanoseconds === true) {
					$value = round($value, 3);
				}
				$value *= 1000;
				// anything above that is nano seconds?
			}
			// on first hit turn off (full off)
			if ($value) {
				$skip_last_zero = null;
			} elseif ($skip_last_zero === false) {
				$zero_last_list[] = $part;
			}
			// build format
			$format = "$value";
			if ($name_space_seperator) {
				$format .= " ";
			}
			if ($short_time_name) {
				$format .= $short_name[$time_name];
			} elseif ($value == 1) {
				$format .= substr($time_name, 0, -1);
			} else {
				$format .= $time_name;
			}
			$formatted[] = $format;
		}
		// if we have 0 value, but only for skip zero condition
		if ($skip_zero === false) {
			if ($value == 0) {
				$zero_list[] = $part;
			} else {
				$zero_list = [];
			}
		}
		if (
			$part == 's' && $value == 0 &&
			$show_microseconds === true &&
			$truncate_zero_seconds_if_microseconds === false
		) {
			$add_zero_seconds = true;
		}
	}
	// if there is a zero list, strip that from the beginning, this is done always
	if (count($zero_list)) {
		// strip
		$formatted = array_slice($formatted, 0, count($zero_list) * -1);
	} elseif (count($zero_last_list) == count($formatted)) {
		// if we have all skip empty last, then we do not have any value
		$formatted = [];
	}
	$formatted = array_reverse($formatted);
	// print "=> F: " . print_r($formatted, true)
	// 	. " | Z: " . print_r($zero_list, true)
	// 	. " | ZL: " . print_r($zero_last_list, true)
	// 	. "<br>";
	if (count($formatted) == 0) {
		// if we have truncate on, then we assume nothing was found
		$str = "0";
		if ($name_space_seperator) {
			$str .= " ";
		}
		// if truncate is on, we assume we found nothing
		if (!empty($truncate_after)) {
			if (in_array($truncate_after, array_values($parts))) {
				$truncate_after = array_flip($parts)[$truncate_after];
			}
			$str .= ($short_time_name ? $short_name[$truncate_after] : $truncate_after);
		} else {
			$str .= ($short_time_name ? $short_name['seconds'] : 'seconds');
		}
		return $str;
	} elseif (count($formatted) == 1) {
		return $negative .
			($add_zero_seconds ?
				'0'
					. ($name_space_seperator ? ' ' : '')
					. ($short_time_name ? $short_name['seconds'] : 'seconds')
					. ' '
				: ''
			)
			. $formatted[0];
	} elseif ($natural_seperator === false) {
		return $negative . implode(' ', $formatted);
	} else {
		$str = implode(', ', array_slice($formatted, 0, -1));
		if (!empty($formatted[count($formatted) - 1])) {
			$str .= ' and ' . $formatted[count($formatted) - 1];
		}
		return $negative . $str;
	}
}

// __END__
