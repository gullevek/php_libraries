<?php

/*
 * Convert color spaces
 * rgb to hex
 * hex to rgb
 * rgb to hsb
 * hsb to rgb
 * rgb to hsl
 * hsl to rgb
*/

// TODO: use oklab as base for converting colors
// https://bottosson.github.io/posts/oklab/

declare(strict_types=1);

namespace CoreLibs\Convert;

use CoreLibs\Convert\Color\Color;
use CoreLibs\Convert\Color\Coordinates;

class Colors
{
	/**
	 * converts the rgb values from int data to the valid rgb html hex string
	 * optional can turn of leading #
	 * if one value is invalid, will return false
	 *
	 * @param  int    $red        red 0-255
	 * @param  int    $green      green 0-255
	 * @param  int    $blue       blue 0-255
	 * @param  bool   $hex_prefix default true, prefix with "#"
	 * @return string             rgb in hex values with leading # if set,
	 * @throws \LengthException If any argument is not in the range of 0~255
	 * @deprecated v9.19.0 use: new Coordinates\RGB([$red, $green, $blue]))->returnAsHex(true/false for #)
	 */
	public static function rgb2hex(
		int $red,
		int $green,
		int $blue,
		bool $hex_prefix = true
	): string {
		return (new Coordinates\RGB([$red, $green, $blue]))->returnAsHex($hex_prefix);
	}

	/**
	 * converts a hex RGB color to the int numbers
	 *
	 * @param  string $hex_string           RGB hexstring
	 * @param  bool   $return_as_string flag to return as string
	 * @param  string $seperator        string seperator: default: ","
	 * @return string|array<string,float|int> array with RGB
	 *                                        or a string with the seperator
	 * @throws \InvalidArgumentException if hex string is empty
	 * @throws \UnexpectedValueException if the hex string value is not valid
	 * @deprecated v9.19.0] use: new Coordinates\RGB($hex_string) (build string/array from return data)
	 */
	public static function hex2rgb(
		string $hex_string,
		bool $return_as_string = false,
		string $seperator = ','
	): string|array {
		$rgbArray = [];
		// rewrite to previous r/g/b key output
		foreach ((new Coordinates\RGB($hex_string))->returnAsArray() as $p => $el) {
			$k = '';
			switch ($p) {
				case 0:
					$k = 'r';
					break;
				case 1:
					$k = 'g';
					break;
				case 2:
					$k = 'b';
					break;
			}
			$rgbArray[$k] = (int)round($el);
		}
		// returns the rgb string or the associative array
		return $return_as_string ? implode($seperator, $rgbArray) : $rgbArray;
	}

	/**
	 * rgb2hsb does not clean convert back to rgb in a round trip
	 * converts RGB to HSB/V values
	 * returns:
	 * array with hue (0-360), sat (0-100%), brightness/value (0-100%)
	 *
	 * @param  int $red         red 0-255
	 * @param  int $green       green 0-255
	 * @param  int $blue        blue 0-255
	 * @return array<int|float> Hue, Sat, Brightness/Value
	 * @throws \LengthException If any argument is not in the range of 0~255
	 * @deprecated v9.19.0 use: Color::rgbToHsb(...)->returnAsArray() will return float unrounded
	 */
	public static function rgb2hsb(int $red, int $green, int $blue): array
	{
		return array_map(
			fn ($v) => (int)round($v),
			Color::rgbToHsb(
				new Coordinates\RGB([$red, $green, $blue])
			)->returnAsArray()
		);
	}

	/**
	 * hsb2rgb does not clean convert back to hsb in a round trip
	 * converts HSB/V to RGB values RGB is full INT
	 * if HSB/V value is invalid, sets this value to 0
	 *
	 * @param  float $H   hue 0-360 (int)
	 * @param  float $S   saturation 0-100 (int)
	 * @param  float $V   brightness/value 0-100 (int)
	 * @return array<int> 0 red/1 green/2 blue array as 0-255
	 * @throws \LengthException If any argument is not in the valid range
	 * @deprecated v9.19.0 use: Color::hsbToRgb(...)->returnAsArray() will return float unrounded
	 */
	public static function hsb2rgb(float $H, float $S, float $V): array
	{
		return array_map(
			fn ($v) => (int)round($v),
			Color::hsbToRgb(
				new Coordinates\HSB([$H, $S, $V])
			)->returnAsArray()
		);
	}

	/**
	 * converts a RGB (0-255) to HSL
	 * return:
	 * array with hue (0-360), saturation (0-100%) and luminance (0-100%)
	 *
	 * @param  int $red     red 0-255
	 * @param  int $green   green 0-255
	 * @param  int $blue    blue 0-255
	 * @return array<float> hue/sat/luminance
	 * @throws \LengthException If any argument is not in the range of 0~255
	 * @deprecated v9.19.0 use: Color::rgbToHsl(...)->returnAsArray() will return float unrounded
	 */
	public static function rgb2hsl(int $red, int $green, int $blue): array
	{
		return array_map(
			fn ($v) => round($v, 1),
			Color::rgbToHsl(
				new Coordinates\RGB([$red, $green, $blue])
			)->returnAsArray()
		);
	}

	/**
	 * converts an HSL to RGB
	 * if HSL value is invalid, set this value to 0
	 *
	 * @param  float $hue           hue: 0-360 (degrees)
	 * @param  float $sat           saturation: 0-100
	 * @param  float $lum           luminance: 0-100
	 * @return array<int,float|int> red/blue/green 0-255 each
	 * @throws \LengthException If any argument is not in the valid range
	 * @deprecated v9.19.0 use: Color::hslToRgb(...)->returnAsArray() will return float unrounded
	 */
	public static function hsl2rgb(float $hue, float $sat, float $lum): array
	{
		return array_map(
			fn ($v) => round($v),
			Color::hslToRgb(
				new Coordinates\HSL([$hue, $sat, $lum])
			)->returnAsArray()
		);
	}
}

// __END__
