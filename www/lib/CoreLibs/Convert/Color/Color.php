<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/11
 * DESCRIPTION:
 * Color Coordinate and Color Space conversions
 *
 * We convert between color cooradinates and color spaces
 * as seen in the list below
 *
 * |      |         RGB           |     Oklab     |       Cie
 * |      |     | HSB |     |     |       |       |        |        |
 * |      | RGB | HSV | HSL | HWB | OkLab | OkLch | CieLab | CieLch |
 * -------+-----+-----+-----+-----+-------+-------+--------+--------+
 * RGB    |  -  |  o  |  o  |  o  |   o   |   o   |        |        |
 * HSB/HB |  o  |  -  |  o  |  o  |       |       |        |        |
 * HSL    |  o  |  o  |  -  |  o  |   o   |   o   |        |        |
 * HWB    |  o  |  o  |  o  |  -  |       |       |        |        |
 * OkLab  |  o  |     |  o  |     |   -   |       |        |        |
 * OkLch  |  o  |     |  o  |     |       |   -   |        |        |
 * CieLab |     |     |     |     |       |       |   -    |        |
 * CieLch |     |     |     |     |       |       |        |   -    |
 *
 * All color coordinates are classes
 * The data can then be converted to a CSS string
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color;

use CoreLibs\Convert\Math;
use CoreLibs\Convert\Color\Coordinates\RGB;
use CoreLibs\Convert\Color\Coordinates\HSL;
use CoreLibs\Convert\Color\Coordinates\HSB;
use CoreLibs\Convert\Color\Coordinates\HWB;
use CoreLibs\Convert\Color\Coordinates\LCH;
use CoreLibs\Convert\Color\Coordinates\Lab;
use CoreLibs\Convert\Color\Coordinates\XYZD65;

class Color
{
	// MARK: RGB <-> HSL

