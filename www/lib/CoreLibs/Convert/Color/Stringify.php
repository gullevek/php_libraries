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
