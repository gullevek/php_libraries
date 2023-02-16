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
	public static function initNumeric($number): float
	{
		if (!is_numeric($number)) {
			return 0;
		} else {
			return (float)$number;
		}
	}
}

// __END__
