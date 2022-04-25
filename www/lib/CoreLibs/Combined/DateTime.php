<?php

/*
 * image thumbnail, rotate, etc
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
	 * @param  int|float $timestamp      unix timestamp
	 * @param  bool      $show_micro     show the micro time (default false)
	 * @param  bool      $micro_as_float Add the micro time with . instead of ms (default false)
	 * @return string                    formated date+time in Y-M-D h:m:s ms
	 */
	public static function dateStringFormat(
		$timestamp,
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
	 * @param  string|int|float $timestamp  interval in seconds and optional float micro seconds
	 * @param  bool             $show_micro show micro seconds, default true
	 * @return string                       interval formatted string or string as is
	 */
	public static function timeStringFormat($timestamp, bool $show_micro = true): string
	{
		// check if the timestamp has any h/m/s/ms inside, if yes skip
		if (!preg_match("/(h|m|s|ms)/", (string)$timestamp)) {
			list($timestamp, $ms) = array_pad(explode('.', (string)round((float)$timestamp, 4)), 2, null);
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
				// if we have ms and it has leading zeros, remove them, but only if it is nut just 0
				$ms = preg_replace("/^0+(\d+)$/", '${1}', $ms);
				if (!is_string($ms) || empty($ms)) {
					$ms = '0';
				}
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
		} else {
			$time_string = $timestamp;
		}
		return (string)$time_string;
	}

	/**
	 * does a reverse of the timeStringFormat and converts the string from
	 * xd xh xm xs xms to a timestamp.microtime format
	 * @param  string|int|float $timestring formatted interval
	 * @return string|int|float             converted float interval, or string as is
	 */
	public static function stringToTime($timestring)
	{
		$timestamp = 0;
		if (preg_match("/(d|h|m|s|ms)/", (string)$timestring)) {
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
		} else {
			return $timestring;
		}
	}

	/**
	 * splits & checks date, wrap around for check_date function
	 * @param  string $date a date string in the format YYYY-MM-DD
	 * @return bool         true if valid date, false if date not valid
	 */
	public static function checkDate($date): bool
	{
		if (!$date) {
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
	 * plits & checks date, wrap around for check_date function
	 * returns int in:
	 *     -1 if the first date is smaller the last
	 *     0 if both are equal
	 *     1 if the first date is bigger than the last
	 *     false (bool): error
	 * @param  string $start_date start date string in YYYY-MM-DD
	 * @param  string $end_date   end date string in YYYY-MM-DD
	 * @return int|bool           false on error, or int -1/0/1 as difference
	 */
	public static function compareDate($start_date, $end_date)
	{
		// pre check for empty or wrong
		if ($start_date == '--' || $end_date == '--' || !$start_date || !$end_date) {
			return false;
		}
		// if invalid, quit
		if (($start_timestamp = strtotime($start_date)) === false) {
			return false;
		}
		if (($end_timestamp = strtotime($end_date)) === false) {
			return false;
		}
		// convert anything to Y-m-d and then to timestamp
		// this is to remove any time parts
		$start_timestamp = strtotime(date('Y-m-d', $start_timestamp));
		$end_timestamp = strtotime(date('Y-m-d', $end_timestamp));
		// compare, or end with false
		if ($start_timestamp < $end_timestamp) {
			return -1;
		} elseif ($start_timestamp == $end_timestamp) {
			return 0;
		} elseif ($start_timestamp > $end_timestamp) {
			return 1;
		} else {
			return false;
		}
	}

	/**
	 * compares the two dates + times. if seconds missing in one set, add :00, converts / to -
	 * returns int/bool in:
	 *     -1 if the first date is smaller the last
	 *     0 if both are equal
	 *     1 if the first date is bigger than the last
	 *     false if no valid date/times chould be found
	 * @param  string $start_datetime start date/time in YYYY-MM-DD HH:mm:ss
	 * @param  string $end_datetime   end date/time in YYYY-MM-DD HH:mm:ss
	 * @return int|bool               false for error or -1/0/1 as difference
	 */
	public static function compareDateTime($start_datetime, $end_datetime)
	{
		// pre check for empty or wrong
		if ($start_datetime == '--' || $end_datetime == '--' || !$start_datetime || !$end_datetime) {
			return false;
		}
		// quit if invalid timestamp
		if (($start_timestamp = strtotime($start_datetime)) === false) {
			return false;
		}
		if (($end_timestamp = strtotime($end_datetime)) === false) {
			return false;
		}
		// compare, or return false
		if ($start_timestamp < $end_timestamp) {
			return -1;
		} elseif ($start_timestamp == $end_timestamp) {
			return 0;
		} elseif ($start_timestamp > $end_timestamp) {
			return 1;
		} else {
			return false;
		}
	}

	/**
	 * calculates the days between two dates
	 * return: overall days, week days, weekend days as array 0...2 or named
	 * as overall, weekday and weekend
	 * @param  string $start_date   valid start date (y/m/d)
	 * @param  string $end_date     valid end date (y/m/d)
	 * @param  bool   $return_named return array type, false (default), true for named
	 * @return array<mixed>         0/overall, 1/weekday, 2/weekend
	 */
	public static function calcDaysInterval($start_date, $end_date, bool $return_named = false): array
	{
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
				$days[2] ++;
			} else {
				$days[1] ++;
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
}

// __END__
