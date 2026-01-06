<?php

/*
 * byte conversion from and to human readable
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Byte
{
	// define byteFormat
	public const BYTE_FORMAT_NOSPACE = 1;
	public const BYTE_FORMAT_ADJUST = 2;
	public const BYTE_FORMAT_SI = 4;
	public const RETURN_AS_STRING = 8;

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
	 * BYTE_FORMAT_NOSPACE [1] turn off spaces between number and suffix
	 * BYTE_FORMAT_ADJUST  [2] use sprintf to always print two decimals
	 * BYTE_FORMAT_SI      [4] use si standard 1000 instead of bytes 1024
	 * To use the constant from outside use class::CONSTANT
	 *
	 * @param  string|int|float $bytes bytes as string int or pure int
	 * @param  int              $flags bitwise flag with use space turned on
	 *                                 BYTE_FORMAT_NOSPACE: no space between number and suffix
	 *                                 BYTE_FORMAT_ADJUST: sprintf adjusted two 2 decimals
	 *                                 BYTE_FORMAT_SI: use 1000 instead of 1024
	 * @return string                  converted byte number (float) with suffix
	 * @throws \InvalidArgumentException 1: no valid flag set
	 */
	public static function humanReadableByteFormat(string|int|float $bytes, int $flags = 0): string
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
			if ($flags > 7) {
				throw new \InvalidArgumentException("Invalid flags parameter: $flags", 1);
			}

			// si or normal
			$unit = $si ? 1000 : 1024;
			// always positive
			$abs_bytes = $bytes == PHP_INT_MIN ? PHP_INT_MAX : abs((float)$bytes);
			// smaller than unit is always B
			if ($abs_bytes < $unit) {
				return $bytes . 'B';
			}
			// labels in order of size [Y, Z]
			$labels = ['', 'K', 'M', 'G', 'T', 'P', 'E'];
			// exp position calculation
			$exp = (int)floor(log($abs_bytes, $unit));
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
	 * BYTE_FORMAT_SI      [4] use si standard 1000 instead of bytes 1024
	 *
	 * @param  string|int|float $number any string or number to convert
	 * @param  int              $flags  bitwise flag with use space turned on
	 *                                  BYTE_FORMAT_SI: use 1000 instead of 1024
	 * @return string|int|float         converted value or original value
	 * @throws \InvalidArgumentException no valid flag set
	 * @throws \LengthException          number too large to convert to int
	 * @throws \RuntimeException		 BCMath extension not loaded if flag is set to string
	 */
	public static function stringByteFormat(string|int|float $number, int $flags = 0): string|int|float
	{
		// use SI 1000 mod and not 1024 mod
		if ($flags & self::BYTE_FORMAT_SI) {
			$si = true;
		} else {
			$si = false;
		}
		if ($flags & self::RETURN_AS_STRING) {
			$return_as_string = true;
		} else {
			$return_as_string = false;
		}
		if ($flags != 0 && $flags != 4 && $flags != 8 && $flags != 12) {
			throw new \InvalidArgumentException("Invalid flags parameter: $flags", 1);
		}
		// matches in regex
		$matches = [];
		// all valid units
		$valid_units_ = 'bkmgtpezy';
		// detects up to exo bytes
		preg_match(
			"/(-)?([\d.,]*)\s?(eib|pib|tib|gib|mib|kib|eb|pb|tb|gb|mb|kb|e|p|t|g|m|k|b)$/i",
			strtolower((string)$number),
			$matches
		);
		$number_negative = false;
		if (!empty($matches[1])) {
			$number_negative = true;
		}
		if (isset($matches[2]) && isset($matches[3])) {
			// remove all non valid characters from the number
			$number = preg_replace('/[^0-9\.]/', '', $matches[2]);
			// final clean up and convert to float
			$number = (float)trim((string)$number);
			// convert any mb/gb/etc to single m/b
			$unit = preg_replace('/[^bkmgtpezy]/i', '', $matches[3]);
			if ($unit) {
				$number = $number * pow($si ? 1000 : 1024, stripos($valid_units_, $unit[0]) ?: 0);
			}
			// if the number is too large, we cannot convert to int directly
			if ($number <= PHP_INT_MIN || $number >= PHP_INT_MAX) {
				// if we do not want to convert to string
				if (!$return_as_string) {
					throw new \LengthException(
						'Number too large be converted to int: ' . (string)$number
					);
				}
				// for string, check if bcmath is loaded, if not this will not work
				if (!extension_loaded('bcmath')) {
					throw new \RuntimeException(
						'Number too large be converted to int and BCMath extension not loaded: ' . (string)$number
					);
				}
			}
			// string return
			if ($return_as_string) {
				// return as string to avoid overflow
				// $number = (string)round($number);
				$number = bcmul(number_format(
					$number,
					12,
					'.',
					''
				), "1");
				if ($number_negative) {
					$number = '-' . $number;
				}
				return $number;
			}
			// convert to INT to avoid +E output
			$number = (int)round($number);
			// if negative input, keep nnegative
			if ($number_negative) {
				$number *= -1;
			}
			// check if number is negative but should be, this is Lenght overflow
			if (!$number_negative && $number < 0) {
				throw new \LengthException(
					'Number too large be converted to int: ' . (string)$number
				);
			}
		}
		// if not matching return as is
		return $number;
	}
}

// __END__
