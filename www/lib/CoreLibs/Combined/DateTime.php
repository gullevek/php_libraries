<?php

/*
 * image thumbnail, rotate, etc
 */

declare(strict_types=1);

namespace CoreLibs\Combined;

class DateTime
{
	/**
	 * a simple wrapper for the date format
	 * @param  int|float $timestamp  unix timestamp
	 * @param  bool      $show_micro show the micro time (default false)
	 * @return string                formated date+time in Y-M-D h:m:s ms
	 */
	public static function dateStringFormat($timestamp, bool $show_micro = false): string
	{
		// split up the timestamp, assume . in timestamp
		// array pad $ms if no microtime
		list ($timestamp, $ms) = array_pad(explode('.', (string)round($timestamp, 4)), 2, null);
		$string = date("Y-m-d H:i:s", (int)$timestamp);
		if ($show_micro && $ms) {
			$string .= ' ' . $ms . 'ms';
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
	 * does a reverse of the TimeStringFormat and converts the string from
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
				$timestamp *= -1;
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
		list ($year, $month, $day, $hour, $min, $sec) = array_pad(
			preg_split("/[\/\- :]/", $datetime) ?: [],
			6,
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
		if (
			($hour < 0 || $hour > 24) ||
			($min < 0 || $min > 60) ||
			(is_numeric($sec) && ($sec < 0 || $sec > 60))
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

		// splits the data up with / or -
		list ($start_year, $start_month, $start_day) = array_pad(
			preg_split('/[\/-]/', $start_date) ?: [],
			3,
			null
		);
		list ($end_year, $end_month, $end_day) = array_pad(
			preg_split('/[\/-]/', $end_date) ?: [],
			3,
			null
		);
		// check that month & day are two digits and then combine
		foreach (['start', 'end'] as $prefix) {
			foreach (['month', 'day'] as $date_part) {
				$_date = $prefix . '_' . $date_part;
				if (isset($$_date) && $$_date < 10 && !preg_match("/^0/", $$_date)) {
					$$_date = '0' . $$_date;
				}
			}
			$_date = $prefix . '_date';
			$$_date = '';
			foreach (['year', 'month', 'day'] as $date_part) {
				$_sub_date = $prefix . '_' . $date_part;
				$$_date .= $$_sub_date;
			}
		}
		// now do the compare
		if ($start_date < $end_date) {
			return -1;
		} elseif ($start_date == $end_date) {
			return 0;
		} elseif ($start_date > $end_date) {
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
		$start_timestamp = strtotime($start_datetime);
		$end_timestamp = strtotime($end_datetime);
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
		$start = new \DateTime($start_date);
		$end = new \DateTime($end_date);
		// so we include the last day too, we need to add +1 second in the time
		$end->setTime(0, 0, 1);

		$days[0] = $end->diff($start)->days;
		$days[1] = 0;
		$days[2] = 0;

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
				'weekend' => $days[2]
			];
		} else {
			return $days;
		}
	}
}

// __END__
