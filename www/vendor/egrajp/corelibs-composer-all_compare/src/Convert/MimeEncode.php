<?php

/*
 * alternate for header mime encode to void problems with long strings and
 * spaces/strange encoding problems.
 * Orignal issues during PHP5/7
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class MimeEncode
{
	/**
	 * wrapper function for mb mime convert
	 * for correct conversion with long strings
	 *
	 * @param  string $string     string to encode
	 * @param  string $encoding   target encoding
	 * @param  string $line_break default line break is \r\n
	 * @return string             encoded string
	 */
	public static function __mbMimeEncode(
		string $string,
		string $encoding,
		string $line_break = "\r\n"
	): string {
		$current_internal_encoding = mb_internal_encoding();
		// set internal encoding, so the mimeheader encode works correctly
		mb_internal_encoding($encoding);
		// if a subject, make a work around for the broken mb_mimencode
		$pos = 0;
		// after 36 single bytes characters,
		// if then comes MB, it is broken
		// has to 2 x 36 < 74 so the mb_encode_mimeheader
		// 74 hardcoded split does not get triggered
		$split = 36;
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
				$_string .= $line_break . " ";
			} elseif (
				// hack for plain text with space at the end
				mb_strlen($output, $encoding) == $split &&
				mb_substr($output, -1, 1, $encoding) == " "
			) {
				// if output ends with space, add one more
				$_string_encoded .= " ";
			}
			$_string .= $_string_encoded;
		}
		// strip out any spaces BEFORE a line break
		$string = str_replace(" " . $line_break, $line_break, $_string);
		// before we end, reset internal encoding
		mb_internal_encoding($current_internal_encoding);
		// return mime encoded string
		return $string;
	}
}

// __END__
