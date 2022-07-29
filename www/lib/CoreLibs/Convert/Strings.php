<?php

/*
 * string convert and transform functions
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Strings
{
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
		// abort if split format is empty
		if (empty($split_format)) {
			return $value;
		}
		// if not in the valid ASCII character range
		// might need some tweaking
		if (preg_match('/[^\x20-\x7e]/', $value)) {
			return $value;
		}
		// if (!mb_check_encoding($value, 'ASCII')) {
		// 	return $value;
		// }
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
			} else {
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
}

// __END__
