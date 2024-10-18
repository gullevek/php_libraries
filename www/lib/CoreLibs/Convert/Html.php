<?php

/*
 * html convert functions
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Html
{
	public const SELECTED = 0;
	public const CHECKED = 1;

	/**
	 * full wrapper for html entities
	 *
	 * uses default params as: ENT_QUOTES | ENT_HTML5
	 * switches from ENT_HTML401 to ENT_HTML5 as we assume all our pages have <!DOCTYPE html>
	 * removed: ENT_SUBSTITUTE -> wrong characters will be replaced with space
	 * encodes in UTF-8
	 * does not double encode
	 *
	 * @param  mixed $string string to html encode
	 * @param  int   $flags [default: ENT_QUOTES | ENT_HTML5]
	 * @return mixed         if string, encoded, else as is (eg null)
	 */
	public static function htmlent(mixed $string, int $flags = ENT_QUOTES | ENT_HTML5): mixed
	{
		if (is_string($string)) {
			return htmlentities($string, $flags, 'UTF-8', false);
		}
		return $string;
	}

	/**
	 * strips out all line breaks or replaced with given string
	 * @param  string $string  string
	 * @param  string $replace replace character, default ' '
	 * @return string          cleaned string without any line breaks
	 */
	public static function removeLB(string $string, string $replace = ' '): string
	{
		return str_replace(["\n\r", "\r", "\n"], $replace, $string);
	}

	/**
	 * returns 'checked' or 'selected' if okay
	 * $needle is a var, $haystack an array or a string
	 * **** THE RETURN: VALUE WILL CHANGE TO A DEFAULT NULL IF NOT FOUND ****
	 *
	 * @param  array<mixed>|string $haystack (search in) haystack can be
	 *                                       an array or a string
	 * @param  string              $needle   needle (search for)
	 * @param  int                 $type     type: 0: returns selected, 1,
	 *                                       returns checked
	 * @return ?string                       returns checked or selected,
	 *                                       else returns null
	 */
	public static function checked(array|string $haystack, string $needle, int $type = 0): ?string
	{
		if (is_array($haystack) && in_array($needle, $haystack)) {
			return $type ? 'checked' : 'selected';
		} elseif (!is_array($haystack) && $haystack == $needle) {
			return $type ? 'checked' : 'selected';
		}
		return null;
	}
}

// __END__
