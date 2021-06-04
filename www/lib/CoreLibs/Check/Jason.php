<?php declare(strict_types=1);

/*
 * Converts a json string to array and stores error for later checking
 * can also return empty array on demand
 * and self set json as is on error as array
 */

namespace CoreLibs\Check;

class Jason
{
	private static $json_last_error;

	/**
	 * converts a json string to an array
	 * or inits an empty array on null string
	 * or failed convert to array
	 * In ANY case it will ALWAYS return array.
	 * Does not throw errors
	 * @param  string|null $json     a json string, or null data
	 * @param  bool        $override if set to true, then on json error
	 *                               set original value as array
	 * @return array                 returns an array from the json values
	 */
	public static function jsonConvertToArray(?string $json, bool $override = false): array
	{
		if ($json !== null) {
			$_json = json_decode($json, true);
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
	 * [jsonGetLastError description]
	 * @param  bool|boolean $return_string [default=false] if set to true
	 *                                     it will return the message string and not
	 *                                     the error number
	 * @return int|string                  Either error number (0 for no error)
	 *                                     or error string ('' for no error)
	 */
	public static function jsonGetLastError(bool $return_string = false)
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
			default:
				$json_error_string = 'Unknown error';
				break;
		}
		return $return_string === true ? $json_error_string : self::$json_last_error;
	}
}

// __END__
