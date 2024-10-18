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
	private static float $hr_start_time;
	/** @var float */
	private static float $hr_end_time;
	/** @var float */
	private static float $hr_last_time;
	// normal
	/** @var float */
	private static float $start_time;
	/** @var float */
	private static float $end_time;
	/** @var string */
	private static string $running_time_string;

	/**
	 * sub calculation for running time based on out time.
	 * If no running time set, return 0
	 *
	 * @param  string $out_time
	 * @return float
	 */
	private static function hrRunningTimeCalc(
		float $run_time,
		string $out_time = 'ms'
	): float {
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
		return $run_time /= $divisor;
	}

	/**
	 * for messure run time between two calls for this method
	 * uses the hrtime() for running time
	 * first call sets start time and returns 0,
	 * every other call sets end time and returns the run time since start
	 * the out_time parameter can be:
	 * n/ns (nano), y/ys (micro), m/ms (milli), s (seconds)
	 * default is milliseconds
	 *
	 * @param  string $out_time set return time adjustment calculation
	 * @return float            running time without out_time suffix
	 */
	public static function hrRunningTime(string $out_time = 'ms'): float
	{
		// if start time not set, set start time
		if (empty(self::$hr_start_time)) {
			self::$hr_start_time = hrtime(true);
			self::$hr_last_time = self::$hr_start_time;
			$run_time = 0;
		} else {
			self::$hr_end_time = hrtime(true);
			$run_time = self::$hr_end_time - self::$hr_last_time;
			self::$hr_last_time = self::$hr_end_time;
		}
		return self::hrRunningTimeCalc($run_time, $out_time);
	}

	/**
	 * print overall end time , can only be called after hrRunningtime
	 * see $out_time parameter description in hrRunningtime.
	 * Does not record a new timestamp, only prints different between start and
	 * last recoreded timestamp
	 *
	 * @param  string $out_time set return time adjustment calculation
	 * @return float            overall running time without out_time suffix
	 */
	public static function hrRunningTimeFromStart(string $out_time = 'ms'): float
	{
		if (!self::$hr_start_time) {
			return (float)0;
		}
		$time = self::hrRunningTimeCalc(
			self::$hr_end_time - self::$hr_start_time,
			$out_time
		);
		return $time;
	}

	/**
	 * reset hr running time internal variables (start, end, last)
	 *
	 * @return void
	 */
	public static function hrRunningTimeReset(): void
	{
		self::$hr_start_time = 0;
		self::$hr_end_time = 0;
		self::$hr_last_time = 0;
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
		if (empty(self::$start_time)) {
			// always reset running time string on first call
			self::$running_time_string = '';
			self::$start_time = ((float)$micro + (float)$timestamp);
			self::$running_time_string .= $simple ? 'Start: ' : "<b>Started at</b>: ";
		} else {
			self::$end_time = ((float)$micro + (float)$timestamp);
			self::$running_time_string .= $simple ? 'End: ' : "<b>Stopped at</b>: ";
		}
		self::$running_time_string .= date('Y-m-d H:i:s', (int)$timestamp);
		self::$running_time_string .= ' ' . $micro . ($simple ? ', ' : '<br>');
		// if both are set
		if (!empty(self::$start_time) && !empty(self::$end_time)) {
			$running_time = self::$end_time - self::$start_time;
			self::$running_time_string .= ($simple ? 'Run: ' : "<b>Script running time</b>: ") . $running_time . " s";
			// reset start & end time after run
			self::$start_time = 0;
			self::$end_time = 0;
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
		return self::$running_time_string;
	}
}

// __END__
