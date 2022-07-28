<?php

/*
 * DEPRECATED: Use correct Convert\Json:: instead
 */

declare(strict_types=1);

namespace CoreLibs\Check;

use CoreLibs\Convert\Json;

class Jason
{
	/**
	 * @param  string|null $json     a json string, or null data
	 * @param  bool        $override if set to true, then on json error
	 *                               set original value as array
	 * @return array<mixed>          returns an array from the json values
	 * @deprecated Use Json::jsonConvertToArray()
	 */
	public static function jsonConvertToArray(?string $json, bool $override = false): array
	{
		return Json::jsonConvertToArray($json, $override);
	}

	/**
	 * @param  bool|boolean $return_string [default=false] if set to true
	 *                                     it will return the message string and not
	 *                                     the error number
	 * @return int|string                  Either error number (0 for no error)
	 *                                     or error string ('' for no error)
	 * @deprecated Use Json::jsonGetLastError()
	 */
	public static function jsonGetLastError(bool $return_string = false)
	{
		return Json::jsonGetLastError($return_string);
	}
}

// __END__
