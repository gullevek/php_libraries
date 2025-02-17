<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/14
 * DESCRIPTION:
 * Utils for color
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color;

use CoreLibs\Convert\Math;

class Utils
{
	/** @var float deviation allowed for valid data checks, small */
	public const EPSILON_SMALL  = 0.000000000001;
	/** @var float deviation allowed for valid data checks, medium */
	public const EPSILON_MEDIUM = 0.0000001;
	/** @var float deviation allowed for valid data checks, big */
	public const ESPILON_BIG    = 0.0001;

	public static function compare(float $lower, float $value, float $upper, float $epslion): bool
	{
		if (
			Math::compareWithEpsilon($value, '<', $lower, $epslion) ||
			Math::compareWithEpsilon($value, '>', $upper, $epslion)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Build the opactiy sub string part and return it
	 *
	 * @param  null|float|string|null $opacity
	 * @return string
	 */
	public static function setOpacity(null|float|string $opacity = null): string
	{
		// set opacity, either a string or float
		if (is_string($opacity)) {
			$opacity = ' / ' . $opacity;
		} elseif ($opacity !== null) {
			$opacity = ' / ' . $opacity;
		} else {
			$opacity = '';
		}
		return $opacity;
	}
}

// __END__
