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
	// rgb to oklab and back will have slight shift
	private const DELTA_OKLAB = 1.05;

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

	// MARK: single test

	public function testSingle()
	{
		$this->assertTrue(true, 'Single test');
	// 	$rgb = new Color\Coordinates\RGB([0, 0, 60]);
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

		// $hsl = new Color\Coordinates\HSL([0, 0, 0]);
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
		// $hsb = new Color\Coordinates\HSB([0, 20, 0]);
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
		// $hwb = new Color\Coordinates\HWB([0, 20, 100]);
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

	// MARK: RGB base

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
					$color = new Color\Coordinates\RGB([$r, $g, $b]);
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

	// MARK: HSL base

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
					$color = new Color\Coordinates\HSL([$H, $S, $L]);
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

	// MARK: HSB

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
					$color = new Color\Coordinates\HSB([$H, $S, $B]);
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

	// MARK: HWB

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
					$color = new Color\Coordinates\HWB([$H, $W, $B]);
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

	// MARK: RGB to hex

	/**
	 * Undocumented function
	 *
	 * @covers ::returnAsHex()
	 * @testdox Convert from and to RGB via hex
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
						$hex_color = (new Color\Coordinates\RGB([$r, $g, $b]))
							->returnAsHex($hex_prefix);
						// parse into hex to rgb and see if we get the same r/g/b
						$color = (new Color\Coordinates\RGB($hex_color))->returnAsArray();
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

	// MARK: RGB Linear

	/**
	 * linear RGB conversion tests
	 *
	 * @covers ::fromLinear
	 * @covers ::toLinear
	 * @testdox Convert from and to RGB linear conversion check
	 *
	 * @return void
	 */
	public function testRgbFromToLinear()
	{
		$rgb = (new Color\Coordinates\RGB([10, 20, 30]))->toLinear();
		$this->assertEquals(
			true,
			$rgb->linear,
			'On create flagged linear missing'
		);
		$rgb_color = $rgb->returnAsArray();
		$rgb->toLinear();
		$this->assertEquals(
			$rgb_color,
			$rgb->returnAsArray(),
			'Double linear call does double linear encoding'
		);
		$rgb->fromLinear();
		$this->assertEquals(
			false,
			$rgb->linear,
			'On reverse linear, flag is missing'
		);
		$rgb_color = $rgb->returnAsArray();
		$this->assertEquals(
			$rgb_color,
			$rgb->returnAsArray(),
			'Double linear inverse call does double linear decoding'
		);
		$rgb = new Color\Coordinates\RGB([20, 30, 40]);
		$rgb_color = $rgb->returnAsArray();
		$this->assertEquals(
			false,
			$rgb->linear,
			'On create without linear flag is linear'
		);
		$rgb->toLinear();
		$this->assertEquals(
			true,
			$rgb->linear,
			'On linear call flag is not linear'
		);
		$rgb->fromLinear();
		$this->assertEquals(
			$rgb_color,
			$rgb->returnAsArray(),
			'conversion to and from linear not matching'
		);
	}

	// MARK: okLab

	/**
	 * From/To RGB <-> OkLab / OkLch
	 *
	 * @covers ::rgbToOkLab
	 * @covers ::rgbToOkLch
	 * @covers ::okLabToRgb
	 * @covers ::okLchToRgb
	 * @testdox Convert from and to RGB to OkLab / OkLch
	 *
	 * @return void
	 */
	public function testRgbColorCoordinateConvertToAndBackBackOkLab()
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
					$color = new Color\Coordinates\RGB([$r, $g, $b]);
					$base = 'rgb';
					foreach (['okLab', 'okLch'] as $coord) {
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
							self::DELTA_OKLAB,
							'Convert ' . $base . ' to ' . $coord . ': ' . print_r($color->returnAsArray(), true) . '/'
								. print_r($color_b->returnAsArray(), true)
						);
					}
				}
			}
		}
	}

	/**
	 * internal oklab/oklch conversion
	 *
	 * @covers ::okLchToOkLab
	 * @covers ::okLabToOkLch
	 * @testdox Convert from and to OkLab / OkLch
	 *
	 * @return void
	 */
	public function testOkLabOkLchColorCoordinateConvertToFrom()
	{
		for ($L = 0.0; $L <= 1.0; $L += 0.2) {
			for ($C = 0.0; $C <= 0.5; $C += 0.1) {
				for ($H = 0.0; $H <= 360.0; $H += 60.0) {
					// chroma 0.0 is B/W skip it
					if ($C == 0.0) {
						continue;
					}
					$color = new Color\Coordinates\LCH([$L, $C, $H], 'OkLab');
					$base = 'okLch';
					foreach (['okLab'] as $coord) {
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

	// MARK: CIELab

	/**
	 * From/To RGB <-> Cie lab / Cie lch
	 *
	 * @covers ::rgbToLab
	 * @covers ::rgbToLch
	 * @covers ::labToRgb
	 * @covers ::lchToRgb
	 * @testdox Convert from and to RGB to Cie Lab / Cie Lch
	 *
	 * @return void
	 */
	public function testRgbColorCoordinateConvertToAndBackBackCieLab()
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
					$color = new Color\Coordinates\RGB([$r, $g, $b]);
					$base = 'rgb';
					foreach (['lab', 'lch'] as $coord) {
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
							self::DELTA_OKLAB,
							'Convert ' . $base . ' to ' . $coord . ': ' . print_r($color->returnAsArray(), true) . '/'
								. print_r($color_b->returnAsArray(), true)
						);
					}
				}
			}
		}
	}

	/**
	 * internal cie lab/cie lch conversion
	 *
	 * @covers ::lchToLab
	 * @covers ::labToLch
	 * @testdox Convert from and to Cie Lab / Cie Lch
	 *
	 * @return void
	 */
	public function testLabLchColorCoordinateConvertToFrom()
	{
		for ($L = 0.0; $L <= 1.0; $L += 0.2) {
			for ($C = 0.0; $C <= 0.5; $C += 0.1) {
				for ($H = 0.0; $H <= 360.0; $H += 60.0) {
					// chroma 0.0 is B/W skip it
					if ($C == 0.0) {
						continue;
					}
					$color = new Color\Coordinates\LCH([$L, $C, $H], 'OkLab');
					$base = 'lch';
					foreach (['lab'] as $coord) {
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

	// MARK: Exceptions

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerHueBased(): array
	{
		// all HSB/V HSL HWB have the same value range, create test data for all of them
		return [
			'H' => [
				'color' => [900, 10, 10],
				'error_code' => 1,
				'error_string' => '/ for hue is not in the range of 0 to 360$/'
			],
			'H' => [
				'color' => [-1, 10, 10], 'error_code' => 1,
				'error_string' => '/ for hue is not in the range of 0 to 360$/',
			],
			'H close' => [
				'color' => [360.1, 10, 10],
				'error_code' => 1,
				'error_string' => '/ for hue is not in the range of 0 to 360$/'
			],
			'H close' => [
				'color' => [-0.1, 10, 10], 'error_code' => 1,
				'error_string' => '/ for hue is not in the range of 0 to 360$/',
			],
			'S/W' => [
				'color' => [90, 900, 10], 'error_code' => 2,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'S/W' => [
				'color' => [90, -1, 10], 'error_code' => 2,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'S/W close' => [
				'color' => [90, 100.1, 10], 'error_code' => 2,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'S/W close' => [
				'color' => [90, -0.1, 10], 'error_code' => 2,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'L/B' => [
				'color' => [90, 10, 900], 'error_code' => 3,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'L/B' => [
				'color' => [90, 10, -1], 'error_code' => 3,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'L/B close' => [
				'color' => [90, 10, 100.1], 'error_code' => 3,
				'error_string' => 'is not in the range of 0 to 100',
			],
			'L/B close' => [
				'color' => [90, 10, -0.1], 'error_code' => 3,
				'error_string' => 'is not in the range of 0 to 100',
			],
		];
	}

	// MARK: HSB Exceptions

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerHueBased
	 * @testdox Exception handling for HSB for error $error_code [$_dataName]
	 *
	 * @param  array  $color
	 * @param  int    $error_code
	 * @param  string $error_string
	 * @return void
	 */
	public function testExceptionHSB(array $color, int $error_code, string $error_string): void
	{
		// error string based on code
		switch ($error_code) {
			case 2:
				$error_string = "/ for saturation $error_string$/";
				break;
			case 3:
				$error_string = "/ for brightness $error_string$/";
				break;
		}
		// for H/S/B exception the same
		$this->expectException(\LengthException::class);
		$this->expectExceptionCode($error_code);
		$this->expectExceptionMessageMatches($error_string);
		new Color\Coordinates\HSB($color);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Exception handling for HSB general calls
	 *
	 * @return void
	 */
	public function testExceptionHSBGeneral()
	{
		// allow
		$b = new Color\Coordinates\HSB([0, 0, 0], 'sRGB');
		// invalid access to class
		$b = new Color\Coordinates\HSB([0, 0, 0]);
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->g;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Only array colors allowed");
		new Color\Coordinates\HSB('string');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\HSB([0, 0, 0], 'FOO_BAR');
	}

	// MARK: HSL Exceptions

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerHueBased
	 * @testdox Exception handling for HSL for error $error_code [$_dataName]
	 *
	 * @param  array  $color
	 * @param  int    $error_code
	 * @param  string $error_string
	 * @return void
	 */
	public function testExceptionHSL(array $color, int $error_code, string $error_string): void
	{
		// error string based on code
		switch ($error_code) {
			case 2:
				$error_string = "/ for saturation $error_string$/";
				break;
			case 3:
				$error_string = "/ for lightness $error_string$/";
				break;
		}
		// for H/S/B exception the same
		$this->expectException(\LengthException::class);
		$this->expectExceptionCode($error_code);
		$this->expectExceptionMessageMatches($error_string);
		new Color\Coordinates\HSL($color);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Exception handling for HSL general calls
	 *
	 * @return void
	 */
	public function testExceptionHSLGeneral()
	{
		// allow
		$b = new Color\Coordinates\HSL([0, 0, 0], 'sRGB');
		// invalid access to class
		$b = new Color\Coordinates\HSL([0, 0, 0]);
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->g;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Only array colors allowed");
		new Color\Coordinates\HSL('string');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\HSL([0, 0, 0], 'FOO_BAR');
	}

	// MARK: HWB Exceptions

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerHueBased
	 * @testdox Exception handling for HWB for error $error_code [$_dataName]
	 *
	 * @param  array  $color
	 * @param  int    $error_code
	 * @param  string $error_string
	 * @return void
	 */
	public function testExceptionHWB(array $color, int $error_code, string $error_string): void
	{
		// error string based on code
		switch ($error_code) {
			case 2:
				$error_string = "/ for whiteness $error_string$/";
				break;
			case 3:
				$error_string = "/ for blackness $error_string$/";
				break;
		}
		// for H/S/B exception the same
		$this->expectException(\LengthException::class);
		$this->expectExceptionCode($error_code);
		$this->expectExceptionMessageMatches($error_string);
		new Color\Coordinates\HWB($color);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Exception handling for HWB general calls
	 *
	 * @return void
	 */
	public function testExceptionHWBGeneral()
	{
		// allow
		$b = new Color\Coordinates\HWB([0, 0, 0], 'sRGB');
		// invalid access to class
		$b = new Color\Coordinates\HWB([0, 0, 0]);
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->g;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Only array colors allowed");
		new Color\Coordinates\HWB('string');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\HWB([0, 0, 0], 'FOO_BAR');
	}

	// MARK: RGB Exceptions

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerRgbBased(): array
	{
		// all HSB/V HSL HWB have the same value range, create test data for all of them
		return [
			'R' => [
				'color' => [900, 10, 10],
				'error_code' => 1,
				'error_string' => '/ is not in the range of 0 to 255$/',
				'linear' => false,
			],
			'R' => [
				'color' => [-1, 10, 10], 'error_code' => 1,
				'error_string' => '/ is not in the range of 0 to 255$/',
				'linear' => false,
			],
			'G' => [
				'color' => [90, 900, 10], 'error_code' => 1,
				'error_string' => '/ is not in the range of 0 to 255$/',
				'linear' => false,
			],
			'G' => [
				'color' => [90, -1, 10], 'error_code' => 1,
				'error_string' => '/ is not in the range of 0 to 255$/',
				'linear' => false,
			],
			'B' => [
				'color' => [90, 10, 900], 'error_code' => 1,
				'error_string' => '/ is not in the range of 0 to 255$/',
				'linear' => false,
			],
			'B' => [
				'color' => [90, 10, -1], 'error_code' => 1,
				'error_string' => '/ is not in the range of 0 to 255$/',
				'linear' => false,
			],
			'R linear' => [
				'color' => [2, 0.5, 0.5],
				'error_code' => 2,
				'error_string' => '/ is not in the range of 0 to 1 for linear rgb$/',
				'linear' => true,
			],
			'R linear' => [
				'color' => [-1, 0.5, 0.5],
				'error_code' => 2,
				'error_string' => '/ is not in the range of 0 to 1 for linear rgb$/',
				'linear' => true,
			],
			'G linear' => [
				'color' => [0.5, 2, 0.5],
				'error_code' => 2,
				'error_string' => '/ is not in the range of 0 to 1 for linear rgb$/',
				'linear' => true,
			],
			'G linear' => [
				'color' => [0.5, -1, 0.5],
				'error_code' => 2,
				'error_string' => '/ is not in the range of 0 to 1 for linear rgb$/',
				'linear' => true,
			],
			'B linear' => [
				'color' => [0.5, 0.5, 2],
				'error_code' => 2,
				'error_string' => '/ is not in the range of 0 to 1 for linear rgb$/',
				'linear' => true,
			],
			'B linear' => [
				'color' => [0.5, 0.5, -1],
				'error_code' => 2,
				'error_string' => '/ is not in the range of 0 to 1 for linear rgb$/',
				'linear' => true,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerRgbBased
	 * @testdox Exception handling for RGB for error $error_code [$_dataName]
	 *
	 * @param  string|array $color
	 * @param  int          $error_code
	 * @param  string       $error_string
	 * @param  bool         $linear
	 * @return void
	 */
	public function testExceptionRGB(string|array $color, int $error_code, string $error_string, bool $linear): void
	{
		// for RGB exception the same
		$this->expectException(\LengthException::class);
		$this->expectExceptionCode($error_code);
		$this->expectExceptionMessageMatches($error_string);
		new Color\Coordinates\RGB($color, options: ["linear" => $linear]);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__get
	 * @testdox Exception handling for RGB general calls
	 *
	 * @return void
	 */
	public function testExceptionRGBGeneral()
	{
		// allow
		$b = new Color\Coordinates\RGB([0, 0, 0], 'sRGB');
		// invalid access to class
		$b = new Color\Coordinates\RGB([0, 0, 0]);
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->h;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\RGB([0, 0, 0], 'FOO_BAR');
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setFromHex
	 * @testdox Exception handling for RGB setFromHex failues
	 *
	 * @return void
	 */
	public function testExceptionRGBFromHex()
	{
		$color = '';
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(3);
		$this->expectExceptionMessage('hex_string argument cannot be empty');
		new Color\Coordinates\RGB($color);

		$color = 'zshj';
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(3);
		$this->expectExceptionMessage('hex_string argument cannot be empty');
		new Color\Coordinates\RGB($color);

		$color = 'aabff';
		$this->expectException(\UnexpectedValueException::class);
		$this->expectExceptionCode(4);
		$this->expectExceptionMessageMatches('/^Invalid hex_string: /');
		new Color\Coordinates\RGB($color);
	}

	// MARK: Lab Exceptions

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerLabBased(): array
	{
		// all HSB/V HSL HWB have the same value range, create test data for all of them
		return [
			'L CieLab' => [
				'color' => [900, 10, 10], 'error_code' => 1,
				'error_string' => '/ for lightness is not in the range of 0 to 100 for CIE Lab$/',
				'colorspace' => 'CIELab',
			],
			'L CieLab' => [
				'color' => [-1, 10, 10], 'error_code' => 1,
				'error_string' => '/ for lightness is not in the range of 0 to 100 for CIE Lab$/',
				'colorspace' => 'CIELab',
			],
			'L OkLab' => [
				'color' => [900, 0.2, 0.2], 'error_code' => 1,
				'error_string' => '/ for lightness is not in the range of 0.0 to 1.0 for OkLab$/',
				'colorspace' => 'OkLab',
			],
			'L OkLab' => [
				'color' => [-1, 0.2, 0.2], 'error_code' => 1,
				'error_string' => '/ for lightness is not in the range of 0.0 to 1.0 for OkLab$/',
				'colorspace' => 'OkLab',
			],
			'a CieLab' => [
				'color' => [90, 900, 10], 'error_code' => 2,
				'error_string' => '/ for a is not in the range of -125 to 125 for CIE Lab$/',
				'colorspace' => 'CIELab',
			],
			'a CieLab' => [
				'color' => [90, -900, 10], 'error_code' => 2,
				'error_string' => '/ for a is not in the range of -125 to 125 for CIE Lab$/',
				'colorspace' => 'CIELab',
			],
			'a OkLab' => [
				'color' => [0.5, 900, 0.2], 'error_code' => 2,
				'error_string' => '/ for a is not in the range of -0.5 to 0.5 for OkLab$/',
				'colorspace' => 'OkLab',
			],
			'a OkLab' => [
				'color' => [0.6, -900, 0.2], 'error_code' => 2,
				'error_string' => '/ for a is not in the range of -0.5 to 0.5 for OkLab$/',
				'colorspace' => 'OkLab',
			],
			'b CieLab' => [
				'color' => [90, 10, 900], 'error_code' => 3,
				'error_string' => '/ for b is not in the range of -125 to 125 for CIE Lab$/',
				'colorspace' => 'CIELab',
			],
			'b CieLab' => [
				'color' => [90, 10, -999], 'error_code' => 3,
				'error_string' => '/ for b is not in the range of -125 to 125 for CIE Lab$/',
				'colorspace' => 'CIELab',
			],
			'b OkLab' => [
				'color' => [0.6, 0.2, 900], 'error_code' => 3,
				'error_string' => '/ for b is not in the range of -0.5 to 0.5 for OkLab$/',
				'colorspace' => 'OkLab',
			],
			'b OkLab' => [
				'color' => [0.6, 0.2, -999], 'error_code' => 3,
				'error_string' => '/ for b is not in the range of -0.5 to 0.5 for OkLab$/',
				'colorspace' => 'OkLab',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerLabBased
	 * @testdox Exception handling for Lab for error $error_code [$_dataName]
	 *
	 * @param  string|array $color
	 * @param  int          $error_code
	 * @param  string       $error_string
	 * @param  string       $colorspace
	 * @return void
	 */
	public function testExceptionLab(
		string|array $color,
		int $error_code,
		string $error_string,
		string $colorspace
	): void {
		// for RGB exception the same
		$this->expectException(\LengthException::class);
		$this->expectExceptionCode($error_code);
		$this->expectExceptionMessageMatches($error_string);
		new Color\Coordinates\Lab($color, colorspace: $colorspace);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__get
	 * @testdox Exception handling for Lab general calls
	 *
	 * @return void
	 */
	public function testExceptionLabGeneral()
	{
		// allow
		$b = new Color\Coordinates\Lab([0, 0, 0], 'OkLab');
		// invalid access to class
		$b = new Color\Coordinates\Lab([0, 0, 0], 'CIELab');
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->x;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Only array colors allowed");
		new Color\Coordinates\Lab('string', 'CIELab');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\Lab([0, 0, 0], 'FOO_BAR');
	}

	// MARK: LCH Exceptions

	// public function testExceptionLch(string|array $color, int $error_code, string $error_string): void

	/**
	 * Undocumented function
	 *
	 * @covers ::__get
	 * @testdox Exception handling for LCH general calls
	 *
	 * @return void
	 */
	public function testExceptionLchGeneral()
	{
		// allow
		$b = new Color\Coordinates\LCH([0, 0, 0], 'OkLab');
		// invalid access to class
		$b = new Color\Coordinates\LCH([0, 0, 0], 'CIELab');
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->x;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Only array colors allowed");
		new Color\Coordinates\LCH('string', 'CIELab');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\LCH([0, 0, 0], 'FOO_BAR');
	}

	// MARK: XYZ Exceptions

	// Note, we do not check for value exceptions here
	// public function testExceptionXyz(string|array $color, int $error_code, string $error_string): void

	/**
	 * Undocumented function
	 *
	 * @covers ::__get
	 * @testdox Exception handling for XYZ general calls
	 *
	 * @return void
	 */
	public function testExceptionXyzGeneral()
	{
		// allow
		$b = new Color\Coordinates\XYZ([0, 0, 0], 'CIEXYZ');
		// invalid access to class
		$b = new Color\Coordinates\XYZ([0, 0, 0]);
		$this->expectException(\ErrorException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Creation of dynamic property is not allowed");
		$b->x;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Only array colors allowed");
		new Color\Coordinates\XYZ('string');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage("Not allowed colorspace");
		new Color\Coordinates\XYZ([0, 0, 0], 'FOO_BAR');
	}
}

// __END__
