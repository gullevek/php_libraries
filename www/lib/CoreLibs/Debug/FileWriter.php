<?php

/*
 * direct write to log file
 * must have BASE folder and LOG foder defined
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

class FileWriter
{
	/** @var string */
	private static $debug_filename = 'debug_file.log'; // where to write output
	private static $debug_folder;

		/**
	 * Set a debug log folder, if not set BASE+LOG folders are set
	 * if they are defined
	 * This folder name must exist and must be writeable
	 *
	 * @param  string  $folder Folder name to where the log file will be written
	 * @return boolean         True for valid folder name, False for invalid
	 */
	public static function fsetFolder(string $folder): bool
	{
		if (!preg_match("/^[\w\-\/]+/", $folder)) {
			return false;
		}
		if (!is_writeable($folder)) {
			return false;
		}
		// if last is not / then add
		if (substr($folder, -1, 1) != DIRECTORY_SEPARATOR) {
			$folder .= DIRECTORY_SEPARATOR;
		}
		self::$debug_folder = $folder;
		return true;
	}

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
		if (!preg_match("/^[\w\-]+\.log$/", $filename)) {
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
		if (empty(self::$debug_filename)) {
			return false;
		}
		// if empty try to set base log folder
		if (
			empty(self::$debug_folder) &&
			defined('BASE') && !empty(BASE) &&
			defined('LOG') && !empty(LOG)
		) {
			self::$debug_folder = BASE . LOG;
		}
		if (!is_writeable(self::$debug_folder)) {
			return false;
		}
		$filename = self::$debug_folder . self::$debug_filename;
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
