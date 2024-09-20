<?php

/*
 * Run is_<type> checks and return default value if not this type
 * This will return default null on invalid entries
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

use CoreLibs\Convert\Extends\SetVarTypeMain;

class SetVarTypeNull extends Extends\SetVarTypeMain
{
	/**
	 * Check is input is string, if not return default string.
	 * Will return null if no string as default.
	 *
	 * @param  mixed       $val     Input value
	 * @param  string|null $default Default override value
	 * @return string|null          Input value or default as string/null
	 */
	public static function setStr(mixed $val, ?string $default = null): ?string
	{
		return SetVarTypeMain::setStrMain($val, $default, true);
	}

	/**
	 * Convert input to string if possible.
	 * Will only work on string/int/float/bool/null types.
	 * Will return null if convert failed as default.
	 *
	 * @param  mixed       $val     Input value
	 * @param  string|null $default Default override value
	 * @return string|null          Input value as string or default as string/null
	 */
	public static function makeStr(mixed $val, ?string $default = null): ?string
	{
		return SetVarTypeMain::makeStrMain($val, $default, true);
	}


	/**
	 * Check if input is int, if not return default value null.
	 *
	 * @param  mixed    $val     Input value
	 * @param  int|null $default Default override value
	 * @return int|null          Input value or default as int/null
	 */
	public static function setInt(mixed $val, ?int $default = null): ?int
	{
		return SetVarTypeMain::setIntMain($val, $default, true);
	}

	/**
	 * Convert intput to int if possible, if not return default value value null.
	 *
	 * @param  mixed    $val     Input value $val
	 * @param  int|null $default Default override value
	 * @return int|null          Input value as int or default as int/null
	 */
	public static function makeInt(mixed $val, ?int $default = null): ?int
	{
		return SetVarTypeMain::makeIntMain($val, $default, true);
	}

	/**
	 * Check if input is float, if not return default value value null.
	 *
	 * @param  mixed      $val     Input value $val
	 * @param  float|null $default Default override value
	 * @return float|null          Input value or default as float/null
	 */
	public static function setFloat(mixed $val, ?float $default = null): ?float
	{
		return SetVarTypeMain::setFloatMain($val, $default, true);
	}

	/**
	 * Convert input to float, if not possible return default value null.
	 *
	 * @param  mixed      $val     Input value $val
	 * @param  float|null $default Default override value
	 * @return float|null          Input value as float or default as float/null
	 */
	public static function makeFloat(mixed $val, ?float $default = null): ?float
	{
		return SetVarTypeMain::makeFloatMain($val, $default, true);
	}

	/**
	 * Check if input is array, if not return default value null.
	 *
	 * @param  mixed             $val     Input value $val
	 * @param  array<mixed>|null $default Default override value
	 * @return array<mixed>|null          Input value or default as array/null
	 */
	public static function setArray(mixed $val, ?array $default = null): ?array
	{
		return SetVarTypeMain::setArrayMain($val, $default, true);
	}

	/**
	 * Check if input is bool, if not will return default value null.
	 *
	 * @param  mixed     $val     Input value $val
	 * @param  bool|null $default Default override value
	 * @return bool|null          Input value or default as bool/null
	 */
	public static function setBool(mixed $val, ?bool $default = null): ?bool
	{
		return SetVarTypeMain::setBoolMain($val, $default, true);
	}

	/**
	 * Convert anything to bool
	 *
	 * @param  mixed     $val Input value $val
	 * @return bool|null      Input value as bool or default as bool/null
	 */
	public static function makeBool(mixed $val): ?bool
	{
		// note that the default value here is irrelevant, we return null
		// on unsetable string var
		return SetVarTypeMain::makeBoolMain($val, false, true);
	}
}

// __END__
