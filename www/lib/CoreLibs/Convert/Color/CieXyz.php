<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/11/12
 * DESCRIPTION:
 * CIE XYZ color space conversion
 * This for various interims work
 * none public
*/

declare(strict_types=1);

namespace CoreLibs\Convert\Color;

use CoreLibs\Convert\Math;
use CoreLibs\Convert\Color\Coordinates\RGB;
use CoreLibs\Convert\Color\Coordinates\Lab;
use CoreLibs\Convert\Color\Coordinates\XYZ;

class CieXyz
{
	// MARK: public wrapper functions

	/**
	 * Convert from RGB to OkLab
	 * via xyz D65
	 *
	 * @param  RGB $rgb
	 * @return Lab
	 */
	public static function rgbViaXyzD65ToOkLab(RGB $rgb): Lab
	{
		return self::xyzD65ToOkLab(
			self::linRgbToXyzD65($rgb)
		);
	}

	/**
	 * Convet from OkLab to RGB
	 * via xyz D65
	 *
	 * @param  Lab $lab
	 * @return RGB
	 */
	public static function okLabViaXyzD65ToRgb(Lab $lab): RGB
	{
		return self::xyzD65ToLinRgb(
			self::okLabToXyzD65($lab)
		)->fromLinear();
	}

	/**
	 * Convert RGB to CIE Lab
	 * via xyz D65 to xyz D50
	 *
	 * @param  RGB $rgb
	 * @return Lab
	 */
	public static function rgbViaXyzD65ViaXyzD50ToLab(RGB $rgb): Lab
	{
		return self::xyzD50ToLab(
			self::xyzD65ToXyzD50(
				self::linRgbToXyzD65($rgb)
			)
		);
	}

