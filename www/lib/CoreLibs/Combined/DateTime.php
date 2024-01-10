<?php

/*
 * date convert and check functions
 */

declare(strict_types=1);

namespace CoreLibs\Combined;

use Exception;

class DateTime
{
	/** @var array<int,string> */
	public const DAY_SHORT = [
		1 => 'Mon',
		2 => 'Tue',
		3 => 'Wed',
		4 => 'Thu',
		5 => 'Fri',
		6 => 'Sat',
		7 => 'Sun',
	];
	/** @var array<int,string> */
	public const DAY_LONG = [
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
		7 => 'Sunday',
	];
	/** @var array<int,string> */
	public const MONTH_LONG = [
		1 => 'January',
		2 => 'February',
		3 => 'March',
		4 => 'April',
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	];
	/** @var array<int,string> */
	public const MONTH_SHORT = [
		1 => 'Jan',
		2 => 'Feb',
		3 => 'Mar',
		4 => 'Apr',
		5 => 'May',
		6 => 'Jun',
		7 => 'Jul',
		8 => 'Aug',
		9 => 'Sep',
		10 => 'Oct',
		11 => 'Nov',
		12 => 'Dec',
	];

	/**
	 * a simple wrapper for the date format
	 * if an invalid timestamp is give zero timestamp unix time is used
	 *
	 * @param  int|float $timestamp      unix timestamp
	 * @param  bool      $show_micro     show the micro time (default false)
	 * @param  bool      $micro_as_float Add the micro time with . instead
	 *                                   of ms (default false)
	 * @return string                    formated date+time in Y-M-D h:m:s ms
	 */
	public static function dateStringFormat(
		int|float $timestamp,
		bool $show_micro = false,
		bool $micro_as_float = false
	): string {
		// split up the timestamp, assume . in timestamp
		// array pad $ms if no microtime
		list ($timestamp, $ms) = array_pad(explode('.', (string)round($timestamp, 4)), 2, null);
		$string = date("Y-m-d H:i:s", (int)$timestamp);
		if ($show_micro && $ms) {
			if ($micro_as_float == false) {
				$string .= ' ' . $ms . 'ms';
			} else {
				$string .= '.' . $ms;
			}
		}
		return $string;
	}

	/**
	 * formats a timestamp into interval, not into a date
	 *
	 * @param  string|int|float $timestamp  interval in seconds and optional
	 *                                      float micro seconds
	 * @param  bool             $show_micro show micro seconds, default true
	 * @return string                       interval formatted string or string as is
	 */
	public static function timeStringFormat(
		string|int|float $timestamp,
		bool $show_micro = true
	): string {
		// check if the timestamp has any h/m/s/ms inside, if yes skip
		if (preg_match("/(h|m|s|ms)/", (string)$timestamp)) {
			return (string)$timestamp;
		}
		// split to 6 (nano seconds)
		list($timestamp, $ms) = array_pad(explode('.', (string)round((float)$timestamp, 6)), 2, null);
		// if micro seconds is on and we have none, set to 0
		if ($show_micro && $ms === null) {
			$ms = 0;
		}
		// if negative remember
		$negative = false;
		if ((int)$timestamp < 0) {
			$negative = true;
		}
		$timestamp = abs((float)$timestamp);
		$timegroups = [86400, 3600, 60, 1];
		$labels = ['d', 'h', 'm', 's'];
		$time_string = '';
		// if timestamp is zero, return zero string
		if ($timestamp == 0) {
			// if no seconds and we have no microseconds either, show no micro seconds
			if ($ms == 0) {
				$ms = null;
			}
			$time_string = '0s';
		} else {
			for ($i = 0, $iMax = count($timegroups); $i < $iMax; $i++) {
				$output = floor((float)$timestamp / $timegroups[$i]);
				$timestamp = (float)$timestamp % $timegroups[$i];
				// output has days|hours|min|sec
				if ($output || $time_string) {
					$time_string .= $output . $labels[$i] . (($i + 1) != count($timegroups) ? ' ' : '');
				}
			}
		}
		// only add ms if we have an ms value
		if ($ms !== null) {
			// prefix the milliseoncds with 0. and round it max 3 digits and then convert to int
			$ms = round((float)('0.' . $ms), 3) * 1000;
			// add ms if there
			if ($show_micro) {
				$time_string .= ' ' . $ms . 'ms';
			} elseif (!$time_string) {
				$time_string .= $ms . 'ms';
			}
		}
		if ($negative) {
			$time_string = '-' . $time_string;
		}
		return (string)$time_string;
	}

