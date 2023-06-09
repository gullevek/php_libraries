<?php

/**
 * AUTOR: Clemens Schwaighofer
 * CREATED: 2023/6/9
 * DESCRIPTION:
 * DB Options for convert type
 *
 * off: no conversion (all string)
 * on: int/bool only
 * json: json/jsonb to array
 * numeric: any numeric or float to float
 * bytes: decode bytea to string
*/

declare(strict_types=1);

namespace CoreLibs\DB\Options;

enum Convert: int
{
	case off = 0;
	case on = 1;
	case json = 2;
	case numeric = 4;
	case bytea = 8;

	/**
	 * get internal name from string value
	 *
	 * @param non-empty-string $name
	 * @return self
	 */
	public static function fromName(string $name): self
	{
		return match ($name) {
			'Off', 'off', 'OFF', 'convert_off', 'CONVERT_OFF' => self::off,
			'On', 'on', 'ON', 'convert_on', 'CONVERT_ON' => self::on,
			'Json', 'json', 'JSON', 'convert_json', 'CONVERT_JSON' => self::json,
			'Numeric', 'numeric', 'NUMERIC', 'convert_numeric', 'CONVERT_NUMERIC' => self::numeric,
			'Bytea', 'bytea', 'BYTEA', 'convert_bytea', 'CONVERT_BYTEA' => self::bytea,
			default => self::off,
		};
	}

	/**
	 * Get internal name from int value
	 *
	 * @param  int  $value
	 * @return self
	 */
	public static function fromValue(int $value): self
	{
		return self::from($value);
	}
}

// __END__
