<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Convert\Color;

/**
 * Test class for Convert\Color\Color
 * @coversDefaultClass \CoreLibs\Convert\Color\Color
 * @testdox \CoreLibs\Convert\Color\Color method tests
 */
final class CoreLibsConvertColorTest extends TestCase
{
	// 12 precision allowed, RGB and back has a lot of float imprecisions here
	private const DELTA = 0.000000000001;

	// sRGB base convert test, should round around and come out the same
	// use for RGB 0, 0, 0 in 60 steps and then max 255
	// for Hxx 0 + 60 until 360 == 0
	// other values for this depend

	// convert to/from oklab/cielab: just from RGB (or HSL) as pre conversion are all the same

	public static function adjustToIntForRgb(array $values): array
	{
		return array_map(
			fn ($v) => round($v),
			$values
		);
	}

	public function testSingle()
	{
		$this->assertTrue(true, 'Single test');
	// 	$rgb = Color\Coordinates\RGB::__constructFromArray([0, 0, 60]);
	// 	print "IN: " . print_r($rgb, true) . "\n";
	// 	$hsl = Color\Color::rgbToHsl($rgb);
	// 	print "to HSL: " . print_r($hsl, true) . "\n";
	// 	$hsb = Color\Color::hslToHsb($hsl);
	// 	print "to HSB: " . print_r($hsb, true) . "\n";
	// 	$hwb = Color\Color::hsbToHwb($hsb);
	// 	print "to HWB: " . print_r($hwb, true) . "\n";
	// 	// and reverse
	// 	$hsb_r = Color\Color::hwbToHsb($hwb);
	// 	print "R to HSB: " . print_r($hsb_r, true) . "\n";
	// 	$hsl_r = Color\Color::hsbToHsl($hsb_r);
	// 	print "R to HSB: " . print_r($hsl_r, true) . "\n";
	// 	$rgb_r = Color\Color::hslToRgb($hsl_r);
	// 	print "R to RGB: " . print_r($rgb_r, true) . "\n";

		// $hsl = Color\Coordinates\HSL::__constructFromArray([0, 0, 0]);
		// print "IN HSL: " . print_r($hsl, true) . "\n";
		// $hsb = Color\Color::hslToHsb($hsl);
		// print "to HSB: " . print_r($hsb, true) . "\n";
		// $hwb = Color\Color::hsbToHwb($hsb);
		// print "to HWB: " . print_r($hwb, true) . "\n";
		// // and reverse
		// $hsb_r = Color\Color::hwbToHsb($hwb);
		// print "R to HSB: " . print_r($hsb_r, true) . "\n";
		// $hsl_r = Color\Color::hsbToHsl($hsb_r);
		// print "R to HSL: " . print_r($hsl_r, true) . "\n";
		// print "--------\n";
		// $hsb = Color\Coordinates\HSB::__constructFromArray([0, 20, 0]);
		// print "IN HSB: " . print_r($hsb, true) . "\n";
		// $hsl = Color\Color::hsbToHsl($hsb);
		// print "to HSL: " . print_r($hsl, true) . "\n";
		// $hwb = Color\Color::hslToHwb($hsl);
		// print "to HWB: " . print_r($hwb, true) . "\n";
		// // and reverse
		// $hsl_r = Color\Color::hwbToHsl($hwb);
		// print "R to HSB: " . print_r($hsb_r, true) . "\n";
		// $hsb_r = Color\Color::hslToHsb($hsl_r);
		// print "R to HSL: " . print_r($hsb_r, true) . "\n";
		// print "--------\n";
		// $hwb = Color\Coordinates\HWB::__constructFromArray([0, 20, 100]);
		// print "IN: " . print_r($hwb, true) . "\n";
		// $hsl = Color\Color::hwbToHsl($hwb);
		// print "to HSL: " . print_r($hsl, true) . "\n";
		// $hwb_r = Color\Color::hslToHwb($hsl);
		// print "HSL to HWB: " . print_r($hwb_r, true) . "\n";
		// $hsb = Color\Color::hwbToHsb($hwb);
		// print "to HSB: " . print_r($hsb, true) . "\n";
		// $hwb_r = Color\Color::hsbToHwb($hsb);
		// print "HSL to HWB: " . print_r($hwb_r, true) . "\n";
	}

