<?php

/*
 * hash wrapper functions for old problem fixes
 */

declare(strict_types=1);

namespace CoreLibs\Language;

class Encoding
{
	/** @var string */
	private static $mb_error_char = '';

	/**
	 * wrapper function for mb mime convert, for correct conversion with long strings
	 * @param  string $string   string to encode
	 * @param  string $encoding target encoding
	 * @return string           encoded string
	 */
	public static function __mbMimeEncode(string $string, string $encoding): string
	{
		// set internal encoding, so the mimeheader encode works correctly
		mb_internal_encoding($encoding);
		// if a subject, make a work around for the broken mb_mimencode
		$pos = 0;
		$split = 36; // after 36 single bytes characters, if then comes MB, it is broken
					 // has to 2 x 36 < 74 so the mb_encode_mimeheader 74 hardcoded split does not get triggered
		$_string = '';
		while ($pos < mb_strlen($string, $encoding)) {
			$output = mb_strimwidth($string, $pos, $split, "", $encoding);
			$pos += mb_strlen($output, $encoding);
			// if the strinlen is 0 here, get out of the loop
			if (!mb_strlen($output, $encoding)) {
				$pos += mb_strlen($string, $encoding);
			}
			$_string_encoded = mb_encode_mimeheader($output, $encoding);
			// only make linebreaks if we have mime encoded code inside
			// the space only belongs in the second line
			if ($_string && preg_match("/^=\?/", $_string_encoded)) {
				$_string .= "\n ";
			}
			$_string .= $_string_encoded;
		}
		// strip out any spaces BEFORE a line break
		$string = str_replace(" \n", "\n", $_string);
		return $string;
	}

	/**
	 * set error char
	 *
	 * @param  string $string The character to use to represent error chars
	 * @return void
	 */
	public static function setErrorChar(string $string): void
	{
		self::$mb_error_char = $string;
	}

	/**
	 * get the current set error character
	 *
	 * @return string Set error character
	 */
	public static function getErrorChar(): string
	{
		return self::$mb_error_char;
	}

	/**
	 * test if a string can be safely convert between encodings. mostly utf8 to shift jis
	 * the default compare has a possibility of failure, especially with windows
	 * it is recommended to the following in the script which uses this method:
	 * mb_substitute_character(0x2234);
	 * $class->mb_error_char = '∴';
	 * if check to Shift JIS
	 * if check to ISO-2022-JP
	 * if check to ISO-2022-JP-MS
	 * set three dots (∴) as wrong character for correct convert error detect
	 * (this char is used, because it is one of the least used ones)
	 * @param  string     $string        string to test
	 * @param  string     $from_encoding encoding of string to test
	 * @param  string     $to_encoding   target encoding
	 * @return bool|array<string>        false if no error or array with failed characters
	 */
	public static function checkConvertEncoding(string $string, string $from_encoding, string $to_encoding)
	{
		// convert to target encoding and convert back
		$temp = mb_convert_encoding($string, $to_encoding, $from_encoding);
		$compare = mb_convert_encoding($temp, $from_encoding, $to_encoding);
		// if string does not match anymore we have a convert problem
		if ($string != $compare) {
			$failed = [];
			// go through each character and find the ones that do not match
			for ($i = 0, $iMax = mb_strlen($string, $from_encoding); $i < $iMax; $i++) {
				$char = mb_substr($string, $i, 1, $from_encoding);
				$r_char = mb_substr($compare, $i, 1, $from_encoding);
				// the ord 194 is a hack to fix the IE7/IE8 bug with line break and illegal character
				if (
					(($char != $r_char && !self::$mb_error_char) ||
					($char != $r_char && $r_char == self::$mb_error_char && self::$mb_error_char)) &&
					ord($char) != 194
				) {
					$failed[] = $char;
				}
			}
			return $failed;
		} else {
			return false;
		}
	}

	/**
	 * detects the source encoding of the string and if doesn't match
	 * to the given target encoding it convert is
	 * if source encoding is set and auto check is true (default) a second
	 * check is done so that the source string encoding actually matches
	 * will be skipped if source encoding detection is ascii
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
			// if different (_source is all but not ascii) then trigger skip if matching
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
