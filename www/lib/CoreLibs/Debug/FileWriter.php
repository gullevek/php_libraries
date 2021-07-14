<?php

/*
 * direct write to log file
 * must have BASE folder and LOG foder defined
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

class FileWriter
{
	private static $debug_filename = 'debug_file.log'; // where to write output

	/**
	 * set new debug file name
	 * Must be alphanumeric and underscore only.
	 * Must end with .log
	 *
	 * @param string $filename File name to set
	 * @return bool            True for valid file name, False if invalid
	 */
	public static function fsetFilename(string $filename): bool
	{
		// valid file. must be only ascii & _, must end with .log
		if (!preg_match("/^[A-Za-z_-]+\.log$/", $filename)) {
			return false;
		}
		self::$debug_filename = $filename;
		return true;
	}

	/**
	 * writes a string to a file immediatly, for fast debug output
	 * @param  string  $string string to write to the file
	 * @param  boolean $enter  default true, if set adds a linebreak \n at the end
	 * @return bool            True for log written, false for not wirrten
	 */
	public static function fdebug(string $string, bool $enter = true): bool
	{
		if (!self::$debug_filename) {
			return false;
		}
		if (!is_writeable(BASE . LOG)) {
			return false;
		}
		$filename = BASE . LOG . self::$debug_filename;
		$fh = fopen($filename, 'a');
		if ($fh === false) {
			return false;
		}
		if ($enter === true) {
			$string .= "\n";
		}
		$string = "[" . \CoreLibs\Debug\Support::printTime() . "] "
			. "[" . \CoreLibs\Get\System::getPageName(2) . "] - " . $string;
		fwrite($fh, $string);
		fclose($fh);
		return true;
	}
}

// __END__
