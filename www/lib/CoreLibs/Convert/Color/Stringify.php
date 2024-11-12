<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Convert color coordinate to CSS string
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color;

use CoreLibs\Convert\Color\Coordinates\RGB;
use CoreLibs\Convert\Color\Coordinates\HSL;
use CoreLibs\Convert\Color\Coordinates\HWB;
use CoreLibs\Convert\Color\Coordinates\Lab;
use CoreLibs\Convert\Color\Coordinates\LCH;

class Stringify
{
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

	/**
	 * return the CSS string including optional opacity
	 *
	 * @param  RGB|Lab|LCH|HSL|HWB $data
	 * @param  null|float|string   $opacity
	 * @return string
	 */
	public static function toCssString(RGB|Lab|LCH|HSL|HWB $data, null|float|string $opacity): string
	{
		return $data->toCssString($opacity);
	}
}

// __END__
