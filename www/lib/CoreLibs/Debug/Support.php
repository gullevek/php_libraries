<?php

/*
 * Debug support functions
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

class Support
{
	/**
	 * wrapper around microtime function to print out y-m-d h:i:s.ms
	 * @param  int $set_microtime -1 to set micro time, 0 for none, positive for rounding
	 * @return string             formated datetime string with microtime
	 */
	public static function printTime(int $set_microtime = -1): string
	{
		list($microtime, $timestamp) = explode(' ', microtime());
		$string = date("Y-m-d H:i:s", (int)$timestamp);
		// if microtime flag is -1 no round, if 0, no microtime, if >= 1, round that size
		if ($set_microtime == -1) {
			$string .= substr($microtime, 1);
		} elseif ($set_microtime >= 1) {
			// in round case we run this through number format to always get the same amount of digits
			$string .= substr(number_format(round((float)$microtime, $set_microtime), $set_microtime), 1);
		}
		return $string;
	}

	/**
	 * prints a html formatted (pre) array
	 * @param  array  $array any array
	 * @return string        formatted array for output with <pre> tag added
	 */
	public static function printAr(array $array): string
	{
		return "<pre>" . print_r($array, true) . "</pre>";
	}

	/**
	 * if there is a need to find out which parent method called a child method,
	 * eg for debugging, this function does this
	 * call this method in the child method and you get the parent function that called
	 * @param  int    $level debug level, default 2
	 * @return ?string       null or the function that called the function where this method is called
	 */
	public static function getCallerMethod(int $level = 1): ?string
	{
		$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		// print \CoreLibs\Debug\Support::printAr($traces);
		// We should check from top down if unset?
		// sets the start point here, and in level two (the sub call) we find this
		if (isset($traces[$level])) {
			return $traces[$level]['function'];
		}
		return null;
	}

	/**
	 * Get the current class where this function is called
	 * Is mostly used in debug log statements to get the class where the debug was called
	 * gets top level class
	 *　loops over the debug backtrace until if finds the first class (from the end)
	 * @return string Class name with namespace
	 */
	public static function getCallerClass(): string
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) ?? [['class' => get_called_class()]];
		$class = null;
		while ($class === null) {
			// if current is
			// [function] => debug
			// [class] => CoreLibs\Debug\Logging
			// then return
			// (OUTSIDE) because it was not called from a class method
			// or return file name
			$class = array_pop($backtrace)['class'] ?? null;
		}
		return $class ?? '';
	}

	/**
	 * If a string is empty, sets '-' for return, or if given any other string
	 *
	 * @param  string|null $string  The string to check
	 * @param  string      $replace [default '-'] What to replace the empty string with
	 * @return string               String itself or the replaced value
	 */
	public static function debugString(?string $string, string $replace = '-'): string
	{
		if (empty($string)) {
			return $replace;
		}
		return $string;
	}
}

// __END__
