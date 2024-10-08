<?php

/*
 * string convert and transform functions
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

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
	 *
	 * @param  string $value            string value to split
	 * @param  string $split_format     split format
	 * @param  string $split_characters list of charcters with which we split
	 *                                  if not set uses dash ('-')
	 * @return string                   split formatted string or original value if not chnaged
	 */
	public static function splitFormatString(
		string $value,
		string $split_format,
		string $split_characters = '-'
	): string {
		if (
			// abort if split format is empty
			empty($split_format) ||
			// if not in the valid ASCII character range for any of the strings
			preg_match('/[^\x20-\x7e]/', $value) ||
			// preg_match('/[^\x20-\x7e]/', $split_format) ||
			preg_match('/[^\x20-\x7e]/', $split_characters) ||
			// only numbers and split characters in split_format
			!preg_match("/[0-9" . $split_characters . "]/", $split_format)
		) {
			return $value;
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
			return $value;
		}
		$out = '';
		$pos = 0;
		$last_split = '';
		foreach ($split_list as $offset) {
			if (is_numeric($offset)) {
				$_part = substr($value, $pos, (int)$offset);
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
		if (!empty($out) && $pos < strlen($value)) {
			$out .= $last_split . substr($value, $pos);
		}
		// if last is not alphanumeric remove, remove
		if (!strcspn(substr($out, -1, 1), $split_characters)) {
			$out = substr($out, 0, -1);
		}
		// overwrite only if out is set
		if (!empty($out)) {
			return $out;
		} else {
			return $value;
		}
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
}

// __END__
