<?php

/*
 * image thumbnail, rotate, etc
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Byte
{
	// define byteFormat
	public const BYTE_FORMAT_NOSPACE = 1;
	public const BYTE_FORMAT_ADJUST = 2;
	public const BYTE_FORMAT_SI = 4;

	/**
	 * This function replaces the old byteStringFormat
	 *
	 * Converts any number string to human readable byte format
	 * Maxium is Exobytes and above that the Exobytes suffix is used for all
	 * If more are needed only the correct short name for the suffix has to be
	 * added to the labels array
	 * On no number string it returns string as is
	 * Source Idea: SOURCE: https://programming.guide/worlds-most-copied-so-snippet.html
	 *
	 * The class itself hast the following defined
	 * BYTE_FORMAT_NOSPACE [1] turn off spaces between number and extension
	 * BYTE_FORMAT_ADJUST  [2] use sprintf to always print two decimals
	 * BYTE_FORMAT_SI      [3] use si standard 1000 instead of bytes 1024
	 * To use the constant from outside use class::CONSTANT
	 * @param  string|int|float $bytes bytes as string int or pure int
	 * @param  int              $flags bitwise flag with use space turned on
	 * @return string                  converted byte number (float) with suffix
	 */
	public static function humanReadableByteFormat($bytes, int $flags = 0): string
	{
		// if not numeric, return as is
		if (is_numeric($bytes)) {
			// flags bit wise check
			// remove space between number and suffix
			if ($flags & self::BYTE_FORMAT_NOSPACE) {
				$space = false;
			} else {
				$space = true;
			}
			// use sprintf instead of round
			if ($flags & self::BYTE_FORMAT_ADJUST) {
				$adjust = true;
			} else {
				$adjust = false;
			}
			// use SI 1000 mod and not 1024 mod
			if ($flags & self::BYTE_FORMAT_SI) {
				$si = true;
			} else {
				$si = false;
			}

			// si or normal
			$unit = $si ? 1000 : 1024;
			// always positive
			$abs_bytes = $bytes == PHP_INT_MIN ? PHP_INT_MAX : abs($bytes);
			// smaller than unit is always B
			if ($abs_bytes < $unit) {
				return $bytes . 'B';
			}
			// labels in order of size [Y, Z]
			$labels = ['', 'K', 'M', 'G', 'T', 'P', 'E'];
			// exp position calculation
			$exp = floor(log($abs_bytes, $unit));
			// avoid printing out anything larger than max labels
			if ($exp >= count($labels)) {
				$exp = count($labels) - 1;
			}
			// deviation calculation
			$dev = pow($unit, $exp) * ($unit - 0.05);
			// shift the exp +1 for on the border units
			if (
				$exp < 6 &&
				$abs_bytes > ($dev - (((int)$dev & 0xfff) == 0xd00 ? 52 : 0))
			) {
				$exp++;
			}
			// label name, including leading space if flagged
			$pre = ($space ? ' ' : '') . ($labels[$exp] ?? '>E') . ($si ? 'i' : '') . 'B';
			$bytes_calc = $abs_bytes / pow($unit, $exp);
			// if original is negative, reverse
			if ($bytes < 0) {
				$bytes_calc *= -1;
			}
			if ($adjust) {
				return sprintf("%.2f%s", $bytes_calc, $pre);
			} else {
				return round($bytes_calc, 2) . $pre;
			}
		} else {
			// if anything other return as string
			return (string)$bytes;
		}
	}

	/**
	 * calculates the bytes based on a string with nnG, nnGB, nnM, etc
	 * NOTE: large exabyte numbers will overflow
	 * flag allowed:
	 * BYTE_FORMAT_SI      [3] use si standard 1000 instead of bytes 1024
	 * @param  string|int|float $number any string or number to convert
	 * @param  int              $flags  bitwise flag with use space turned on
	 * @return string|int|float         converted value or original value
	 */
	public static function stringByteFormat($number, int $flags = 0)
	{
		// use SI 1000 mod and not 1024 mod
		if ($flags & self::BYTE_FORMAT_SI) {
			$si = true;
		} else {
			$si = false;
		}
		// matches in regex
		$matches = [];
		// all valid units
		$valid_units_ = 'bkmgtpezy';
		// detects up to exo bytes
		preg_match(
			"/([\d.,]*)\s?(eib|pib|tib|gib|mib|kib|eb|pb|tb|gb|mb|kb|e|p|t|g|m|k|b)$/i",
			strtolower($number),
			$matches
		);
		if (isset($matches[1]) && isset($matches[2])) {
			// remove all non valid characters from the number
			$number = preg_replace('/[^0-9\.]/', '', $matches[1]);
			// final clean up and convert to float
			$number = (float)trim($number);
			// convert any mb/gb/etc to single m/b
			$unit = preg_replace('/[^bkmgtpezy]/i', '', $matches[2]);
			if ($unit) {
				$number = $number * pow($si ? 1000 : 1024, stripos($valid_units_, $unit[0]));
			}
			// convert to INT to avoid +E output
			$number = (int)round($number);
		}
		// if not matching return as is
		return $number;
	}
}

// __END__