	/**
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
	public static function intervalStringFormat(
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
		$parts = [
			'years' => 'y',
			'months' => 'm',
			'days' => 'd',
			'hours' => 'h',
			'minutes' => 'i',
			'seconds' => 's',
			'microseconds' => 'f',
		];
		$short_name = [
			'years' => 'y', 'months' => 'm', 'days' => 'd',
			'hours' => 'h', 'minutes' => 'm', 'seconds' => 's',
			'microseconds' => 'ms'
		];
		// $skip = false;
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
			// $skip = true;
		}
		$formatted = [];
		$zero_formatted = [];
		$value_set = false;
		$add_zero_seconds = false;
		foreach ($parts as $time_name => $part) {
			if (
				// skip for micro seconds
				($show_microseconds === false && $part == 'f') ||
				// skip for if days only and we have year or month
				($show_only_days === true && in_array($part, ['y', 'm']))
			) {
				continue;
			}
			$add_value = 0;
			if ($show_only_days === true && $part == 'd') {
				$value = $interval->days;
			} else {
				$value = $interval->$part;
			}
			// print "-> V: $value | $part, $time_name"
			// 	. " | Set: " . ($value_set ? 'Y' : 'N') . ", SkipZ: " . ($skip_zero ? 'Y' : 'N')
			// 	. " | SkipLZ: " . ($skip_last_zero ? 'Y' : 'N')
			// 	. " | " . ($value != 0 ? 'Not zero' : 'ZERO') . "<br>";
			if ($value != 0) {
				if ($part == 'f') {
					if ($truncate_nanoseconds === true) {
						$value = round($value, 3);
					}
					$value *= 1000;
					// anything above that is nano seconds?
				}
				if ($value) {
					$value_set = true;
				}
				$add_value = 1;
			} elseif (
				$value == 0 &&
				$value_set === true && (
					$skip_last_zero === false ||
					$skip_zero === false
				)
			) {
				$add_value = 2;
			}
			// echo "ADD VALUE: $add_value<br>";
			if ($add_value) {
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
				if ($add_value == 1) {
					if (count($zero_formatted) && $skip_zero === false) {
						$formatted = array_merge($formatted, $zero_formatted);
					}
					$zero_formatted = [];
					$formatted[] = $format;
				} elseif ($add_value == 2) {
					$zero_formatted[] = $format;
				}
			}
			// if seconds is zero
			if (
				$part == 's' && $value == 0 &&
				$show_microseconds === true &&
				$truncate_zero_seconds_if_microseconds === false
			) {
				$add_zero_seconds = true;
			}
			// stop after a truncate is matching
			if ($part == $truncate_after || $truncate_after == $time_name) {
				break;
			}
		}
		// add all zero entries if we have skip last off
		if (count($zero_formatted) && $skip_last_zero === false) {
			$formatted = array_merge($formatted, $zero_formatted);
		}
		// print "=> F: " . print_r($formatted, true)
		// 	. " | Z: " . print_r($zero_list, true)
		// 	. " | ZL: " . print_r($zero_last_list, true)
		// 	. "<br>";
		if (count($formatted) == 0) {
			// if we have truncate on, then we assume nothing was found
			if (!empty($truncate_after)) {
				if (in_array($truncate_after, array_values($parts))) {
					$truncate_after = array_flip($parts)[$truncate_after];
				}
				$time_name = $truncate_after;
			} else {
				$time_name = 'seconds';
			}
			return '0' . ($name_space_seperator ? ' ' : '')
				. ($short_time_name ? $short_name[$time_name] : $time_name);
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
				$str .= ' and ' . (string)array_pop($formatted);
			}
			return $negative . $str;
		}
	}

	/**
	 * does a reverse of the timeStringFormat and converts the string from
	 * xd xh xm xs xms to a timestamp.microtime format
	 *
	 * @param  string|int|float $timestring formatted interval
	 * @return string|int|float             converted float interval, or string as is
	 */
	public static function stringToTime(string|int|float $timestring): string|int|float
	{
		$timestamp = 0;
		if (!preg_match("/(d|h|m|s|ms)/", (string)$timestring)) {
			return $timestring;
		}
		$timestring = (string)$timestring;
		// pos for preg match read + multiply factor
		$timegroups = [2 => 86400, 4 => 3600, 6 => 60, 8 => 1];
		$matches = [];
		// if start with -, strip and set negative
		$negative = false;
		if (preg_match("/^-/", $timestring)) {
			$negative = true;
			$timestring = substr($timestring, 1);
		}
		// preg match: 0: full string
		// 2, 4, 6, 8 are the to need values
		preg_match("/^((\d+)d ?)?((\d+)h ?)?((\d+)m ?)?((\d+)s ?)?((\d+)ms)?$/", $timestring, $matches);
		// multiply the returned matches and sum them up. the last one (ms) is added with .
		foreach ($timegroups as $i => $time_multiply) {
			if (isset($matches[$i]) && is_numeric($matches[$i])) {
				$timestamp += (float)$matches[$i] * $time_multiply;
			}
		}
		if (isset($matches[10]) && is_numeric($matches[10])) {
			$timestamp .= '.' . $matches[10];
		}
		if ($negative) {
			// cast to flaot so we can do a negative multiplication
			$timestamp = (float)$timestamp * -1;
		}
		return $timestamp;
	}