	/**
	 * converts a RGB (0-255) to HSL
	 * return:
	 * class with hue (0-360), saturation (0-100%) and luminance (0-100%)
	 *
	 * @param  RGB $rgb Class for rgb
	 * @return HSL      Class hue/sat/luminance
	 */
	public static function rgbToHsl(RGB $rgb): HSL
	{
		$red = $rgb->R / 255;
		$green = $rgb->G / 255;
		$blue = $rgb->B / 255;

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
			return HSL::__constructFromArray([
				0.0,
				0.0,
				$lum * 100,
			]);
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
			return HSL::__constructFromArray([
				$hue,
				$sat * 100,
				$lum * 100,
			]);
		}
	}

	/**
	 * converts an HSL to RGB
	 * if HSL value is invalid, set this value to 0
	 *
	 * @param  HSL $hsl Class with hue: 0-360 (degrees),
	 *                             saturation: 0-100,
	 *                             luminance: 0-100
	 * @return RGB      Class for rgb
	 */
	public static function hslToRgb(HSL $hsl): RGB
	{
		$hue = $hsl->H;
		$sat = $hsl->S;
		$lum = $hsl->L;
		// calc to internal convert value for hue
		$hue = (1 / 360) * $hue;
		// convert to internal 0-1 format
		$sat /= 100;
		$lum /= 100;
		// if saturation is 0
		if ($sat == 0) {
			$lum = round($lum * 255);
			return RGB::__constructFromArray([$lum, $lum, $lum]);
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

			return RGB::__constructFromArray([
				255 * $hueue($hue + (1 / 3)),
				255 * $hueue($hue),
				255 * $hueue($hue - (1 / 3)),
			]);
		}
	}

		// MARK: RGB <-> HSB

	/**
	 * rgb2hsb does not clean convert back to rgb in a round trip
	 * converts RGB to HSB/V values
	 * returns:
	 * Class with hue (0-360), sat (0-100%), brightness/value (0-100%)
	 *
	 * @param  RGB $rgb Class for rgb
	 * @return HSB      Class Hue, Sat, Brightness/Value
	 */
	public static function rgbToHsb(RGB $rgb): HSB
	{
		$red = $rgb->R / 255;
		$green = $rgb->G / 255;
		$blue = $rgb->B / 255;

		$MAX = max($red, $green, $blue);
		$MIN = min($red, $green, $blue);
		$HUE = 0;
		$DELTA = $MAX - $MIN;

		// achromatic
		if ($MAX == $MIN) {
			return HSB::__constructFromArray([0, 0, $MAX * 100]);
		}
		if ($red == $MAX) {
			$HUE = fmod(($green - $blue) / $DELTA, 6);
		} elseif ($green == $MAX) {
			$HUE = (($blue - $red) / $DELTA) + 2;
		} elseif ($blue == $MAX) {
			$HUE = (($red - $green) / $DELTA) + 4;
		}
		$HUE *= 60;
		// avoid negative
		if ($HUE < 0) {
			$HUE += 360;
		}

		return HSB::__constructFromArray([
			$HUE, // Hue
			($DELTA / $MAX) * 100, // Saturation
			$MAX * 100, // Brightness
		]);
	}

	/**
	 * hsb2rgb does not clean convert back to hsb in a round trip
	 * converts HSB/V to RGB values RGB is full INT
	 * if HSB/V value is invalid, sets this value to 0
	 *
	 * @param  HSB $hsb hue 0-360 (int),
	 *                  saturation 0-100 (int),
	 *                  brightness/value 0-100 (int)
	 * @return RGB      Class for RGB
	 */
	public static function hsbToRgb(HSB $hsb): RGB
	{
		$H = $hsb->H;
		$S = $hsb->S;
		$V = $hsb->B;
		// convert to internal 0-1 format
		$S /= 100;
		$V /= 100;

		if ($S == 0) {
			$V = $V * 255;
			return RGB::__constructFromArray([$V, $V, $V]);
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

		return RGB::__constructFromArray([
			$red * 255,
			$green * 255,
			$blue * 255,
		]);
	}

	// MARK: HSL <-> HSB

	/**
	 * Convert HSL to HSB
	 *
	 * @param  HSL $hsl
	 * @return HSB
	 */
	public static function hslToHsb(HSL $hsl): HSB
	{
		$saturation = $hsl->S / 100;
		$lightness = $hsl->L / 100;
		$value  = $lightness + $saturation * min($lightness, 1 - $lightness);
		// check for black and white
		$saturation = ($value === 0) ?
			0 :
			200 * (1 - $lightness / $value);
		return HSB::__constructFromArray([
			$hsl->H,
			$saturation,
			$value * 100,
		]);
	}

	/**
	 * Convert HSB to HSL
	 *
	 * @param  HSB $hsb
	 * @return HSL
	 */
	public static function hsbToHsl(HSB $hsb): HSL
	{
		// hsv/toHsl
		$hue = $hsb->H;
		$saturation = $hsb->S / 100;
		$value = $hsb->V / 100;

		$lightness = $value * (1 - $saturation / 2);
		// check for B/W
		$saturation = in_array($lightness, [0, 1], true) ?
			0 :
			100 * ($value - $lightness) / min($lightness, 1 - $lightness)
		;

		return HSL::__constructFromArray([
			$hue,
			$saturation,
			$lightness * 100,
		]);
	}

	// MARK: HSB <-> HWB

	/**
	 * convert HSB to HWB
	 *
	 * @param  HSB $hsb
	 * @return HWB
	 */
	public static function hsbToHwb(HSB $hsb): HWB
	{
		// hsv\Hwb
		return HWB::__constructFromArray([
			$hsb->H, // hue,
			$hsb->B * (100 - $hsb->S) / 100, // 2: brightness, 1: saturation
			100 - $hsb->B,
		]);
	}

	/**
	 * convert HWB to HSB
	 *
	 * @param  HWB $hwb
	 * @return HSB
	 */
	public static function hwbToHsb(HWB $hwb): HSB
	{
		$hue = $hwb->H;
		$whiteness = $hwb->W / 100;
		$blackness = $hwb->B / 100;

		$sum = $whiteness + $blackness;
		// for black and white
		if ($sum >= 1) {
			$saturation = 0;
			$value = $whiteness / $sum * 100;
		} else {
			$value = 1 - $blackness;
			$saturation = $value === 0 ? 0 : (1 - $whiteness / $value) * 100;
			$value *= 100;
		}

		return HSB::__constructFromArray([
			$hue,
			$saturation,
			$value,
		]);
	}

	// MARK: RGB <-> HWB

	/**
	 * Convert RGB to HWB
	 * via rgb -> hsl -> hsb -> hwb
	 *
	 * @param  RGB $rgb
	 * @return HWB
	 */
	public static function rgbToHwb(RGB $rgb): HWB
	{
		return self::hsbToHwb(
			self::hslToHsb(
				self::rgbToHsl($rgb)
			)
		);
	}

	/**
	 * Convert HWB to RGB
	 * via hwb -> hsb -> hsl -> rgb
	 *
	 * @param  HWB $hwb
	 * @return RGB
	 */
	public static function hwbToRgb(HWB $hwb): RGB
	{
		return self::hslToRgb(
			self::hsbToHsl(
				self::hwbToHsb($hwb)
			)
		);
	}

	// MARK: HSL <-> HWB

	/**
	 * Convert HSL to HWB
	 * via hsl -> hsb -> hwb
	 *
	 * @param  HSL $hsl
	 * @return HWB
	 */
	public static function hslToHwb(HSL $hsl): HWB
	{
		return self::hsbToHwb(
			self::hslToHsb(
				$hsl
			)
		);
	}

	/**
	 * Convert HWB to HSL
	 * via hwb -> hsb -> hsl
	 *
	 * @param  HWB $hwb
	 * @return HSL
	 */
	public static function hwbToHsl(HWB $hwb): HSL
	{
		return self::hsbToHsl(
			self::hwbToHsb($hwb)
		);
	}

	// MARK: OkLch <-> OkLab

	/**
	 * okLAab to okLCH
	 *
	 * @param  Lab $lab
	 * @return LCH
	 */
	public static function okLabToOkLch(Lab $lab): LCH
	{
		// okLab\toOkLch
		$a = $lab->a;
		$b = $lab->b;

		$hue = atan2($b, $a) * 180 / pi();

		return LCH::__constructFromArray([
			$lab->L,
			sqrt($a ** 2 + $b ** 2),
			$hue >= 0 ? $hue : $hue + 360,
		]);
	}

	/**
	 * okLCH to okLab
	 *
	 * @param  LCH $lch
	 * @return Lab
	 */
	public static function okLchToOkLab(LCH $lch): Lab
	{
		// oklch/toOkLab
		// oklch to oklab
		return Lab::__constructFromArray([
			$lch->L,
			$lch->C * cos($lch->H * pi() / 180), // a
			$lch->C * sin($lch->H * pi() / 180), // b
		], 'Oklab');
	}

	// MARK: xyzD65 <-> linearRGB

	/**
	 * convert linear RGB to xyz D65
	 * if rgb is not flagged linear, it will be auto converted
	 *
	 * @param  RGB  $rgb
	 * @return XYZD65
	 */
	public static function linRgbToXyzD65(RGB $rgb): XYZD65
	{
		// if not linear, convert to linear
		if (!$rgb->linear) {
			$rgb->toLinear();
		}
		return XYZD65::__constructFromArray(Math::multiplyMatrices(
			[
				[0.41239079926595934, 0.357584339383878, 0.1804807884018343],
				[0.21263900587151027, 0.715168678767756, 0.07219231536073371],
				[0.01933081871559182, 0.11919477979462598, 0.9505321522496607],
			],
			$rgb->returnAsArray()
		));
	}

	/**
	 * Convert xyz D65 to linear RGB
	 *
	 * @param  XYZD65 $xyzD65
	 * @return RGB
	 */
	public static function xyzD65ToLinRgb(XYZD65 $xyzD65): RGB
	{
		// xyz D65 to linrgb
		return RGB::__constructFromArray(Math::multiplyMatrices(
			a : [
				[  3.2409699419045226,  -1.537383177570094,   -0.4986107602930034  ],
				[ -0.9692436362808796,   1.8759675015077202,   0.04155505740717559 ],
				[  0.05563007969699366, -0.20397695888897652,  1.0569715142428786  ],
			],
			b : $xyzD65->returnAsArray()
		), linear: true);
	}

	// MARK: xyzD65 <-> OkLab

	/**
	 * xyz D65 to OkLab
	 *
	 * @param  XYZD65 $xyzD65
	 * @return Lab
	 */
	public static function xyzD65ToOkLab(XYZD65 $xyzD65): Lab
	{
		return Lab::__constructFromArray(Math::multiplyMatrices(
			[
				[0.2104542553, 0.7936177850, -0.0040720468],
				[1.9779984951, -2.4285922050, 0.4505937099],
				[0.0259040371, 0.7827717662, -0.8086757660],
			],
			array_map(
				callback: fn ($v) => pow($v, 1 / 3),
				array: Math::multiplyMatrices(
					a: [
						[0.8190224432164319, 0.3619062562801221, -0.12887378261216414],
						[0.0329836671980271, 0.9292868468965546, 0.03614466816999844],
						[0.048177199566046255, 0.26423952494422764, 0.6335478258136937],
					],
					b: $xyzD65->returnAsArray(),
				),
			)
		), 'Oklab');
	}

	/**
	 * xyz D65 to OkLab
	 *
	 * @param  Lab    $lab
	 * @return XYZD65
	 */
	public static function okLabToXyzD65(Lab $lab): XYZD65
	{
		return XYZD65::__constructFromArray(Math::multiplyMatrices(
			a: [
					[1.2268798733741557, -0.5578149965554813, 0.28139105017721583],
					[-0.04057576262431372, 1.1122868293970594, -0.07171106666151701],
					[-0.07637294974672142, -0.4214933239627914, 1.5869240244272418],
			],
			b: array_map(
				callback: fn ($v) => is_numeric($v) ? $v ** 3 : 0,
				array: Math::multiplyMatrices(
					a: [
							[0.99999999845051981432, 0.39633779217376785678, 0.21580375806075880339],
							[1.0000000088817607767, -0.1055613423236563494, -0.063854174771705903402],
							[1.0000000546724109177, -0.089484182094965759684, -1.2914855378640917399],
					],
					// Divide $lightness by 100 to convert from CSS OkLab
					b: $lab->returnAsArray(),
				),
			),
		));
	}

	// MARK: rgb <-> oklab

	/**
	 * Undocumented function
	 *
	 * @param  RGB $rgb
	 * @return Lab
	 */
	public static function rgbToOkLab(RGB $rgb): Lab
	{
		return self::xyzD65ToOkLab(
			self::linRgbToXyzD65($rgb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return RGB
	 */
	public static function okLabToRgb(Lab $lab): RGB
	{
		return self::xyzD65ToLinRgb(
			self::okLabToXyzD65($lab)
		)->fromLinear();
	}

	// MARK: rgb <-> oklch

	/**
	 * convert rgb to OkLch
	 * via rgb -> linear rgb -> xyz D65 -> OkLab -> OkLch
	 *
	 * @param  RGB $rbh
	 * @return LCH
	 */
	public static function rgbToOkLch(RGB $rgb): LCH
	{
		return self::okLabToOkLch(
			self::rgbToOkLab($rgb)
		);
	}

	/**
	 * Convert OkLch to rgb
	 * via OkLab -> OkLch -> xyz D65 -> linear rgb -> rgb
	 *
	 * @param  LCH $lch
	 * @return RGB
	 */
	public static function okLchToRgb(LCH $lch): RGB
	{
		return self::okLabToRgb(
			self::okLchToOkLab($lch)
		);
	}

	// MARK: HSL <-> OKLab

	/**
	 * Undocumented function
	 *
	 * @param  HSL $hsl
	 * @return Lab
	 */
	public static function hslToOkLab(HSL $hsl): Lab
	{
		return self::rgbToOkLab(
			self::hslToRgb($hsl)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return HSL
	 */
	public static function okLabToHsl(Lab $lab): HSL
	{
		return self::rgbToHsl(
			self::okLabToRgb($lab)
		);
	}

	// MARK: HSL <-> OKLCH

	/**
	 * Undocumented function
	 *
	 * @param  HSL $hsl
	 * @return LCH
	 */
	public static function hslToOkLch(HSL $hsl): LCH
	{
		return self::rgbToOkLch(
			self::hslToRgb($hsl)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  LCH $lch
	 * @return HSL
	 */
	public static function okLchToHsl(LCH $lch): HSL
	{
		return self::rgbToHsl(
			self::okLchToRgb($lch)
		);
	}

	// MARK: HSB <-> OKLab

	/**
	 * Undocumented function
	 *
	 * @param  HSB $hsb
	 * @return Lab
	 */
	public static function hsbToOkLab(HSB $hsb): Lab
	{
		return self::rgbToOkLab(
			self::hsbToRgb($hsb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return HSB
	 */
	public static function okLabToHsb(Lab $lab): HSB
	{
		return self::rgbToHsb(
			self::okLabToRgb($lab)
		);
	}

	// MARK: HSB <-> OKLCH

	/**
	 * Undocumented function
	 *
	 * @param  HSB $hsb
	 * @return LCH
	 */
	public static function hsbToOkLch(HSB $hsb): LCH
	{
		return self::rgbToOkLch(
			self::hsbToRgb($hsb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  LCH $lch
	 * @return HSB
	 */
	public static function okLchToHsb(LCH $lch): HSB
	{
		return self::rgbToHsb(
			self::okLchToRgb($lch)
		);
	}

	// MARK: HWB <-> OKLab

	/**
	 * Undocumented function
	 *
	 * @param  HWB $hwb
	 * @return Lab
	 */
	public function hwbToOkLab(HWB $hwb): Lab
	{
		return self::rgbToOkLab(
			self::hwbToRgb($hwb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return HWB
	 */
	public function okLabToHwb(Lab $lab): HWB
	{
		return self::rgbToHwb(
			self::okLabToRgb($lab)
		);
	}

	// MARK: HWB <-> OKLCH

	/**
	 * Undocumented function
	 *
	 * @param  HWB $hwb
	 * @return LCH
	 */
	public function hwbToOkLch(HWB $hwb): LCH
	{
		return self::rgbToOkLch(
			self::hwbToRgb($hwb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  LCH $lch
	 * @return HWB
	 */
	public function okLchToHwb(LCH $lch): HWB
	{
		return self::rgbToHwb(
			self::okLchToRgb($lch)
		);
	}
}
