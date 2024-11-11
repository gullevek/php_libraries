<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/7
 * DESCRIPTION:
 * oklab conversions
 * rgb -> oklab
 * oklab -> rgb
 * rgb -> okhsl
 * okshl -> rgb
 * rgb -> okhsv
 * okhsv -> rgb
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color;

class OkLab
{
	/**
	 * lines sRGB to oklab
	 *
	 * @param  int   $red
	 * @param  int   $green
	 * @param  int   $blue
	 * @return array<float>
	 */
	public static function srgb2okLab(int $red, int $green, int $blue): array
	{
		$l = (float)0.4122214708 * (float)$red +
			(float)0.5363325363 * (float)$green +
			(float)0.0514459929 * (float)$blue;
		$m = (float)0.2119034982 * (float)$red +
			(float)0.6806995451 * (float)$green +
			(float)0.1073969566 * (float)$blue;
		$s = (float)0.0883024619 * (float)$red +
			(float)0.2817188376 * (float)$green +
			(float)0.6299787005 * (float)$blue;

		// cbrtf = 3 root (val)
		$l_ = pow($l, 1.0 / 3);
		$m_ = pow($m, 1.0 / 3);
		$s_ = pow($s, 1.0 / 3);

		return [
			(float)0.2104542553 * $l_ + (float)0.7936177850 * $m_ - (float)0.0040720468 * $s_,
			(float)1.9779984951 * $l_ - (float)2.4285922050 * $m_ + (float)0.4505937099 * $s_,
			(float)0.0259040371 * $l_ + (float)0.7827717662 * $m_ - (float)0.8086757660 * $s_,
		];
	}

	/**
	 * convert okLab to linear sRGB
	 *
	 * @param  float $L
	 * @param  float $a
	 * @param  float $b
	 * @return array<int>
	 */
	public static function okLab2srgb(float $L, float $a, float $b): array
	{
		$l_ = $L + (float)0.3963377774 * $a + (float)0.2158037573 * $b;
		$m_ = $L - (float)0.1055613458 * $a - (float)0.0638541728 * $b;
		$s_ = $L - (float)0.0894841775 * $a - (float)1.2914855480 * $b;

		$l = $l_ * $l_ * $l_;
		$m = $m_ * $m_ * $m_;
		$s = $s_ * $s_ * $s_;

		return [
			(int)round(+(float)4.0767416621 * $l - (float)3.3077115913 * $m + (float)0.2309699292 * $s),
			(int)round(-(float)1.2684380046 * $l + (float)2.6097574011 * $m - (float)0.3413193965 * $s),
			(int)round(-(float)0.0041960863 * $l - (float)0.7034186147 * $m + (float)1.7076147010 * $s),
		];
	}
}

// __END__
