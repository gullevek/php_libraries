<?php

/*
 * Debug support functions
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

use CoreLibs\Convert\Html;

class Support
{
	/**
	 * wrapper around microtime function to print out y-m-d h:i:s.ms
	 *
	 * @param  int $set_microtime -1 to set micro time, 0 for none,
	 *                            positive for rounding
	 * @return string             formated datetime string with microtime
	 */
	public static function printTime(int $set_microtime = -1): string
	{
		[$microtime, $timestamp] = explode(' ', microtime());
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
	 *
	 * @param  array<mixed> $array   any array
	 * @param  bool         $no_html default add <pre>
	 * @return string                formatted array for output with <pre> tag added
	 */
	public static function printAr(array $array, bool $no_html = false): string
	{
		return $no_html ?
			print_r($array, true) :
			'<pre>' . print_r($array, true) . '</pre>';
	}

	/**
	 * alternate name for printAr function
	 *
	 * @param  array<mixed> $array   any array
	 * @param  bool         $no_html default add <pre>
	 * @return string                formatted array for output with <pre> tag added
	 */
	public static function printArray(array $array, bool $no_html = false): string
	{
		return self::printAr($array, $no_html);
	}

	/**
	 * A replacement for the \CoreLibs\Debug\Support::printAr
	 * But this does not wrap it in <pre></pre>
	 * Do not use this without using it in a string in debug function
	 * Note: for full data debug dumps use Support::dumpVar()
	 *
	 * @param  array<mixed> $a Array to format
	 * @return string          print_r formated
	 */
	public static function prAr(array $a): string
	{
		return self::printAr($a, true);
	}

	/**
	 * convert bool value to string
	 * if $name is set prefix with nae
	 * default true: true, false: false
	 *
	 * @param  bool   $bool    Variable to convert
	 * @param  string $name    [default: ''] Prefix name
	 * @param  string $true    [default: 'true'] True string
	 * @param  string $false   [default: 'false'] False string
	 * @param  bool   $no_html [default: false] if true do not print html
	 * @return string          String with converted bool text for debug
	 */
	public static function printBool(
		bool $bool,
		string $name = '',
		string $true = 'true',
		string $false = 'false',
		bool $no_html = false,
	): string {
		return
			(!empty($name) ?
				($no_html ?
					$name : '<b>' . $name . '</b>') . ': '
				: '')
			. ($bool ? $true : $false);
	}

	/**
	 * Convert bool value to string value. Short name alias for printBool
	 *
	 * @param  bool   $bool  Bool value to be transformed
	 * @param  string $true  [default: 'true'] Override default string 'true'
	 * @param  string $false [default: 'false'] Override default string 'false'
	 * @return string        $true or $false string for true/false bool
	 */
	public static function prBl(
		bool $bool,
		string $true = 'true',
		string $false = 'false'
	): string {
		return self::printBool($bool, '', $true, $false, true);
	}

	/**
	 * print out any data as string
	 * will convert boolean to TRUE/FALSE
	 * if object return get_class
	 * for array use printAr function, can be controlled with no_html for
	 * Debug\Logging compatible output
	 * Recommended to use Support::dumpVar()
	 *
	 * @param  mixed  $mixed
	 * @param  bool   $no_html set to true to strip <pre> tags
	 * @return string
	 */
	public static function printToString(mixed $mixed, bool $no_html = false): string
	{
		if (is_null($mixed)) {
			return (string)'NULL';
		} elseif (is_bool($mixed)) {
			return self::printBool($mixed, '', 'TRUE', 'FALSE');
		} elseif (is_resource($mixed)) {
			return (string)$mixed;
		} elseif (is_object($mixed)) {
			return get_class($mixed);
		} elseif (is_array($mixed)) {
			// use the pre one OR debug one
			return self::printAr($mixed, $no_html);
		} elseif (is_string($mixed)) {
			if ($no_html) {
				return Html::htmlent((string)$mixed);
			} else {
				return (string)$mixed;
			}
		} else {
			// should be int/float/string
			return (string)$mixed;
		}
	}

	/**
	 * Dumps var data and returns it as string
	 * var_dump based
	 * Recommended debug output
	 *
	 * @param  mixed  $data         Anything
	 * @param  bool   $no_html      [default=false] If true strip all html tags
	 *                              (for text print)
	 * @return string               A text string
	 */
	public static function dumpVar(
		mixed $data,
		bool $no_html = false,
	): string {
		// dump data
		ob_start();
		var_dump($data);
		$debug_dump = ob_get_clean() ?: '[FAILED TO GET var_dump() data]';
		// check if the original caller is dV, if yes, up the caller level for
		// the file line get by 1, so we get file + pos from the dV call and
		// not this call
		$caller_level = 1;
		$caller_list = self::getCallerMethodList();
		if ($caller_list[0] == 'dV') {
			$caller_level = 2;
		}
		// we need to strip the string in <small></small that is
		// "path ... CoreLibs/Debug/Support.php:<number>:
		// and replace it with the caller methods and location
		$caller_file_number = self::getCallerFileLine($caller_level);
		$debug_dump = preg_replace(
			'|<small>(/.*:\d+:)</small>|',
			'<small>' . $caller_file_number . ':</small>',
			$debug_dump
		) ?? $debug_dump; // in case of failure keep original
		// if strip is ture, remove all HTML tags and convert any html entities back
		return $no_html ?
			str_replace(
				// things to replace in the string if set
				['&gt;', '&lt;', '&#13;', '&#10;'],
				['>', '<', "\r", "\n"],
				strip_tags($debug_dump)
			) :
			$debug_dump;
	}

	/**
	 * exports (dumps) var, in more printable design, but without detail info
	 *
	 * @param  mixed  $data    Anything
	 * @param  bool   $no_html If true true do not add <pre> tags
	 * @return string          A text string
	 */
	public static function exportVar(mixed $data, bool $no_html = false): string
	{
		return $no_html ?
			var_export($data, true) :
			'<pre>' . var_export($data, true) . '</pre>';
	}

	/**
	 * Return file name and line number where this was called
	 * One level up
	 *
	 * @param  int         $level trace level, default 1
	 * @return string|null        null or file name:line number
	 */
	public static function getCallerFileLine(int $level = 1): ?string
	{
		$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		// print \CoreLibs\Debug\Support::printAr($traces);
		// We should check from top down if unset?
		// sets the start point here, and in level two (the sub call) we find this
		if (isset($traces[$level])) {
			return ($traces[$level]['file'] ?? $traces[$level]['function'])
				. ':' . ($traces[$level]['line'] ?? '-');
		}
		return null;
	}

	/**
	 * if there is a need to find out which parent method called a child method,
	 * eg for debugging, this function does this
	 *
	 * call this method in the child method and you get the parent function that called
	 * @param  int    $level trace level, default 1
	 * @return ?string       null or the function that called the function
	 *                       where this method is called
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
	 * Returns array with all methods in the call stack in the order so that last
	 * called is last in order
	 * Will start with start_level to skip unwanted from stack
	 * Defaults to skip level 0 wich is this methid
	 *
	 * @param  integer $start_level From what level on, as defaul starts with 1
	 *                              to exclude self
	 * @return array<mixed>         All method names in list where max is last called
	 */
	public static function getCallerMethodList(int $start_level = 1): array
	{
		$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$methods = [];
		foreach ($traces as $level => $data) {
			if ($level >= $start_level) {
				if (!empty($data['function'])) {
					array_unshift($methods, $data['function']);
				}
			}
		}
		return $methods;
	}

	/**
	 * Get the current class where this function is called
	 * Is mostly used in debug log statements to get the class where the debug
	 * was called
	 * gets top level class
	 *　loops over the debug backtrace until if finds the first class (from the end)
	 *
	 * @return string Class name with namespace
	 */
	public static function getCallerClass(): string
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		// ?? [['class' => get_called_class()]];
		// TODO make sure that this doesn't loop forver
		$class = null;
		while ($class === null && count($backtrace) > 0) {
			// if current is
			// [function] => debug
			// [class] => CoreLibs\Debug\Logging
			// then return
			// (OUTSIDE) because it was not called from a class method
			// or return file name
			$get_class = array_pop($backtrace);
			$class = $get_class['class'] ?? null;
		}
		// on null or empty return empty string
		return empty($class) ? '' : $class;
	}

	/**
	 * If a string is empty, sets '-' for return, or if given any other string
	 *
	 * @param  string|null $string  The string to check
	 * @param  string      $replace [default '-'] What to replace the empty string with
	 * @return string               String itself or the replaced value
	 */
	public static function debugString(
		?string $string,
		string $replace = '-',
		bool $no_html = false
	): string {
		if (empty($string)) {
			$string = $replace;
		}
		if ($no_html) {
			return Html::htmlent($string);
		} else {
			return $string;
		}
	}
}

// __END__
