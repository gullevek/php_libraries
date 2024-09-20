<?php

/*
 * Run is_<type> checks and return default value if not this type
 * This will return default null on invalid entries
 */

declare(strict_types=1);

namespace CoreLibs\Convert\Extends;

class SetVarTypeMain
{
	/**
	 * If input variable is string then returns it, else returns default set
	 * if not null is true, then null as return is allowed, else return is
	 * converted to string
	 *
	 * @param  mixed       $val     Input variable
	 * @param  string|null $default Default value
	 * @param  bool        $to_null Convert to null (default no)
	 * @return string|null          Input var or default value
	 */
	protected static function setStrMain(
		mixed $val,
		?string $default = null,
		bool $to_null = false
	): ?string {
		if (
			$val === null ||
			is_scalar($val) ||
			$val instanceof \Stringable
		) {
			return (string)$val;
		}
		if ($to_null === false) {
			return (string)$default;
		}
		return $default;
	}

	/**
	 * Will convert input data to string if possible.
	 * Runs for string/int/float/bool/null
	 * Will skip array/object/resource/callable/etc and use default for that
	 * Note: this is pretty much the same as setStrMain because string is easy
	 *
	 * @param  mixed       $val     Input variable
	 * @param  string|null $default Default value
	 * @param  bool        $to_null Convert to null (default no)
	 * @return string|null          Converted input data to string/null
	 */
	protected static function makeStrMain(
		mixed $val,
		?string $default = null,
		bool $to_null = false
	): ?string {
		// int/float/string/bool/null, everything else is ignored
		// no: array/object/resource/callable
		if (
			is_int($val) ||
			is_float($val) ||
			is_string($val) ||
			is_bool($val) ||
			is_null($val)
		) {
			return (string)$val;
		}
		if ($to_null === false) {
			return (string)$default;
		}
		return $default;
	}


	/**
	 * If input variable is int, return it, else return default value. If to_null
	 * is true then null as return is allowed, else only int is returned
	 * Note, if float is sent in, int is returned
	 *
	 * @param  mixed    $val     Input variable
	 * @param  int|null $default Default value
	 * @param  bool     $to_null Convert to null (default no)
	 * @return int|null          Input var or default value
	 */
	protected static function setIntMain(
		mixed $val,
		?int $default = null,
		bool $to_null = false
	): ?int {
		if (is_numeric($val)) {
			return (int)$val;
		}
		if ($to_null === false) {
			return (int)$default;
		}
		return $default;
	}

	/**
	 * Convert input to int via filter_var. If not convertable return default value.
	 * If to_null is set to true null return is allowed
	 * NOTE: this is only a drastic fallback and not recommned for special use.
	 * It will try to check via filter_var if we can get an int value and then use
	 * intval to convert it.
	 * Reason is that filter_var will convert eg 1.5 to 15 instead 1
	 * One is very wrong, the other is at least better, but not perfect
	 *
	 * @param  mixed    $val     Input variable
	 * @param  int|null $default Default value
	 * @param  bool     $to_null Convert to null (default no)
	 * @return int|null          Converted input data to int/null
	 */
	protected static function makeIntMain(
		mixed $val,
		?int $default = null,
		bool $to_null = false
	): ?int {
		// if we can filter it to a valid int, we can convert it
		// we so avoid object, array, etc
		if (
			filter_var(
				$val,
				FILTER_SANITIZE_NUMBER_INT
			) !== false
		) {
			return intval($val);
		}
		if ($to_null === false) {
			return (int)$default;
		}
		return $default;
	}

	/**
	 * If input is float return it, else set to default value. If to_null is set
	 * to true, allow null return
	 * Note if an int is sent in, float is returned
	 *
	 * @param  mixed      $val     Input variable
	 * @param  float|null $default Default value
	 * @param  bool       $to_null Convert to null (default no)
	 * @return float|null          Input var or default value
	 */
	protected static function setFloatMain(
		mixed $val,
		?float $default = null,
		bool $to_null = false
	): ?float {
		if (is_numeric($val)) {
			return (float)$val;
		}
		if ($to_null === false) {
			return (float)$default;
		}
		return $default;
	}

	/**
	 * Convert intput var to float via filter_var. If failed to so return default.
	 * If to_null is set to true allow null return
	 *
	 * @param  mixed      $val     Input variable
	 * @param  float|null $default Default value
	 * @param  bool       $to_null Convert to null (default no)
	 * @return float|null          Converted intput data to float/null
	 */
	protected static function makeFloatMain(
		mixed $val,
		?float $default = null,
		bool $to_null = false
	): ?float {
		if (
			(
				$val = filter_var(
					$val,
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION
				)
			) !== false
		) {
			return (float)$val;
		}
		if ($to_null === false) {
			return (float)$default;
		}
		return $default;
	}

	/**
	 * If input var is array return it, else return default value. If to_null is
	 * set to true, allow null return
	 *
	 * @param  mixed             $val     Input variable
	 * @param  array<mixed>|null $default Default value
	 * @param  bool              $to_null Convert to null (default no)
	 * @return array<mixed>|null          Input var or default value
	 */
	protected static function setArrayMain(
		mixed $val,
		?array $default = null,
		bool $to_null = false
	): ?array {
		if (is_array($val)) {
			return $val;
		}
		if ($to_null === false) {
			return (array)$default;
		}
		return $default;
	}

	/**
	 * If input var is bool return it, else return default value. If to_null is
	 * set to true will allow null return.
	 *
	 * @param  mixed     $val     Input variable
	 * @param  bool|null $default Default value
	 * @param  bool      $to_null Convert to null (default no)
	 * @return bool|null          Input var or default value
	 */
	protected static function setBoolMain(
		mixed $val,
		?bool $default = null,
		bool $to_null = false
	): ?bool {
		if (is_bool($val)) {
			return $val;
		}
		if ($to_null === false) {
			return (bool)$default;
		}
		return $default;
	}

	/**
	 * Convert anything to bool. If it is a string it will try to use the filter_var
	 * to convert know true/false strings.
	 * Else it uses (bool) to convert the rest
	 * If null is allowed, will return null
	 *
	 * @param  mixed     $val     Input variable
	 * @param  bool      $default Default value if to_null if false
	 * @param  bool      $to_null Convert to null (default no)
	 * @return bool|null          Converted input data to bool/ null
	 */
	protected static function makeBoolMain(
		mixed $val,
		bool $default = false,
		bool $to_null = false
	): ?bool {
		$boolvar = is_string($val) ?
				filter_var(
					$val,
					FILTER_VALIDATE_BOOLEAN,
					FILTER_NULL_ON_FAILURE
				) :
				(bool)$val;
		return $boolvar === null && !$to_null ? $default : $boolvar;
	}
}

// __END__
