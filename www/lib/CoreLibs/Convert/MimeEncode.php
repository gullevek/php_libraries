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
	 * NOTE: This is only a wrapper for mb_encode_mimeheader to stay compatible
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
		// use the internal convert to mime header
		// it works from PHP 8.2 on
		$string = mb_encode_mimeheader($string, $encoding, 'B', $line_break);
		// before we end, reset internal encoding
		mb_internal_encoding($current_internal_encoding);
		// return mime encoded string
		return $string;
	}
}

// __END__
