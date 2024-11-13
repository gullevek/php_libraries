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
	 */
	public static function cbrt(float|int $number): float
	{
		return pow((float)$number, 1.0 / 3);
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
	 * @param  array<array<int|float>> $a m x n matrice
	 * @param  array<array<int|float>> $b n x p matrice
	 *
	 * @return array<array<int|float>>    m x p product
	 */
	public static function multiplyMatrices(array $a, array $b): array
	{
		$m = count($a);

		if (!is_array($a[0] ?? null)) {
			// $a is vector, convert to [[a, b, c, ...]]
			$a = [ $a ];
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
		$bCols = array_map(
			callback: fn ($k) => \array_map(
				(fn ($i) => $i[$k]),
				$b,
			),
			array: array_keys($b[0]),
		);

		$product = array_map(
			callback: fn ($row) => array_map(
				callback: fn ($col) => is_array($row) ?
					array_reduce(
						array: $row,
						callback: fn ($a, $v, $i = null) => $a + $v * (
							$col[$i ?? array_search($v, $row)] ?? 0
						),
						initial: 0,
					) :
					array_reduce(
						array: $col,
						callback: fn ($a, $v) => $a + $v * $row,
						initial: 0,
					),
				array: $bCols,
			),
			array: $a,
		);

		if ($m === 1) {
			// Avoid [[a, b, c, ...]]:
			$product = $product[0];
		}

		if ($p === 1) {
			// Avoid [[a], [b], [c], ...]]:
			return array_map(
				callback: fn ($v) => $v[0],
				array: $product,
			);
		}

		return $product;
	}
}

// __END__
