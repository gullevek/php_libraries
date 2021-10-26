<?php

/*
 * various running time checkers
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

class RunningTime
{
	// hr
	/** @var float */
	private static $hr_starttime;
	/** @var float */
	private static $hr_runtime;
	/** @var float */
	private static $hr_endtime;
	// normal
	/** @var float */
	private static $starttime;
	/** @var float */
	private static $endtime;
	/** @var string */
	private static $runningtime_string;

	/**
	 * for messure run time between two calls for this method
	 * uses the hrtime() for running time
	 * first call sets start time and returns 0,
	 * second call sets end time and returns the run time
	 * the out_time parameter can be:
	 * n/ns (nano), y/ys (micro), m/ms (milli), s
	 * default is milliseconds
	 * @param  string $out_time set return time adjustment calculation
	 * @return float            running time without out_time suffix
	 */
	public static function hrRunningTime(string $out_time = 'ms'): float
	{
		// if start time not set, set start time
		if (!self::$hr_starttime) {
			self::$hr_starttime = hrtime(true);
			self::$hr_runtime = 0;
		} else {
			self::$hr_endtime = hrtime(true);
			self::$hr_runtime = self::$hr_endtime - self::$hr_starttime;
			// reset start and end time past run
			self::$hr_starttime = 0;
			self::$hr_endtime = 0;
		}
		// init divisor, just in case
		$divisor = 1;
		// check through valid out time, if nothing matches default to ms
		switch ($out_time) {
			case 'n':
			case 'ns':
				$divisor = 1;
				break;
			case 'y':
			case 'ys':
				$divisor = 1000;
				break;
			case 'm':
			case 'ms':
				$divisor = 1000000;
				break;
			case 's':
				$divisor = 1000000000;
				break;
			// default is ms
			default:
				$divisor = 1000000;
				break;
		}
		// return the run time in converted format
		self::$hr_runtime /= $divisor;
		return self::$hr_runtime;
	}

	/**
	 * prints start or end time in text format. On first call sets start time
	 * on second call it sends the end time and then also prints the running time
	 * Sets the internal runningtime_string variable with Start/End/Run time string
	 * NOTE: for pure running time check it is recommended to use hrRunningTime method
	 * @param  bool  $simple if true prints HTML strings, default text only
	 * @return float         running time as float number
	 */
	public static function runningTime(bool $simple = false): float
	{
		list($micro, $timestamp) = explode(' ', microtime());
		$running_time = 0;
		// set start & end time
		if (!self::$starttime) {
			// always reset running time string on first call
			self::$runningtime_string = '';
			self::$starttime = ((float)$micro + (float)$timestamp);
			self::$runningtime_string .= $simple ? 'Start: ' : "<b>Started at</b>: ";
		} else {
			self::$endtime = ((float)$micro + (float)$timestamp);
			self::$runningtime_string .= $simple ? 'End: ' : "<b>Stopped at</b>: ";
		}
		self::$runningtime_string .= date('Y-m-d H:i:s', (int)$timestamp);
		self::$runningtime_string .= ' ' . $micro . ($simple ? ', ' : '<br>');
		// if both are set
		if (self::$starttime && self::$endtime) {
			$running_time = self::$endtime - self::$starttime;
			self::$runningtime_string .= ($simple ? 'Run: ' : "<b>Script running time</b>: ") . $running_time . " s";
			// reset start & end time after run
			self::$starttime = 0;
			self::$endtime = 0;
		}
		return $running_time;
	}

	/**
	 * get the runningTime string (for debug visual)
	 *
	 * @return string
	 */
	public static function runningTimeString(): string
	{
		return self::$runningtime_string;
	}
}

// __END__
