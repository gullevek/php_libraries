<?php

/*
 * Run is_<type> checks and return default value if not this type
 * This will return a default value as always what is expected and never null
 * Use this for santize output from multi return functions where we know what
 * will come back
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

use CoreLibs\Convert\Extends\SetVarTypeMain;

class SetVarType extends Extends\SetVarTypeMain
{
	/**
	 * Check is input is string, if not return default string.
	 * Will always return string
	 *
	 * @param  mixed  $val     Input value
	 * @param  string $default Default override value
	 * @return string          Input value or default as string
	 */
	public static function setStr(mixed $val, string $default = ''): string
	{
		return (string)SetVarTypeMain::setStrMain($val, $default, false);
	}

	/**
	 * Convert input to string if possible.
	 * Will only work on string/int/float/bool/null types
	 * Will always return string
	 *
	 * @param  mixed  $val     Input value
	 * @param  string $default Default override value
	 * @return string          Input value as string or default as string
	 */
	public static function makeStr(mixed $val, string $default = ''): string
	{
		return (string)SetVarTypeMain::makeStrMain($val, $default, false);
	}

	/**
	 * Check if input is int, if not return default int value 0.
	 * Will always return int.
	 *
	 * @param  mixed $val     Input value
	 * @param  int   $default Default override value
	 * @return int            Input value or default as int
	 */
	public static function setInt(mixed $val, int $default = 0): int
	{
		return (int)SetVarTypeMain::setIntMain($val, $default, false);
	}

	/**
	 * Convert intput to int if possible, if not return default value 0.
	 * Will always return int.
	 *
	 * @param  mixed $val     Input value
	 * @param  int   $default Default override value
	 * @return int            Input value as int or default as int
	 */
	public static function makeInt(mixed $val, int $default = 0): int
	{
		return (int)SetVarTypeMain::makeIntMain($val, $default, false);
	}

	/**
	 * Check if input is float, if not return default value value 0.0.
	 * Will always return float
	 *
	 * @param  mixed $val     Input value
	 * @param  float $default Default override value
	 * @return float          Input value or default as float
	 */
	public static function setFloat(mixed $val, float $default = 0.0): float
	{
		return (float)SetVarTypeMain::setFloatMain($val, $default, false);
	}

	/**
	 * Convert input to float, if not possible return default value 0.0.
	 * Will always return float
	 *
	 * @param  mixed $val     Input value
	 * @param  float $default Default override value
	 * @return float          Input value as float or default as float
	 */
	public static function makeFloat(mixed $val, float $default = 0.0): float
	{
		return (float)SetVarTypeMain::makeFloatMain($val, $default, false);
	}

	/**
	 * Check if input is array, if not return default empty array.
	 * Will always return array.
	 *
	 * @param  mixed        $val     Input value
	 * @param  array<mixed> $default Default override value
	 * @return array<mixed>          Input value or default as array
	 */
	public static function setArray(mixed $val, array $default = []): array
	{
		return (array)SetVarTypeMain::setArrayMain($val, $default, false);
	}

	/**
	 * Check if input is bool, if not will return default value false.
	 * Will aways return bool.
	 *
	 * @param  mixed $val     Input value
	 * @param  bool  $default Default override value
	 * @return bool           Input value or default as bool
	 */
	public static function setBool(mixed $val, bool $default = false): bool
	{
		return (bool)SetVarTypeMain::setBoolMain($val, $default, false);
	}

	/**
	 * Convert anything to bool
	 *
	 * @param  mixed $val     Input value
	 * @param  bool  $default Default override value
	 * @return bool           Input value as bool or default as bool
	 */
	public static function makeBool(mixed $val, bool $default = false): bool
	{
		return (bool)SetVarTypeMain::makeBoolMain($val, $default, false);
	}
}

// __END__