	/**
	 * Returns long or short day of week name based on ISO day of week number
	 * 1: Monday
	 * ...
	 * 7: Sunday
	 *
	 * @param  int    $isodow 1: Monday, 7: Sunday
	 * @param  bool   $long   Default false 'Mon', if true 'Monday'
	 * @return string         Day of week string either short 'Mon' or long 'Monday'
	 */
	public static function setWeekdayNameFromIsoDow(int $isodow, bool $long = false): string
	{
		// if not valid, set to invalid
		if ($isodow < 1 || $isodow > 7) {
			return $long ? 'Invalid' : 'Inv';
		}
		return date($long ? 'l' : 'D', strtotime("Sunday +{$isodow} days") ?: null);
	}

	/**
	 * Get the day of week Name from date
	 *
	 * @param  string $date Any valid date
	 * @param  bool   $long Default false 'Mon', if true 'Monday'
	 * @return string       Day of week string either short 'Mon' or long 'Monday'
	 */
	public static function setWeekdayNameFromDate(string $date, bool $long = false): string
	{
		if (!self::checkDate($date)) {
			return $long ? 'Invalid' : 'Inv';
		}
		return date($long ? 'l' : 'D', strtotime($date) ?: null);
	}

	/**
	 * Get the day of week Name from date
	 *
	 * @param  string $date  Any valid date
	 * @return int           ISO Weekday number 1: Monday, 7: Sunday, -1 for invalid date
	 */
	public static function setWeekdayNumberFromDate(string $date): int
	{
		if (!self::checkDate($date)) {
			return -1;
		}
		return (int)date('N', strtotime($date) ?: null);
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 *
	 * @param  string $date a date string in the format YYYY-MM-DD
	 * @return bool         true if valid date, false if date not valid
	 */
	public static function checkDate(string $date): bool
	{
		if (empty($date)) {
			return false;
		}
		list ($year, $month, $day) = array_pad(
			preg_split("/[\/-]/", $date) ?: [],
			3,
			null
		);
		if (!$year || !$month || !$day) {
			return false;
		}
		if (!checkdate((int)$month, (int)$day, (int)$year)) {
			return false;
		}
		return true;
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 *
	 * @param  string $datetime date (YYYY-MM-DD) + time (HH:MM:SS), SS can be dropped
	 * @return bool             true if valid date, false if date not valid
	 */
	public static function checkDateTime(string $datetime): bool
	{
		if (!$datetime) {
			return false;
		}
		// catch last overflow if sec has - in front
		list ($year, $month, $day, $hour, $min, $sec, $sec_overflow) = array_pad(
			preg_split("/[\/\- :]/", $datetime) ?: [],
			7,
			null
		);
		if (!$year || !$month || !$day) {
			return false;
		}
		if (!checkdate((int)$month, (int)$day, (int)$year)) {
			return false;
		}
		if (!is_numeric($hour) || !is_numeric($min)) {
			return false;
		}
		if (!empty($sec) && !is_numeric($sec)) {
			return false;
		}
		if (!empty($sec) && ($sec < 0 || $sec > 60)) {
			return false;
		};
		// in case we have - for seconds
		if (!empty($sec_overflow)) {
			return false;
		}
		if (
			($hour < 0 || $hour > 24) ||
			($min < 0 || $min > 60)
		) {
			return false;
		}
		return true;
	}

	/**
	 * compares two dates, tries to convert them via strtotime to timestamps
	 * returns int/bool in:
	 * -1 if the first date is smaller the last
	 * 0 if both are equal
	 * 1 if the first date is bigger than the last
	 * false if date validation/conversion failed
	 *
	 * @param  string $start_date start date string in YYYY-MM-DD
	 * @param  string $end_date   end date string in YYYY-MM-DD
	 * @return int                int -1 (s<e)/0 (s=e)/1 (s>e) as difference
	 * @throws \UnexpectedValueException On empty start/end values
	 */
	public static function compareDate(string $start_date, string $end_date): int
	{
		// pre check for empty or wrong
		if ($start_date == '--' || $end_date == '--' || empty($start_date) || empty($end_date)) {
			throw new \UnexpectedValueException('Start or End date not set or are just "--"', 1);
		}
		// if invalid, quit
		if (($start_timestamp = strtotime($start_date)) === false) {
			throw new \UnexpectedValueException("Error parsing start date through strtotime()", 2);
		}
		if (($end_timestamp = strtotime($end_date)) === false) {
			throw new \UnexpectedValueException("Error parsing end date through strtotime()", 3);
		}
		$comp = 0;
		// convert anything to Y-m-d and then to timestamp
		// this is to remove any time parts
		$start_timestamp = strtotime(date('Y-m-d', $start_timestamp));
		$end_timestamp = strtotime(date('Y-m-d', $end_timestamp));
		// compare, or end with false
		if ($start_timestamp < $end_timestamp) {
			$comp = -1;
		} elseif ($start_timestamp == $end_timestamp) {
			$comp = 0;
		} elseif ($start_timestamp > $end_timestamp) {
			$comp = 1;
		}
		return $comp;
	}

	/**
	 * compares the two dates + times. if seconds missing in one set,
	 * adds :00, converts date + times via strtotime to timestamps
	 * returns int/bool in:
	 * -1 if the first date is smaller the last
	 * 0 if both are equal
	 * 1 if the first date is bigger than the last
	 * false if date/times validation/conversion failed
	 *
	 * @param  string $start_datetime start date/time in YYYY-MM-DD HH:mm:ss
	 * @param  string $end_datetime   end date/time in YYYY-MM-DD HH:mm:ss
	 * @return int                    -1 (s<e)/0 (s=e)/1 (s>e) as difference
	 * @throws \UnexpectedValueException On empty start/end values
	 */
	public static function compareDateTime(string $start_datetime, string $end_datetime): int
	{
		// pre check for empty or wrong
		if ($start_datetime == '--' || $end_datetime == '--' || empty($start_datetime) || empty($end_datetime)) {
			throw new \UnexpectedValueException('Start or end timestamp not set or are just "--"', 1);
		}
		// quit if invalid timestamp
		if (($start_timestamp = strtotime($start_datetime)) === false) {
			throw new \UnexpectedValueException("Error parsing start timestamp through strtotime()", 2);
		}
		if (($end_timestamp = strtotime($end_datetime)) === false) {
			throw new \UnexpectedValueException("Error parsing end timestamp through strtotime()", 3);
		}
		$comp = 0;
		// compare, or return false
		if ($start_timestamp < $end_timestamp) {
			$comp = -1;
		} elseif ($start_timestamp == $end_timestamp) {
			$comp = 0;
		} elseif ($start_timestamp > $end_timestamp) {
			$comp = 1;
		}
		return $comp;
	}

	/**
	 * calculates the days between two dates
	 * return: overall days, week days, weekend days as array 0...2 or named
	 * as overall, weekday and weekend
	 *
	 * @param  string $start_date   valid start date (y/m/d)
	 * @param  string $end_date     valid end date (y/m/d)
	 * @param  bool   $return_named return array type, false (default), true for named
	 * @return array<mixed>         0/overall, 1/weekday, 2/weekend
	 */
	public static function calcDaysInterval(
		string $start_date,
		string $end_date,
		bool $return_named = false
	): array {
		// pos 0 all, pos 1 weekday, pos 2 weekend
		$days = [];
		// if anything invalid, return 0,0,0
		try {
			$start = new \DateTime($start_date);
			$end = new \DateTime($end_date);
		} catch (Exception $e) {
			if ($return_named === true) {
				return [
					'overall' => 0,
					'weekday' => 0,
					'weekend' => 0,
				];
			} else {
				return [0, 0, 0];
			}
		}
		// so we include the last day too, we need to add +1 second in the time
		$end->setTime(0, 0, 1);
		// if end date before start date, only this will be filled
		$days[0] = $end->diff($start)->days;
		$days[1] = 0;
		$days[2] = 0;
		// get period for weekends/weekdays
		$period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
		foreach ($period as $dt) {
			$curr = $dt->format('D');
			if ($curr == 'Sat' || $curr == 'Sun') {
				$days[2]++;
			} else {
				$days[1]++;
			}
		}
		if ($return_named === true) {
			return [
				'overall' => $days[0],
				'weekday' => $days[1],
				'weekend' => $days[2],
			];
		} else {
			return $days;
		}
	}

	/**
	 * check if a weekend day (sat/sun) is in the given date range
	 * Can have time too, but is not needed
	 *
	 * @param  string $start_date Y-m-d
	 * @param  string $end_date   Y-m-d
	 * @return bool               True for has weekend, False for has not
	 */
	public static function dateRangeHasWeekend(
		string $start_date,
		string $end_date,
	): bool {
		$dd_start = new \DateTime($start_date);
		$dd_end = new \DateTime($end_date);
		if (
			// starts with a weekend
			$dd_start->format('N') >= 6 ||
			// start day plus diff will be 6 and so fall into a weekend
			((int)$dd_start->format('w') + $dd_start->diff($dd_end)->days) >= 6
		) {
			return true;
		}
		return false;
	}
}

// __END__
