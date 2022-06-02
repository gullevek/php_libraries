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
	 * @param  mixed $string string to html encode
	 * @return mixed         if string, encoded, else as is (eg null)
	 */
	public static function htmlent($string)
	{
		if (is_string($string)) {
			return htmlentities($string, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
		} else {
			return $string;
		}
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
	public static function checked($haystack, string $needle, int $type = 0): ?string
	{
		if (is_array($haystack)) {
			if (in_array($needle, $haystack)) {
				return $type ? 'checked' : 'selected';
			}
		} else {
			if ($haystack == $needle) {
				return $type ? 'checked' : 'selected';
			}
		}
		return null;
	}
}

// __END__
