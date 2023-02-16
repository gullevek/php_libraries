<?php

/*
 * check if string is valid in target encoding
 */

declare(strict_types=1);

namespace CoreLibs\Check;

class Encoding
{
	/** @var int<min, -1>|int<1, max>|string */
	private static $mb_error_char = '';

	/**
	 * set error char
	 *
	 * @param  string|int|null $string The character to use to represent
	 *                                 error chars
	 *                                 "long" for long, "none" for none
	 *                                 or a valid code point in int
	 *                                 like 0x2234 (8756, ∴)
	 *                                 default character is ? (63)
	 *                                 if null is set then "none"
	 * @return void
	 */
	public static function setErrorChar($string): void
	{
		if (empty($string)) {
			$string = 'none';
		}
		// if not special string or char but code point
		if (in_array($string, ['none', 'long', 'entity'])) {
			self::$mb_error_char = $string;
		} else {
			// always convert to char for internal use
			self::$mb_error_char = \IntlChar::chr($string);
			// if string convert to code point
			if (is_string($string)) {
				$string = \IntlChar::ord($string);
			}
		}
		mb_substitute_character($string);
	}

	/**
	 * get the current set error character
	 *
	 * @param  bool $return_substitute_func if set to true return the set
	 *                                      character from the php function
	 *                                      directly
	 * @return string|int Set error character
	 */
	public static function getErrorChar(bool $return_substitute_func = false)
	{
		// return mb_substitute_character();
		if ($return_substitute_func === true) {
			return mb_substitute_character();
		} else {
			return self::$mb_error_char;
		}
	}

	/**
	 * test if a string can be safely convert between encodings.
	 * mostly utf8 to shift jis
	 * the default compare has a possibility of failure, especially with windows
	 * it is recommended to the following in the script which uses this method:
	 * mb_substitute_character(0x2234);
	 * $class->mb_error_char = '∴';
	 * if check to Shift JIS
	 * if check to ISO-2022-JP
	 * if check to ISO-2022-JP-MS
	 * set three dots (∴) as wrong character for correct convert error detect
	 * (this char is used, because it is one of the least used ones)
	 *
	 * @param  string     $string        string to test
	 * @param  string     $from_encoding encoding of string to test
	 * @param  string     $to_encoding   target encoding
	 * @return bool|array<string>        false if no error or
	 *                                   array with failed characters
	 */
	public static function checkConvertEncoding(
		string $string,
		string $from_encoding,
		string $to_encoding
	) {
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
				// the ord 194 is a hack to fix the IE7/IE8
				// bug with line break and illegal character
				if (
					(($char != $r_char && (!self::$mb_error_char ||
					in_array(self::$mb_error_char, ['none', 'long', 'entity']))) ||
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
}

// __END__
