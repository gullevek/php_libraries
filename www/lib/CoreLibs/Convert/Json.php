<?php

/*
 * Converts a json string to array and stores error for later checking
 * can also return empty array on demand
 * and self set json as is on error as array
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Json
{
	/** @var int */
	private static $json_last_error;

	/**
	 * converts a json string to an array
	 * or inits an empty array on null string
	 * or failed convert to array
	 * In ANY case it will ALWAYS return array.
	 * Does not throw errors
	 *
	 * @param  string|null $json     a json string, or null data
	 * @param  bool        $override if set to true, then on json error
	 *                               set original value as array
	 * @return array<mixed>          returns an array from the json values
	 */
	public static function jsonConvertToArray(?string $json, bool $override = false, int $flags = 0): array
	{
		if ($json !== null) {
			// if flags has JSON_THROW_ON_ERROR remove it
			if ($flags & JSON_THROW_ON_ERROR) {
				$flags = $flags & ~JSON_THROW_ON_ERROR;
			}
			$_json = json_decode($json, true, flags:$flags);
			if (self::$json_last_error = json_last_error()) {
				if ($override == true) {
					// init return as array with original as element
					$json = [$json];
				} else {
					$json = [];
				}
			} else {
				$json = $_json;
			}
		} else {
			$json = [];
		}
		// be sure that we return an array
		return (array)$json;
	}

	/**
	 * convert array to json
	 * Will set empty json {} on false/error
	 * Error can be read with jsonGetLastError
	 * Deos not throw errors
	 *
	 * @param  array<mixed> $data
	 * @param  int          $flags [JSON_UNESCAPED_UNICODE] json_encode flags as is
	 * @return string              JSON string or '{}' if false
	 */
	public static function jsonConvertArrayTo(array $data, int $flags = JSON_UNESCAPED_UNICODE): string
	{
		$json_string = json_encode($data, $flags) ?: '{}';
		self::$json_last_error = json_last_error();
		return (string)$json_string;
	}

	/**
	 * Validate if a json string could be decoded.
	 * Weill set the internval last error state and info can be read with jsonGetLastError
	 *
	 * @param  string $json
	 * @param  int    $flags only JSON_INVALID_UTF8_IGNORE is currently allowed
	 * @return bool
	 */
	public static function jsonValidate(string $json, int $flags = 0): bool
	{
		$json_valid = json_validate($json, flags:$flags);
		self::$json_last_error = json_last_error();
		return $json_valid;
	}

	/**
	 * returns human readable string for json errors thrown in jsonConvertToArray
	 * Source: https://www.php.net/manual/en/function.json-last-error.php
	 *
	 * @param  bool       $return_string [default=false] if set to true
	 *                                   it will return the message string and not
	 *                                   the error number
	 * @return int|string                Either error number (0 for no error)
	 *                                   or error string ('' for no error)
	 */
	public static function jsonGetLastError(bool $return_string = false): int|string
	{
		$json_error_string = '';
		// valid errors as of php 8.0
		switch (self::$json_last_error) {
			case JSON_ERROR_NONE:
				$json_error_string = '';
				break;
			case JSON_ERROR_DEPTH:
				$json_error_string = 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$json_error_string = 'Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$json_error_string = 'Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$json_error_string = 'Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$json_error_string = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			case JSON_ERROR_RECURSION:
				$json_error_string = 'One or more recursive references in the value to be encoded';
				break;
			case JSON_ERROR_INF_OR_NAN:
				$json_error_string = 'One or more NAN or INF values in the value to be encoded';
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$json_error_string = '	A value of a type that cannot be encoded was given';
				break;
			case JSON_ERROR_INVALID_PROPERTY_NAME:
				$json_error_string = 'A key starting with \u0000 character was in the string';
				break;
			case JSON_ERROR_UTF16:
				$json_error_string = 'Single unpaired UTF-16 surrogate in unicode escape';
				break;
			default:
				$json_error_string = 'Unknown error';
				break;
		}
		return $return_string === true ? $json_error_string : self::$json_last_error;
	}

	/**
	 * wrapper to call convert array to json with pretty print
	 *
	 * @param  array<mixed>  $data
	 * @return string
	 */
	public static function jsonPrettyPrint(array $data): string
	{
		return self::jsonConvertArrayTo(
			$data,
			JSON_PRETTY_PRINT |
			JSON_UNESCAPED_LINE_TERMINATORS |
			JSON_UNESCAPED_SLASHES |
			JSON_UNESCAPED_UNICODE
		);
	}
}

// __END__
