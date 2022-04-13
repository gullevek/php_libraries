<?php

/*
 * check if string is valid in target encoding
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Encoding
{
	/**
	 * detects the source encoding of the string and if doesn't match
	 * to the given target encoding it convert is
	 * if source encoding is set and auto check is true (default) a second
	 * check is done so that the source string encoding actually matches
	 * will be skipped if source encoding detection is ascii
	 *
	 * @param  string $string          string to convert
	 * @param  string $to_encoding     target encoding
	 * @param  string $source_encoding optional source encoding, will try to auto detect
	 * @param  bool   $auto_check      default true, if source encoding is set
	 *                                 check that the source is actually matching
	 *                                 to what we sav the source is
	 * @return string                  encoding converted string
	 */
	public static function convertEncoding(
		string $string,
		string $to_encoding,
		string $source_encoding = '',
		bool $auto_check = true
	): string {
		// set if not given
		if (!$source_encoding) {
			$source_encoding = mb_detect_encoding($string);
		} else {
			$_source_encoding = mb_detect_encoding($string);
		}
		if (
			$auto_check === true &&
			isset($_source_encoding) &&
			$_source_encoding == $source_encoding
		) {
			// trigger check if we have override source encoding.
			// if different (_source is all but not ascii) then trigger
			// skip if matching
		}
		if ($source_encoding != $to_encoding) {
			if ($source_encoding) {
				$string = mb_convert_encoding($string, $to_encoding, $source_encoding);
			} else {
				$string = mb_convert_encoding($string, $to_encoding);
			}
		}
		return $string;
	}
}

// __END__