	/**
	 * From/To RGB <-> ... conversion tests
	 *
	 * @covers ::rgbToHsb
	 * @covers ::rgbToHsl
	 * @covers ::rgbToHwb
	 * @covers ::hsbToRgb
	 * @covers ::hslToRgb
	 * @covers ::hwebToRgb
	 * @testdox Convert from and to RGB via HSL, HWB, HSB/V
	 *
	 * @return void
	 */
	public function testRgbColorCoordinateConvertToAndBack(): void
	{
		for ($r = 0; $r <= 300; $r += 60) {
			for ($g = 0; $g <= 300; $g += 60) {
				for ($b = 0; $b <= 300; $b += 60) {
					// for this test we stay in the correct lane
					if ($r > 255) {
						$r = 255;
					}
					if ($g > 255) {
						$g = 255;
					}
					if ($b > 255) {
						$b = 255;
					}
					// base is always the same
					$color = Color\Coordinates\RGB::__constructFromArray([$r, $g, $b]);
					$base = 'rgb';
					foreach (['hsb', 'hsl', 'hwb'] as $coord) {
						// print "COORD: " . $coord . ", RGB: " . print_r($color->returnAsArray(), true) . "\n";
						// rgb to X and back must be same
						$target = $base . 'To' . ucfirst($coord);
						$source = $coord . 'To' . ucfirst($base);
						$converted_color = Color\Color::$target($color);
						$color_b = Color\Color::$source($converted_color);
						// $converted_color = Color\Color::rgbToHsb($color);
						// $rgb_b = Color\Color::hsbToRgb($converted_color);
						$this->assertEqualsWithDelta(
							$color->returnAsArray(),
							$color_b->returnAsArray(),
							self::DELTA,
							'Convert ' . $base . ' to ' . $coord . ': ' . print_r($color->returnAsArray(), true) . '/'
								. print_r($color_b->returnAsArray(), true)
						);
					}
				}
			}
		}
	}

	// HSL / HSB / HWB conversion are not reversable if
	// HSL: lightness 0 or 100
	// HSB: saturation or brightness 0
	// HWB: blackness >= 80 and whitness >= 20 or B>=20 & W>=20 or B>=50 & W>=50

