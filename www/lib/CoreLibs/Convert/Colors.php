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
	 * @return string|bool        rgb in hex values with leading # if set,
	 *                            false for invalid color
	 */
	public static function rgb2hex(int $red, int $green, int $blue, bool $hex_prefix = true)
	{
		$hex_color = '';
		if ($hex_prefix === true) {
			$hex_color = '#';
		}
		foreach (['red', 'green', 'blue'] as $color) {
			// if not valid, abort
			if ($$color < 0 || $$color > 255) {
				return false;
			}
			// pad left with 0
			$hex_color .= str_pad(dechex($$color), 2, '0', STR_PAD_LEFT);
		}
		return $hex_color;
	}

	/**
	 * converts a hex RGB color to the int numbers
	 *
	 * @param  string $hexStr           RGB hexstring
	 * @param  bool   $return_as_string flag to return as string
	 * @param  string $seperator        string seperator: default: ","
	 * @return string|array<string,float|int>|bool false on error or array with RGB
	 *                                             or a string with the seperator
	 */
	public static function hex2rgb(
		string $hexStr,
		bool $return_as_string = false,
		string $seperator = ','
	) {
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		if (!is_string($hexStr)) {
			return false;
		}
		$rgbArray = [];
		if (strlen($hexStr) == 6) {
			// If a proper hex code, convert using bitwise operation.
			// No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['r'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['g'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['b'] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) {
			// If shorthand notation, need some string manipulations
			$rgbArray['r'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['g'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['b'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} else {
			// Invalid hex color code
			return false;
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
	 * @param  int $red              red 0-255
	 * @param  int $green            green 0-255
	 * @param  int $blue             blue 0-255
	 * @return array<int|float>|bool Hue, Sat, Brightness/Value
	 *                               false for input value error
	 */
	public static function rgb2hsb(int $red, int $green, int $blue)
	{
		// check that rgb is from 0 to 255
		foreach (['red', 'green', 'blue'] as $c) {
			if ($$c < 0 || $$c > 255) {
				return false;
			}
			$$c = $$c / 255;
		}

		$MAX = max($red, $green, $blue);
		$MIN = min($red, $green, $blue);
		$HUE = 0;

		if ($MAX == $MIN) {
			return [0, 0, round($MAX * 100)];
		}
		if ($red == $MAX) {
			$HUE = ($green - $blue) / ($MAX - $MIN);
		} elseif ($green == $MAX) {
			$HUE = 2 + (($blue - $red) / ($MAX - $MIN));
		} elseif ($blue == $MAX) {
			$HUE = 4 + (($red - $green) / ($MAX - $MIN));
		}
		$HUE *= 60;
		if ($HUE < 0) {
			$HUE += 360;
		}

		return [
			(int)round($HUE),
			(int)round((($MAX - $MIN) / $MAX) * 100),
			(int)round($MAX * 100)
		];
	}

	/**
	 * hsb2rgb does not clean convert back to hsb in a round trip
	 * converts HSB/V to RGB values RGB is full INT
	 * if HSB/V value is invalid, sets this value to 0
	 *
	 * @param  float $H          hue 0-360 (int)
	 * @param  float $S          saturation 0-100 (int)
	 * @param  float $V          brightness/value 0-100 (int)
	 * @return array<int>|bool 0 red/1 green/2 blue array as 0-255
	 *                         false for input value error
	 */
	public static function hsb2rgb(float $H, float $S, float $V)
	{
		// check that H is 0 to 359, 360 = 0
		// and S and V are 0 to 1
		if ($H == 360) {
			$H = 0;
		}
		if ($H < 0 || $H > 359) {
			return false;
		}
		if ($S < 0 || $S > 100) {
			return false;
		}
		if ($V < 0 || $V > 100) {
			return false;
		}
		// convert to internal 0-1 format
		$S /= 100;
		$V /= 100;

		if ($S == 0) {
			$V = (int)round($V * 255);
			return [$V, $V, $V];
		}

		$Hi = floor($H / 60);
		$f = ($H / 60) - $Hi;
		$p = $V * (1 - $S);
		$q = $V * (1 - ($S * $f));
		$t = $V * (1 - ($S * (1 - $f)));

		switch ($Hi) {
			case 0:
				$red = $V;
				$green = $t;
				$blue = $p;
				break;
			case 1:
				$red = $q;
				$green = $V;
				$blue = $p;
				break;
			case 2:
				$red = $p;
				$green = $V;
				$blue = $t;
				break;
			case 3:
				$red = $p;
				$green = $q;
				$blue = $V;
				break;
			case 4:
				$red = $t;
				$green = $p;
				$blue = $V;
				break;
			case 5:
				$red = $V;
				$green = $p;
				$blue = $q;
				break;
			default:
				$red = 0;
				$green = 0;
				$blue = 0;
		}

		return [
			(int)round($red * 255),
			(int)round($green * 255),
			(int)round($blue * 255)
		];
	}

	/**
	 * converts a RGB (0-255) to HSL
	 * return:
	 * array with hue (0-360), saturation (0-100%) and luminance (0-100%)
	 *
	 * @param  int $red          red 0-255
	 * @param  int $green        green 0-255
	 * @param  int $blue         blue 0-255
	 * @return array<float>|bool hue/sat/luminance
	 *                           false for input value error
	 */
	public static function rgb2hsl(int $red, int $green, int $blue)
	{
		// check that rgb is from 0 to 255
		foreach (['red', 'green', 'blue'] as $c) {
			if ($$c < 0 || $$c > 255) {
				return false;
			}
			$$c = $$c / 255;
		}

		$min = min($red, $green, $blue);
		$max = max($red, $green, $blue);
		$chroma = $max - $min;
		$sat = 0;
		$hue = 0;
		// luminance
		$lum = ($max + $min) / 2;

		// achromatic
		if ($chroma == 0) {
			// H, S, L
			return [0.0, 0.0, round($lum * 100, 1)];
		} else {
			$sat = $chroma / (1 - abs(2 * $lum - 1));
			if ($max == $red) {
				$hue = fmod((($green - $blue) / $chroma), 6);
				if ($hue < 0) {
					$hue = (6 - fmod(abs($hue), 6));
				}
			} elseif ($max == $green) {
				$hue = ($blue - $red) / $chroma + 2;
			} elseif ($max == $blue) {
				$hue = ($red - $green) / $chroma + 4;
			}
			$hue = $hue * 60;
			// $sat = 1 - abs(2 * $lum - 1);
			return [
				round($hue, 1),
				round($sat * 100, 1),
				round($lum * 100, 1)
			];
		}
	}

	/**
	 * converts an HSL to RGB
	 * if HSL value is invalid, set this value to 0
	 *
	 * @param  float $hue                hue: 0-360 (degrees)
	 * @param  float $sat                saturation: 0-100
	 * @param  float $lum                luminance: 0-100
	 * @return array<int,float|int>|bool red/blue/green 0-255 each
	 */
	public static function hsl2rgb(float $hue, float $sat, float $lum)
	{
		if (!is_numeric($hue)) {
			return false;
		}
		if ($hue == 360) {
			$hue = 0;
		}
		if ($hue < 0 || $hue > 359) {
			return false;
		}
		if ($sat < 0 || $sat > 100) {
			return false;
		}
		if ($lum < 0 || $lum > 100) {
			return false;
		}
		// calc to internal convert value for hue
		$hue = (1 / 360) * $hue;
		// convert to internal 0-1 format
		$sat /= 100;
		$lum /= 100;
		// if saturation is 0
		if ($sat == 0) {
			$lum = (int)round($lum * 255);
			return [$lum, $lum, $lum];
		} else {
			$m2 = $lum < 0.5 ? $lum * ($sat + 1) : ($lum + $sat) - ($lum * $sat);
			$m1 = $lum * 2 - $m2;
			$hueue = function ($base) use ($m1, $m2) {
				// base = hue, hue > 360 (1) - 360 (1), else < 0 + 360 (1)
				$base = $base < 0 ? $base + 1 : ($base > 1 ? $base - 1 : $base);
				// 6: 60, 2: 180, 3: 240
				// 2/3 = 240
				// 1/3 = 120 (all from 360)
				if ($base * 6 < 1) {
					return $m1 + ($m2 - $m1) * $base * 6;
				}
				if ($base * 2 < 1) {
					return $m2;
				}
				if ($base * 3 < 2) {
					return $m1 + ($m2 - $m1) * ((2 / 3) - $base) * 6;
				}
				return $m1;
			};

			return [
				(int)round(255 * $hueue($hue + (1 / 3))),
				(int)round(255 * $hueue($hue)),
				(int)round(255 * $hueue($hue - (1 / 3)))
			];
		}
	}
}

// __END__
