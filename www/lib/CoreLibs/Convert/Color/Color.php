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
 * |       |         RGB           |     Oklab     |     CieLab      |
 * |       |     | HSB |     |     |       |       |        |        |
 * |       | RGB | HSV | HSL | HWB | OkLab | OkLch | CieLab | CieLch |
 * --------+-----+-----+-----+-----+-------+-------+--------+--------+
 * RGB     |  -  |  o  |  o  |  o  |   o   |   o   |   o    |   o    |
 * HSB/HSV |  o  |  -  |  o  |  o  |   o   |   o   |   o    |   o    |
 * HSL     |  o  |  o  |  -  |  o  |   o   |   o   |   o    |   o    |
 * HWB     |  o  |  o  |  o  |  -  |   o   |   o   |   o    |   o    |
 * OkLab   |  o  |  o  |  o  |  o  |   -   |   o   |   o    |   o    |
 * OkLch   |  o  |  o  |  o  |  o  |   o   |   -   |   o    |   o    |
 * CieLab  |  o  |  o  |  o  |  o  |   o   |   o   |   -    |   o    |
 * CieLch  |  o  |  o  |  o  |  o  |   o   |   o   |   o    |   -    |
 *
 * All color coordinates are classes
 * The data can then be converted to a CSS string
 *
 * CieXyz Class
 * Not theat xyz (CIEXYZ) does not have its own conversion as it is not used in web
 * applications
 * Also XYZ has two different coordinate systems for the D50 an D65 whitepoint
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color;

use CoreLibs\Convert\Color\Coordinates\RGB;
use CoreLibs\Convert\Color\Coordinates\HSL;
use CoreLibs\Convert\Color\Coordinates\HSB;
use CoreLibs\Convert\Color\Coordinates\HWB;
use CoreLibs\Convert\Color\Coordinates\LCH;
use CoreLibs\Convert\Color\Coordinates\Lab;

class Color
{
	// MARK: general lab/lch

	/**
	 * general Lab to LCH convert
	 *
	 * @param  Lab   $lab
	 * @return array{0:float,1:float,2:float} LCH values as array
	 */
	private static function __labToLch(Lab $lab): array
	{
		// cieLab to cieLch
		$a = $lab->a;
		$b = $lab->b;

		$hue = atan2($b, $a) * 180 / pi();

		return [
			$lab->L,
			sqrt($a ** 2 + $b ** 2),
			$hue >= 0 ? $hue : $hue + 360,
		];
	}

	/**
	 * general LCH to Lab convert
	 *
	 * @param  LCH   $lch
	 * @return array{0:float,1:float,2:float} Lab values as array
	 */
	private static function __lchToLab(LCH $lch): array
	{
		return [
			$lch->L,
			$lch->C * cos($lch->H * pi() / 180), // a
			$lch->C * sin($lch->H * pi() / 180), // b
		];
	}

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

	// MARK: LAB <-> LCH

	// toLch

	/**
	 * CIE Lab to LCH
	 *
	 * @param  Lab $lab
	 * @return LCH
	 */
	public static function labToLch(Lab $lab): LCH
	{
		// cieLab to cieLch
		return LCH::__constructFromArray(self::__labToLch($lab), colorspace: 'CIELab');
	}