	/**
	 * Undocumented function
	 *
	 * @covers ::hslToHsb
	 * @covers ::hsbToHsl
	 * @covers ::hslToHwb
	 * @covers ::hwbToHsl
	 * @testdox Convert from and to HSL via RGB, HWB, HSB/V
	 *
	 * @return void
	 */
	public function testHslColorCoordinateConvertToAndBack(): void
	{
		for ($H = 0; $H <= 360; $H += 60) {
			for ($S = 0; $S <= 100; $S += 20) {
				for ($L = 0; $L <= 100; $L += 20) {
					// if lightness 0 or 100 then we cannot reverse (B/W)
					if (($L == 0 or $L == 100)) {
						continue;
					}
					$color = Color\Coordinates\HSL::__constructFromArray([$H, $S, $L]);
					$base = 'hsl';
					foreach (['hsb', 'hwb', 'rgb'] as $coord) {
						// for rgb hue on S = 0 is irrelevant (B/W)
						if ($H > 0 && $coord == 'rgb') {
							continue;
						}
						$target = $base . 'To' . ucfirst($coord);
						$source = $coord . 'To' . ucfirst($base);
						$converted_color = Color\Color::$target($color);
						$color_b = Color\Color::$source($converted_color);
						// print "COORD: " . $coord . ", HSL: " . print_r($color->returnAsArray(), true) . "\n";
						$this->assertEqualsWithDelta(
							$color->returnAsArray(),
							$color_b->returnAsArray(),
							self::DELTA,
							'Convert HSL to ' . $coord . ': ' . print_r($color->returnAsArray(), true) . '/'
								. print_r($color_b->returnAsArray(), true)
						);
					}
				}
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hsbToHsl
	 * @covers ::hslToHsb
	 * @covers ::hsbToHwb
	 * @covers ::hwbToHsb
	 * @testdox Convert from and to HSB via RGB, HWB, HSL
	 *
	 * @return void
	 */
	public function testHsbColorCoordinateConvertToAndBack(): void
	{
		for ($H = 0; $H <= 360; $H += 60) {
			for ($S = 0; $S <= 100; $S += 20) {
				for ($B = 0; $B <= 100; $B += 20) {
					// if sat or brightness is 0 then we cannot reverse correctly (B/W)
					if ($S == 0 or $B == 0) {
						continue;
					}
					$color = Color\Coordinates\HSB::__constructFromArray([$H, $S, $B]);
					$base = 'hsb';
					foreach (['hwb', 'hsl', 'rgb'] as $coord) {
						$target = $base . 'To' . ucfirst($coord);
						$source = $coord . 'To' . ucfirst($base);
						$converted_color = Color\Color::$target($color);
						$color_b = Color\Color::$source($converted_color);
						// print "COORD: " . $coord . ", HSL: " . print_r($color->returnAsArray(), true) . "\n";
						$this->assertEqualsWithDelta(
							$color->returnAsArray(),
							$color_b->returnAsArray(),
							self::DELTA,
							'Convert ' . $base . ' to ' . $coord . ': ' . print_r($color->returnAsArray(), true) . '/'
								. print_r($color_b->returnAsArray(), true)
						);
					}
				}
			}
		}
	}


	/**
	 * Undocumented function
	 *
	 * @covers ::hwbToHsl
	 * @covers ::hslToHwb
	 * @covers ::hwbToHsb
	 * @covers ::hsbToHwb
	 * @testdox Convert from and to HWB via RGB, HSL, HSB/V
	 *
	 * @return void
	 */
	public function testHwbColorCoordinateConvertToAndBack(): void
	{
		for ($H = 0; $H <= 360; $H += 60) {
			for ($W = 0; $W <= 100; $W += 20) {
				for ($B = 0; $B <= 100; $B += 20) {
					// if W>=20 and B>=80 or B>=20 and W>=20 or both >=50
					// we cannot reverse correctl (B/W)
					if (
						($W >= 20 && $B >= 80) ||
						($W >= 80 && $B >= 20) ||
						($W >= 50 && $B >= 50)
					) {
						continue;
					}
					$base = 'hwb';
					$color = Color\Coordinates\HWB::__constructFromArray([$H, $W, $B]);
					foreach (['hsl', 'hsb', 'rgb'] as $coord) {
						// for rgb hue on S = 0 is irrelevant (B/W)
						if ($H > 0 && $coord == 'rgb') {
							continue;
						}
						$target = $base . 'To' . ucfirst($coord);
						$source = $coord . 'To' . ucfirst($base);
						$converted_color = Color\Color::$target($color);
						$color_b = Color\Color::$source($converted_color);
						// print "COORD: " . $coord . ", HSL: " . print_r($color->returnAsArray(), true) . "\n";
						$this->assertEqualsWithDelta(
							$color->returnAsArray(),
							$color_b->returnAsArray(),
							self::DELTA,
							'Convert ' . $base . ' to ' . $coord . ': ' . print_r($color->returnAsArray(), true) . '/'
								. print_r($color_b->returnAsArray(), true)
						);
					}
				}
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * covers ::returnAsHex()
	 * covers ::__constructFromHexString()
	 * @testdox Convert from RGB to hex and back
	 *
	 * @return void
	 */
	public function testRgbToFromHex(): void
	{
		for ($r = 0; $r <= 300; $r += 60) {
			for ($g = 0; $g <= 300; $g += 60) {
				for ($b = 0; $b <= 300; $b += 60) {
					// for this test we stay in the correct lane
					if ($r > 255) {
						$r = 255;
					}
					if ($g > 255) {
						$g = 255;
					}
					if ($b > 255) {
						$b = 255;
					}
					// with or without prefix
					foreach ([true, false] as $hex_prefix) {
						$hex_color = Color\Coordinates\RGB::__constructFromArray([$r, $g, $b])
							->returnAsHex($hex_prefix);
						// parse into hex to rgb and see if we get the same r/g/b
						$color = Color\Coordinates\RGB::__constructFromHexString($hex_color)->returnAsArray();
						//
						$this->assertEquals(
							[$r, $g, $b],
							$color,
							'Convert rgb to hex and back: ' . print_r([$r, $g, $b], true) . '/'
								. print_r($color, true)
						);
					}
				}
			}
		}
	}

	// oklab

	// cie lab

	// create exceptions for all color spaces

	public function testExceptionHSB(): void
	{
		// for H/S/B exception the same
		$this->expectException(\LengthException::class);
		Color\Coordinates\HSB::__constructFromArray([900, 10, 10]);

		// invalid access to class
		// $this->expectException(\ErrorException::class);
	}
}

// __END__
