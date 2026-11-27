<?php

/*
 * check if string is valid in target encoding
 */

declare(strict_types=1);

namespace CoreLibs\Check;

class Encoding
{
	/** @var string error character, or none/long/entity */
	private static string $mb_error_char = '';

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
	 * @throws \InvalidArgumentException if the character or code point is not valid
	 */
	public static function setErrorChar(string|int|null $string): void
	{
		if (empty($string)) {
			$string = 'none';
		}
		// if not special string or char but code point
		if (in_array($string, ['none', 'long', 'entity'], true)) {
			$error_char = $string;
			$substitute = $string;
		} else {
			// always convert to char for internal use, null on invalid code point/char
			$error_char = \IntlChar::chr($string);
			// mb_substitute_character needs the code point
			$substitute = is_string($string) ? \IntlChar::ord($string) : $string;
			if ($error_char === null || $substitute === null) {
				throw new \InvalidArgumentException(
					'Invalid error character or code point: ' . $string
				);
			}
		}
		try {
			mb_substitute_character($substitute);
		} catch (\ValueError $e) {
			// eg surrogate code points like 0xD800
			throw new \InvalidArgumentException(
				'Invalid error character or code point: ' . $string,
				0,
				$e
			);
		}
		// only set after mb_substitute_character accepted it
		self::$mb_error_char = $error_char;
	}

	/**
	 * get the current set error character
	 *
	 * @param  bool $return_substitute_func if set to true return the set
	 *                                      character from the php function
	 *                                      directly
	 * @return string|int Set error character
	 */
	public static function getErrorChar(bool $return_substitute_func = false): string|int
	{
		// return mb_substitute_character();
		if ($return_substitute_func === true) {
			// if false abort with error
			if (($return = mb_substitute_character()) === false) {
				return self::$mb_error_char;
			}
			return $return;
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
	 * @return array<string>|false       false if no error or
	 *                                   array with failed characters
	 */
	public static function checkConvertEncoding(
		string $string,
		string $from_encoding,
		string $to_encoding
	): array|false {
		// convert to target encoding and convert back
		$temp = mb_convert_encoding($string, $to_encoding, $from_encoding);
		if ($temp === false) {
			return false;
		}
		$compare = mb_convert_encoding($temp, $from_encoding, $to_encoding);
		if ($compare === false) {
			return false;
		}
		// if string does not match anymore we have a convert problem
		if ($string == $compare) {
			return false;
		}
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
				ord($char[0]) != 194
			) {
				$failed[] = $char;
			}
		}
		return $failed;
	}
}

// __END__
