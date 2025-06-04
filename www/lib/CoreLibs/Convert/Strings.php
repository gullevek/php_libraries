<?php

/*
 * string convert and transform functions
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

use CoreLibs\Combined\ArrayHandler;

class Strings
{
	/**
	 * return the number of elements in the split list
	 * 0 if nothing / invalid split
	 * 1 if no split character found
	 * n for the numbers in the split list
	 *
	 * @param  string $split_format
	 * @param  string $split_characters
	 * @return int
	 */
	public static function countSplitParts(
		string $split_format,
		string $split_characters = '-'
	): int {
		if (
			empty($split_format) ||
			// non valid characters inside, abort
			!preg_match("/^[0-9" . $split_characters . "]/", $split_format) ||
			preg_match('/[^\x20-\x7e]/', $split_characters)
		) {
			return 0;
		}
		$split_list = preg_split(
			// allowed split characters
			"/([" . $split_characters . "]{1})/",
			$split_format
		);
		if (!is_array($split_list)) {
			return 0;
		}
		return count(array_filter($split_list));
	}

	/**
	 * split format a string base on a split format string
	 * split format string is eg
	 * 4-4-4 that means 4 characters DASH 4 characters DASH 4 characters
	 * So a string in the format of
	 * ABCD1234EFGH will be ABCD-1234-EFGH
	 * Note a string LONGER then the maxium will be attached with the LAST
	 * split character. In above exmaple
	 * ABCD1234EFGHTOOLONG will be ABCD-1234-EFGH-TOOLONG
	 * If the characters are NOT ASCII it will return the string as is
	 *
	 * @param  string $string           string value to split
	 * @param  string $split_format     split format
	 * @return string                   split formatted string or original value if not chnaged
	 * @throws \InvalidArgumentException for empty split format, invalid values, split characters or split format
	 */
	public static function splitFormatString(
		string $string,
		string $split_format,
	): string {
		// skip if string or split format is empty is empty
		if (empty($string) || empty($split_format)) {
			return $string;
		}
		if (preg_match('/[^\x20-\x7e]/', $string)) {
			throw new \InvalidArgumentException(
				"The string to split can only be ascii characters: " . $string
			);
		}
		// get the split characters that are not numerical and check they are ascii
		$split_characters = self::removeDuplicates(preg_replace('/[0-9]/', '', $split_format));
		if (preg_match('/[^\x20-\x7e]/', $split_characters)) {
			throw new \InvalidArgumentException(
				"The split character has to be a valid ascii character: " . $split_characters
			);
		}
		if (!preg_match("/^[0-9" . $split_characters . "]+$/", $split_format)) {
			throw new \InvalidArgumentException(
				"The split format can only be numbers and the split characters: " . $split_format
			);
		}
		// split format list
		$split_list = preg_split(
			// allowed split characters
			"/([" . $split_characters . "]{1})/",
			$split_format,
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);
		// if this is false, or only one array, abort split
		if (!is_array($split_list) || count($split_list) == 1) {
			return $string;
		}
		$out = '';
		$pos = 0;
		$last_split = '';
		foreach ($split_list as $offset) {
			if (is_numeric($offset)) {
				$_part = substr($string, $pos, (int)$offset);
				if (empty($_part)) {
					break;
				}
				$out .= $_part;
				$pos += (int)$offset;
			} elseif ($pos) { // if first, do not add
				$out .= $offset;
				$last_split = $offset;
			}
		}
		if (!empty($out) && $pos < strlen($string)) {
			$out .= $last_split . substr($string, $pos);
		}
		// if last is not alphanumeric remove, remove
		if (!strcspn(substr($out, -1, 1), $split_characters)) {
			$out = substr($out, 0, -1);
		}
		// overwrite only if out is set
		if (!empty($out)) {
			return $out;
		} else {
			return $string;
		}
	}

	/**
	 * Split a string into n-length blocks with a split character inbetween
	 * This is simplified version from splitFormatString that uses
	 * fixed split length with a characters, this evenly splits the string out into the
	 * given length
	 * This works with non ASCII characters too
	 *
	 * @param  string $string           string to split
	 * @param  int    $split_length     split length, must be smaller than string and larger than 0
	 * @param  string $split_characters [default=-] the character to split, can be more than one
	 * @return string
	 * @throws \InvalidArgumentException Thrown if split length style is invalid
	 */
	public static function splitFormatStringFixed(
		string $string,
		int $split_length,
		string $split_characters = '-'
	): string {
		// if empty string or if split lenght is 0 or empty split characters
		// then we skip any splitting
		if (empty($string) || $split_length == 0 || empty($split_characters)) {
			return $string;
		}
		$return_string = '';
		$string_length = mb_strlen($string);
		// check that the length is not too short
		if ($split_length < 1 || $split_length >= $string_length) {
			throw new \InvalidArgumentException(
				"The split length must be at least 1 character and less than the string length to split. "
				. "Split length: " . $split_length . ", string length: " . $string_length
			);
		}
		for ($i = 0; $i < $string_length; $i += $split_length) {
			$return_string .= mb_substr($string, $i, $split_length) . $split_characters;
		}
		// remove last trailing character which is always the split char length
		return mb_substr($return_string, 0, -1 * mb_strlen($split_characters));
	}

	/**
	 * Strip any duplicated slahes from a path
	 * eg: //foo///bar/foo.inc -> /foo/bar/foo.inc
	 *
	 * @param  string $path Path to strip slashes from
	 * @return string       Clean path, on error returns original path
	 */
	public static function stripMultiplePathSlashes(string $path): string
	{
		return preg_replace(
			'#/+#',
			'/',
			$path
		) ?? $path;
	}

	/**
	 * Remove UTF8 BOM Byte string from line
	 * Note: this is often found in CSV files exported from Excel at the first row, first element
	 *
	 * @param  string $text
	 * @return string
	 */
	public static function stripUTF8BomBytes(string $text): string
	{
		return trim($text, pack('H*', 'EFBBBF'));
	}

	/**
	 * Make as string of characters unique
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function removeDuplicates(string $string): string
	{
		// combine again
		$result = implode(
			'',
			// unique list
			array_unique(
				// split into array
				mb_str_split($string)
			)
		);

		return $result;
	}

	/**
	 * check if all characters are in set
	 *
	 * @param  string $needle   Needle to search
	 * @param  string $haystack Haystack to search in
	 * @return bool             True on found, False if not in haystack
	 */
	public static function allCharsInSet(string $needle, string $haystack): bool
	{
		$input_length = strlen($needle);

		for ($i = 0; $i < $input_length; $i++) {
			if (strpos($haystack, $needle[$i]) === false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * converts a list of arrays of strings into a string of unique entries
	 * input arrays can be nested, only values are used
	 *
	 * @param  array<mixed> ...$char_lists
	 * @return string
	 */
	public static function buildCharStringFromLists(array ...$char_lists): string
	{
		return implode('', array_unique(
			ArrayHandler::flattenArray(
				array_merge(...$char_lists)
			)
		));
	}
}

// __END__