	/**
	 * Convert CIE Lab to RGB
	 * via xyz D50 to xyz D65
	 *
	 * @param  Lab $lab
	 * @return RGB
	 */
	public static function labViaXyzD50ViaXyzD65ToRgb(Lab $lab): RGB
	{
		return self::xyzD65ToLinRgb(
			self::xyzD50ToXyxD65(
				self::labToXyzD50($lab)
			)
		)->fromLinear();
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return Lab
	 */
	public static function okLabViaXyzD65ViaXyzD50ToLab(Lab $lab): Lab
	{
		return self::xyzD50ToLab(
			self::xyzD65ToXyzD50(
				self::okLabToXyzD65($lab)
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param  Lab $lab
	 * @return Lab
	 */
	public static function labViaXyzD50ViaXyzD65ToOkLab(Lab $lab): Lab
	{
		return self::xyzD65ToOkLab(
			self::xyzD50ToXyxD65(
				self::labToXyzD50($lab)
			)
		);
	}

	// MARK: xyzD65 <-> xyzD50

	/**
	 * xyzD65 to xyzD50 whitepoint
	 *
	 * @param  XYZ $xyz
	 * @return XYZ
	 */
	private static function xyzD65ToXyzD50(XYZ $xyz): XYZ
	{
		return XYZ::__constructFromArray(Math::multiplyMatrices(
			a: [
				[1.0479298208405488, 0.022946793341019088, -0.05019222954313557],
				[0.029627815688159344, 0.990434484573249, -0.01707382502938514],
				[-0.009243058152591178, 0.015055144896577895, 0.7518742899580008],
			],
			b: $xyz->returnAsArray(),
		), whitepoint: 'D50');
	}

	/**
	 * xyzD50 to xyzD65 whitepoint
	 *
	 * @param  XYZ $xyz
	 * @return XYZ
	 */
	private static function xyzD50ToXyxD65(XYZ $xyz): XYZ
	{
		return XYZ::__constructFromArray(Math::multiplyMatrices(
			a: [
				[0.9554734527042182, -0.023098536874261423, 0.0632593086610217],
				[-0.028369706963208136, 1.0099954580058226, 0.021041398966943008],
				[0.012314001688319899, -0.020507696433477912, 1.3303659366080753],
			],
			b: $xyz->returnAsArray()
		), whitepoint: 'D65');
	}

	// MARK: xyzD50 <-> Lab

	/**
	 * Convert xyzD50 to Lab (Cie)
	 *
	 * @param  XYZ $xyz
	 * @return Lab
	 */
	private static function xyzD50ToLab(XYZ $xyz): Lab
	{
		$_xyz = $xyz->returnAsArray();
		$d50 = [
			0.3457 / 0.3585,
			1.00000,
			(1.0 - 0.3457 - 0.3585) / 0.3585,
		];

		$a = 216 / 24389;
		$b = 24389 / 27;

		$_xyz = array_map(
			fn ($k, $v) => $v / $d50[$k],
			array_keys($_xyz),
			array_values($_xyz),
		);

		$f = array_map(
			fn ($v) => (($v > $a) ?
				pow($v, 1 / 3) :
				(($b * $v + 16) / 116)
			),
			$_xyz,
		);

		return Lab::__constructFromArray([
			(116 * $f[1]) - 16,
			500 * ($f[0] - $f[1]),
			200 * ($f[1] - $f[2]),
		], colorspace: 'CIELab');
	}

	/**
	 * Convert Lab (Cie) to xyz D50
	 *
	 * @param  Lab    $lab
	 * @return XYZ
	 */
	private static function labToXyzD50(Lab $lab): XYZ
	{
		$_lab = $lab->returnAsArray();
		$a = 24389 / 27;
		$b = 216 / 24389;
		$f = [];
		$f[1] = ($_lab[0] + 16) / 116;
		$f[0] = $_lab[1] / 500 + $f[1];
		$f[2] = $f[1] - $_lab[2] / 200;
		$xyz  = [
			// x
			pow($f[0], 3) > $b ?
				pow($f[0], 3) :
				(116 * $f[0] - 16) / $a,
			// y
			$_lab[0] > $a * $b ?
				pow(($_lab[0] + 16) / 116, 3) :
				$_lab[0] / $a,
			// z
			pow($f[2], 3) > $b ?
				pow($f[2], 3) :
				(116 * $f[2] - 16) / $a,
		];

		$d50 = [
			0.3457 / 0.3585,
			1.00000,
			(1.0 - 0.3457 - 0.3585) / 0.3585,
		];

		return XYZ::__constructFromArray(
			array_map(
				fn ($k, $v) => $v * $d50[$k],
				array_keys($xyz),
				array_values($xyz),
			),
			whitepoint: 'D50'
		);
	}

	// MARK: xyzD65 <-> (linear)RGB

	/**
	 * convert linear RGB to xyz D65
	 * if rgb is not flagged linear, it will be auto converted
	 *
	 * @param  RGB  $rgb
	 * @return XYZ
	 */
	private static function linRgbToXyzD65(RGB $rgb): XYZ
	{
		// if not linear, convert to linear
		if (!$rgb->linear) {
			$rgb->toLinear();
		}
		return XYZ::__constructFromArray(Math::multiplyMatrices(
			[
				[0.41239079926595934, 0.357584339383878, 0.1804807884018343],
				[0.21263900587151027, 0.715168678767756, 0.07219231536073371],
				[0.01933081871559182, 0.11919477979462598, 0.9505321522496607],
			],
			$rgb->returnAsArray()
		), whitepoint: 'D65');
	}

	/**
	 * Convert xyz D65 to linear RGB
	 *
	 * @param  XYZ $xyz
	 * @return RGB
	 */
	private static function xyzD65ToLinRgb(XYZ $xyz): RGB
	{
		// xyz D65 to linrgb
		return RGB::__constructFromArray(Math::multiplyMatrices(
			a : [
				[  3.2409699419045226,  -1.537383177570094,   -0.4986107602930034  ],
				[ -0.9692436362808796,   1.8759675015077202,   0.04155505740717559 ],
				[  0.05563007969699366, -0.20397695888897652,  1.0569715142428786  ],
			],
			b : $xyz->returnAsArray()
		), linear: true);
	}

	// MARK: xyzD65 <-> OkLab

	/**
	 * xyz D65 to OkLab
	 *
	 * @param  XYZ $xyz
	 * @return Lab
	 */
	private static function xyzD65ToOkLab(XYZ $xyz): Lab
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
					b: $xyz->returnAsArray(),
				),
			)
		), colorspace: 'OkLab');
	}

	/**
	 * xyz D65 to OkLab
	 *
	 * @param  Lab    $lab
	 * @return XYZ
	 */
	private static function okLabToXyzD65(Lab $lab): XYZ
	{
		return XYZ::__constructFromArray(Math::multiplyMatrices(
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
		), whitepoint: 'D65');
	}
}

// __END__