	/**
	 * Convert CIE LCH to Lab
	 *
	 * @param  LCH $lch
	 * @return Lab
	 */
	public static function lchToLab(LCH $lch): Lab
	{
		return Lab::__constructFromArray(self::__lchToLab($lch), colorspace: 'CIELab');
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
		return LCH::__constructFromArray(self::__labToLch($lab), colorspace: 'OkLab');
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
		return Lab::__constructFromArray(self::__lchToLab($lch), colorspace: 'OkLab');
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
		return CieXyz::rgbViaXyzD65ToOkLab($rgb);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return RGB
	 */
	public static function okLabToRgb(Lab $lab): RGB
	{
		return CieXyz::okLabViaXyzD65ToRgb($lab);
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
	public static function hwbToOkLab(HWB $hwb): Lab
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
	public static function okLabToHwb(Lab $lab): HWB
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
	public static function hwbToOkLch(HWB $hwb): LCH
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
	public static function okLchToHwb(LCH $lch): HWB
	{
		return self::rgbToHwb(
			self::okLchToRgb($lch)
		);
	}

	// MARK: RGB <-> Lab (Cie)

	/**
	 * RGB to Lab
	 * via RGB -> linRgb -> xyz D65 -> xyz D50 -> Lab
	 *
	 * @param  RGB $rgb
	 * @return Lab
	 */
	public static function rgbToLab(RGB $rgb): Lab
	{
		return CieXyz::rgbViaXyzD65ViaXyzD50ToLab($rgb);
		/* return CieXyz::xyzD50ToLab(
			CieXyz::xyzD65ToXyzD50(
				CieXyz::linRgbToXyzD65($rgb)
			)
		); */
	}

	/**
	 * Lab to RGB
	 * via Lab -> xyz D50 -> xyz D65 -> lin RGB -> RGB
	 *
	 * @param  Lab $lab
	 * @return RGB
	 */
	public static function labToRgb(Lab $lab): RGB
	{
		return CieXyz::labViaXyzD50ViaXyzD65ToRgb($lab);
		/* return CieXyz::xyzD65ToLinRgb(
			CieXyz::xyzD50ToXyxD65(
				CieXyz::labToXyzD50($lab)
			)
		)->fromLinear(); */
	}

	// MARK: RGB <-> Lch (Cie)

	/**
	 * Convert RGB to LCH (Cie)
	 * via RGB to Lab
	 *
	 * @param  RGB $rgb
	 * @return LCH
	 */
	public static function rgbToLch(RGB $rgb): LCH
	{
		// return self::rgbToL
		return self::labToLch(
			self::rgbToLab($rgb)
		);
	}

	/**
	 * Convert LCH (Cie) to RGB
	 * via Lab to RGB
	 *
	 * @param  LCH $lch
	 * @return RGB
	 */
	public static function lchToRgb(LCH $lch): RGB
	{
		return self::labToRgb(
			self::lchToLab($lch)
		);
	}

	// MARK: HSL <-> Lab (CIE)

	/**
	 * HSL to Lab (CIE)
	 *
	 * @param  HSL $hsl
	 * @return Lab
	 */
	public static function hslToLab(HSL $hsl): Lab
	{
		return self::rgbToLab(
			self::hslToRgb($hsl)
		);
	}

	/**
	 * Lab (CIE) to HSL
	 *
	 * @param  Lab $lab
	 * @return HSL
	 */
	public static function labToHsl(Lab $lab): HSL
	{
		return self::rgbToHsl(
			self::labToRgb($lab)
		);
	}

	// MARK: HSL <-> Lch (CIE)

	/**
	 * Undocumented function
	 *
	 * @param  HSL $hsl
	 * @return LCH
	 */
	public static function hslToLch(HSL $hsl): LCH
	{
		return self::rgbToLch(
			self::hslToRgb($hsl)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  LCH $lch
	 * @return HSL
	 */
	public static function lchToHsl(LCH $lch): HSL
	{
		return self::rgbToHsl(
			self::lchToRgb($lch)
		);
	}

	// MARK: HSB <-> Lab (CIE)

	/**
	 * Undocumented function
	 *
	 * @param  HSB $hsb
	 * @return Lab
	 */
	public static function hsbToLab(HSB $hsb): Lab
	{
		return self::rgbToLab(
			self::hsbToRgb($hsb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return HSB
	 */
	public static function labToHsb(Lab $lab): HSB
	{
		return self::rgbToHsb(
			self::labToRgb($lab)
		);
	}

	// MARK: HSB <-> Lch (CIE)

	/**
	 * Undocumented function
	 *
	 * @param  HSB $hsb
	 * @return LCH
	 */
	public static function hsbToLch(HSB $hsb): LCH
	{
		return self::rgbToLch(
			self::hsbToRgb($hsb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  LCH $lch
	 * @return HSB
	 */
	public static function lchToHsb(LCH $lch): HSB
	{
		return self::rgbToHsb(
			self::lchToRgb($lch)
		);
	}

	// MARK: HWB <-> Lab (CIE)

	/**
	 * Undocumented function
	 *
	 * @param  HWB $hwb
	 * @return Lab
	 */
	public static function hwbToLab(HWB $hwb): Lab
	{
		return self::rgbToLab(
			self::hwbToRgb($hwb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return HWB
	 */
	public static function labToHwb(Lab $lab): HWB
	{
		return self::rgbToHwb(
			self::labToRgb($lab)
		);
	}

	// MARK: HWB <-> Lch (CIE)

	/**
	 * Undocumented function
	 *
	 * @param  HWB $hwb
	 * @return Lch
	 */
	public static function hwbToLch(HWB $hwb): Lch
	{
		return self::rgbToLch(
			self::hwbToRgb($hwb)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  LCH $lch
	 * @return HWB
	 */
	public static function lchToHweb(LCH $lch): HWB
	{
		return self::rgbToHwb(
			self::lchToRgb($lch)
		);
	}

	// MARK: Lab (Cie) <-> OkLab

	/**
	 * okLab to Lab (Cie)
	 *
	 * @param  Lab $lab
	 * @return Lab
	 */
	public static function okLabToLab(Lab $lab): Lab
	{
		return CieXyz::okLabViaXyzD65ViaXyzD50ToLab($lab);
		/* return CieXyz::xyzD50ToLab(
			CieXyz::xyzD65ToXyzD50(
				CieXyz::okLabToXyzD65($lab)
			)
		); */
	}

	/**
	 * Lab (Cie) to okLab
	 *
	 * @param  Lab $lab
	 * @return Lab
	 */
	public static function labToOkLab(Lab $lab): Lab
	{
		return CieXyz::labViaXyzD50ViaXyzD65ToOkLab($lab);
		/* return CieXyz::xyzD65ToOkLab(
			CieXyz::xyzD50ToXyxD65(
				CieXyz::labToXyzD50($lab)
			)
		); */
	}

	// MARK: Lab (Cie) <-> Oklch

	/**
	 * OkLch to Lab (CIE)
	 *
	 * @param  LCH $lch
	 * @return Lab
	 */
	public static function okLchToLab(LCH $lch): Lab
	{
		return self::okLabToLab(
			self::okLchToOkLab($lch)
		);
	}

	/**
	 * Lab (CIE) to OkLch
	 *
	 * @param  Lab $lab
	 * @return LCH
	 */
	public static function labToOkLch(Lab $lab): LCH
	{
		return self::okLabToOkLch(
			self::labToOkLab($lab)
		);
	}

	// MARK: Lch (Cie) <-> OkLch

	/**
	 * okLch to Lch (Cie)
	 * via okLabToLab
	 *
	 * @param  LCH $lch
	 * @return LCH
	 */
	public static function okLchToLch(LCH $lch): LCH
	{
		return self::labToLch(
			self::okLabToLab(
				self::okLchToOkLab($lch)
			)
		);
	}

	/**
	 * Lch (Cie) to OkLch
	 * via labToOkLab
	 *
	 * @param  LCH $lch
	 * @return LCH
	 */
	public static function lchToOkLch(LCH $lch): LCH
	{
		return self::labToOkLch(
			self::lchToLab($lch)
		);
	}

	// MARK: Lch (Cie) to OkLab

	/**
	 * OkLab to Lch (CIE)
	 *
	 * @param  LAB $lab
	 * @return LCH
	 */
	public static function okLabToLch(LAB $lab): LCH
	{
		return self::labToLch(
			self::okLabToLab($lab)
		);
	}

	/**
	 * Lch (CIE) to OkLab
	 *
	 * @param  LCH $lch
	 * @return LAB
	 */
	public static function lchToOkLab(LCH $lch): LAB
	{
		return self::labToOkLab(
			self::lchToLab($lch)
		);
	}
}
