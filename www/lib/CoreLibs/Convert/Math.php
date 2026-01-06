<?php

/*
 * various math related function wrappers
 */

declare(strict_types=1);

namespace CoreLibs\Convert;

class Math
{
	/**
	 * some float numbers will be rounded up even if they have no decimal entries
	 * this function fixes this by pre-rounding before calling ceil
	 *
	 * @param  float       $number    number to round
	 * @param  int|integer $precision intermediat round up decimals (default 10)
	 * @return float                  correct ceil number
	 */
	public static function fceil(float $number, int $precision = 10): float
	{
		return ceil(round($number, $precision));
	}

	/**
	 * round inside an a number, not the decimal part only
	 * eg 48767 with -2 -> 48700
	 *
	 * @param  float $number    number to round
	 * @param  int   $precision negative number for position in number (default -2)
	 * @return float            rounded number
	 */
	public static function floorp(float $number, int $precision = -2): float
	{
		// if precision is requal or larger than the number length,
		// set precision to length -1
		if (abs($precision) >= strlen((string)$number)) {
			$precision = (strlen((string)$number) - 1) * -1;
		}
		$mult = pow(10, $precision); // Can be cached in lookup table
		return floor($number * $mult) / $mult;
	}

	/**
	 * inits input to 0, if value is not numeric
	 *
	 * @param  string|int|float $number string or number to check
	 * @return float                    if not number, then returns 0, else original input
	 */
	public static function initNumeric(string|int|float $number): float
	{
		if (!is_numeric($number)) {
			return 0;
		} else {
			return (float)$number;
		}
	}

	/**
	 * calc cube root
	 *
	 * @param  float $number Number to cubic root
	 * @return float         Calculated value
	 * @throws \InvalidArgumentException if $number is negative
	 */
	public static function cbrt(float|int $number): float
	{
		$value = pow((float)$number, 1.0 / 3);
		if (is_nan($value)) {
			throw new \InvalidArgumentException('cube root from this number is not supported: ' . $number);
		}
		return $value;
	}

	/**
	 * use PHP_FLOAT_EPSILON to compare if two float numbers are matching
	 *
	 * @param  float $x
	 * @param  float $y
	 * @param  float $epsilon [default=PHP_FLOAT_EPSILON]
	 * @return bool  True equal
	 */
	public static function equalWithEpsilon(float $x, float $y, float $epsilon = PHP_FLOAT_EPSILON): bool
	{
		if (abs($x - $y) < $epsilon) {
			return true;
		}
		return false;
	}

	/**
	 * Compare two value base on direction given
	 * The default delta is PHP_FLOAT_EPSILON
	 *
	 * @param  float  $value
	 * @param  string $compare
	 * @param  float  $limit
	 * @param  float  $epsilon [default=PHP_FLOAT_EPSILON]
	 * @return bool   True on smaller/large or equal
	 */
	public static function compareWithEpsilon(
		float $value,
		string $compare,
		float $limit,
		float $epsilon = PHP_FLOAT_EPSILON
	): bool {
		switch ($compare) {
			case '<':
				if ($value < ($limit - $epsilon)) {
					return true;
				}
				break;
			case '<=':
				if ($value <= ($limit - $epsilon)) {
					return true;
				}
				break;
			case '==':
				return self::equalWithEpsilon($value, $limit, $epsilon);
			case '>':
				if ($value > ($limit + $epsilon)) {
					return true;
				}
				break;
			case '>=':
				if ($value >= ($limit + $epsilon)) {
					return true;
				}
				break;
		}
		return false;
	}

	/**
	 * This function is directly inspired by the multiplyMatrices() function in color.js
	 * form Lea Verou and Chris Lilley.
	 * (see https://github.com/LeaVerou/color.js/blob/main/src/multiply-matrices.js)
	 * From:
	 * https://github.com/matthieumastadenis/couleur/blob/3842cf51c9517e77afaa0a36ec78643a0c258e0b/src/utils/utils.php#L507
	 *
	 * It returns an array which is the product of the two number matrices passed as parameters.
	 *
	 * NOTE:
	 * if the right side (B matrix) has a missing row, this row will be fillwed with 0 instead of
	 * throwing an error:
	 * A:
	 * [
	 *   [1, 2, 3],
	 *   [4, 5, 6],
	 * ]
	 * B:
	 * [
	 *   [7, 8, 9],
	 *   [10, 11, 12],
	 * ]
	 * The B will get a third row with [0, 0, 0] added to make the multiplication work as it will be
	 * rewritten as
	 * B-rewrite:
	 * [
	 *   [7, 10, 0],
	 *   [8, 11, 12],
	 *   [0, 0, 0] <- automatically added
	 * ]
	 *
	 * The same is done for unbalanced entries, they are filled with 0
	 *
	 * @param  array<float|int|array<int|float>> $a m x n matrice
	 * @param  array<float|int|array<int|float>> $b n x p matrice
	 *
	 * @return array<float|int|array<int|float>>    m x p product
	 */
	public static function multiplyMatrices(array $a, array $b): array
	{
		$m = count($a);

		if (!is_array($a[0] ?? null)) {
			// $a is vector, convert to [[a, b, c, ...]]
			$a = [$a];
		}

		if (!is_array($b[0])) {
			// $b is vector, convert to [[a], [b], [c], ...]]
			$b = array_map(
				callback: fn ($v) => [ $v ],
				array: $b,
			);
		}

		$p = count($b[0]);

		// transpose $b:
		// so that we can multiply row by row
		$bCols = array_map(
			callback: fn ($k) => array_map(
				(fn ($i) => is_array($i) ? $i[$k] ?? 0 : 0),
				$b,
			),
			array: array_keys($b[0]),
		);

		$product = array_map(
			callback: fn ($row) => array_map(
				callback: fn ($col) => is_array($row) ?
					array_reduce(
						array: $row,
						// TODO check that v is not an array
						callback: fn ($a, $v, $i = null) => $a + $v * ( /** @phpstan-ignore-line Possible array + int */
							// if last entry missing for full copy add a 0 to it
							$col[$i ?? array_search($v, $row, true)] ?? 0
						),
						initial: 0,
					) :
					array_reduce(
						array: $col,
						// TODO check that v is not an array
						callback: fn ($a, $v) => $a + $v * $row, /** @phpstan-ignore-line Possible array + int */
						initial: 0,
					),
				array: $bCols,
			),
			array: $a,
		);

		if ($m === 1) {
			// Avoid [[a, b, c, ...]]:
			return $product[0];
		}

		if ($p === 1) {
			// Avoid [[a], [b], [c], ...]]:
			return array_map(
				callback: fn ($v) => $v[0] ?? 0,
				array: $product,
			);
		}

		return $product;
	}
}

// __END__
