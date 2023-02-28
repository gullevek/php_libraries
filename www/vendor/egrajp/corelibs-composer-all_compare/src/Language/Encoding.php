<?php

/*
 * deprecated function calls
 * Language\Encoding::__mbMimeEncode -> Convert\MimeEncode::__mbMimeEncode
 * Langauge\Encoding::checkConvertEncoding -> Check\Encoding::checkConvertEncoding
 * Langauge\Encoding::setErrorChar -> Check\Encoding::setErrorChar
 * Langauge\Encoding::getErrorChar -> Check\Encoding::getErrorChar
 * Langauge\Encoding::convertEncoding -> Convert\Encoding::convertEncoding
 */

declare(strict_types=1);

namespace CoreLibs\Language;

class Encoding
{
	/**
	 * wrapper function for mb mime convert
	 * for correct conversion with long strings
	 *
	 * @param  string $string     string to encode
	 * @param  string $encoding   target encoding
	 * @param  string $line_break default line break is \r\n
	 * @return string             encoded string
	 * @deprecated Use \CoreLibs\Convert\MimeEncode::__mbMimeEncode();
	 */
	public static function __mbMimeEncode(
		string $string,
		string $encoding,
		string $line_break = "\r\n"
	): string {
		return \CoreLibs\Convert\MimeEncode::__mbMimeEncode($string, $encoding, $line_break);
	}

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
	 * @deprecated Use \CoreLibs\Check\Encoding::setErrorChar();
	 */
	public static function setErrorChar(string|int|null $string): void
	{
		\CoreLibs\Check\Encoding::setErrorChar($string);
	}

	/**
	 * get the current set error character
	 *
	 * @param  bool $return_substitute_func if set to true return the set
	 *                                      character from the php function
	 *                                      directly
	 * @return string|int Set error character
	 * @deprecated Use \CoreLibs\Check\Encoding::getErrorChar();
	 */
	public static function getErrorChar(bool $return_substitute_func = false): string|int
	{
		return \CoreLibs\Check\Encoding::getErrorChar($return_substitute_func);
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
	 * @deprecated Use \CoreLibs\Check\Encoding::checkConvertEncoding();
	 */
	public static function checkConvertEncoding(
		string $string,
		string $from_encoding,
		string $to_encoding
	): array|false {
		return \CoreLibs\Check\Encoding::checkConvertEncoding($string, $from_encoding, $to_encoding);
	}

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
	 * @deprecated Use \CoreLibs\Convert\Encoding::convertEncoding();
	 */
	public static function convertEncoding(
		string $string,
		string $to_encoding,
		string $source_encoding = '',
		bool $auto_check = true
	): string {
		return \CoreLibs\Convert\Encoding::convertEncoding(
			$string,
			$to_encoding,
			$source_encoding,
			$auto_check
		);
	}
}

// __END__
