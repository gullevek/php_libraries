<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2025/1/17
 * DESCRIPTION:
 * Deprecated helper for fputcsv
*/

declare(strict_types=1);

namespace CoreLibs\DeprecatedHelper;

use InvalidArgumentException;

class Deprecated84
{
	/**
	 * This is a wrapper for fputcsv to fix deprecated warning for $escape parameter
	 * See: https://www.php.net/manual/en/function.fputcsv.php
	 * escape parameter deprecation and recommend to set to "" for compatible with PHP 9.0
	 *
	 * @param mixed $stream
	 * @param array<mixed> $fields
	 * @param string $separator
	 * @param string $enclosure
	 * @param string $escape
	 * @param string $eol
	 * @return int|false
	 * @throws InvalidArgumentException
	 */
	public static function fputcsv(
		mixed $stream,
		array $fields,
		string $separator = ",",
		string $enclosure = '"',
		string $escape = '', // set to empty for future compatible
		string $eol = PHP_EOL
	): int | false {
		if (!is_resource($stream)) {
			throw new \InvalidArgumentException("fputcsv stream parameter must be a resrouce");
		}
		return fputcsv($stream, $fields, $separator, $enclosure, $escape, $eol);
	}

	/**
	 * This is a wrapper for fgetcsv to fix deprecated warning for $escape parameter
	 * See: https://www.php.net/manual/en/function.fgetcsv.php
	 * escape parameter deprecation and recommend to set to "" for compatible with PHP 9.0
	 *
	 * @param mixed $stream
	 * @param null|int $length
	 * @param string $separator
	 * @param string $enclosure
	 * @param string $escape
	 * @return array<mixed>|false
	 * @throws InvalidArgumentException
	 */
	public static function fgetcsv(
		mixed $stream,
		?int $length = null,
		string $separator = ',',
		string $enclosure = '"',
		string $escape = '' // set to empty for future compatible
	): array | false {
		if (!is_resource($stream)) {
			throw new \InvalidArgumentException("fgetcsv stream parameter must be a resrouce");
		}
		return fgetcsv($stream, $length, $separator, $enclosure, $escape);
	}

	/**
	 * This is a wrapper for str_getcsv to fix deprecated warning for $escape parameter
	 * See: https://www.php.net/manual/en/function.str-getcsv.php
	 * escape parameter deprecation and recommend to set to "" for compatible with PHP 9.0
	 *
	 * @param string $string
	 * @param string $separator
	 * @param string $enclosure
	 * @param string $escape
	 * @return array<mixed>
	 */
	// phpcs:disable PSR1.Methods.CamelCapsMethodName
	public static function str_getcsv(
		string $string,
		string $separator = ",",
		string $enclosure = '"',
		string $escape = '' // set to empty for future compatible
	): array {
		return str_getcsv($string, $separator, $enclosure, $escape);
	}
	// phpcs:enable PSR1.Methods.CamelCapsMethodName
}

// __END__
