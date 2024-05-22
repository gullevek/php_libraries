<?php

/*
 * various file/file name functions
 */

declare(strict_types=1);

namespace CoreLibs\Check;

class File
{
	/**
	 * quick return the extension of the given file name
	 *
	 * @param  string $filename file name
	 * @return string           extension of the file name
	 */
	public static function getFilenameEnding(string $filename): string
	{
		$page_temp = pathinfo($filename);
		return $page_temp['extension'] ?? '';
	}

	/**
	 * get lines in a file
	 *
	 * @param  string $file file for line count read
	 * @return int          number of lines or -1 for non readable file
	 */
	public static function getLinesFromFile(string $file): int
	{
		if (
			is_file($file) &&
			file_exists($file) &&
			is_readable($file)
		) {
			$f = fopen($file, 'rb');
			if (!is_resource($f)) {
				return 0;
			}
			$lines = 0;
			while (!feof($f)) {
				$lines += substr_count(fread($f, 8192) ?: '', "\n");
			}
			fclose($f);
		} else {
			// if file does not exist or is not readable, return -1
			$lines = -1;
		}
		// return lines in file
		return $lines;
	}

	/**
	 * get the mime type of a file via finfo
	 * if file not found, throws exception
	 * else returns '' for any other finfo read problem
	 *
	 * @param  string $read_file File to read, relative or absolute path
	 * @return string
	 */
	public static function getMimeType(string $read_file): string
	{
		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		if (!is_file($read_file)) {
			throw new \UnexpectedValueException('[getMimeType] File not found: ' . $read_file);
		}
		return $finfo->file($read_file) ?: '';
	}
}

// __END__
