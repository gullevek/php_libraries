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
			$lines = 0;
			while (!feof($f)) {
				$lines += substr_count(fread($f, 8192), "\n");
			}
			fclose($f);
		} else {
			// if file does not exist or is not readable, return -1
			$lines = -1;
		}
		// return lines in file
		return $lines;
	}
}

// __END__
