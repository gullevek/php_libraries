<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\Colors
 * @coversDefaultClass \CoreLibs\Convert\Colors
 * @testdox \CoreLibs\Convert\Colors legacy method tests
 */
final class CoreLibsConvertColorsTest extends TestCase
{
	// convert list
	public static $colors = [];

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerRgb2hexColor(): array
	{
		return [
			'color' => [
				0 => 10,
				1 => 100,
				2 => 200,
				3 => '#0a64c8',
				4 => '0a64c8'
			],
			'gray' => [
				0 => 12,
				1 => 12,
				2 => 12,
				3 => '#0c0c0c',
				4 => '0c0c0c',
			],
			'black' => [
				0 => 0,
				1 => 0,
				2 => 0,
				3 => '#000000',
				4 => '000000',
			],
			'white' => [
				0 => 255,
				1 => 255,
				2 => 255,
				3 => '#ffffff',
				4 => 'ffffff',
			],
			'invalid color red & green' => [
				0 => -12,
				1 => 300,
				2 => 12,
				3 => false,
				4 => false
			],
			'invalid color red ' => [
				0 => -12,
				1 => 12,
				2 => 12,
				3 => false,
				4 => false
			],
			'invalid color green ' => [
				0 => 12,
				1 => -12,
				2 => 12,
				3 => false,
				4 => false
			],
			'invalid color blue ' => [
				0 => 12,
				1 => 12,
				2 => -12,
				3 => false,
				4 => false
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerHex2rgbColor(): array
	{
		return [
			'color' => [
				0 => '#0a64c8',
				1 => ['r' => 10, 'g' => 100, 'b' => 200],
				2 => '10,100,200',
				3 => ';',
				4 => '10;100;200',
			],
			'gray, long' => [
				0 => '0c0c0c',
				1 => ['r' => 12, 'g' => 12, 'b' => 12],
				2 => '12,12,12',
				3 => ';',
				4 => '12;12;12',
			],
			'gray, short' => [
				0 => 'ccc',
				1 => ['r' => 204, 'g' => 204, 'b' => 204],
				2 => '204,204,204',
				3 => ';',
				4 => '204;204;204',
			],
			'hex string with #' => [
				0 => '#0c0c0c',
				1 => ['r' => 12, 'g' => 12, 'b' => 12],
				2 => '12,12,12',
				3 => ';',
				4 => '12;12;12',
			],
			'a too long hex string' => [
				0 => '#0c0c0c0c',
				1 => false,
				2 => false,
				3 => ';',
				4 => false,
			],
			'a too short hex string' => [
				0 => '0c0c',
				1 => false,
				2 => false,
				3 => ';',
				4 => false,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function rgb2hslAndhsbList(): array
	{
		// if hsb_from or hsl_from is set, this will be used in hsb/hsl convert
		// hsb_rgb is used for adjusted rgb valus due to round error to in
		return [
			'valid gray' => [
				'rgb' => [12, 12, 12],
				'hsb' => [0, 0, 5],
				'hsb_rgb' => [13, 13, 13], // should be rgb, but rounding in this
				'hsl' => [0.0, 0.0, 4.7],
				'valid' => true,
			],
			'valid color' => [
				'rgb' => [10, 100, 200],
				'hsb' => [212, 95, 78.0],
				'hsb_rgb' => [10, 98, 199], // should be rgb, but rounding error
				'hsl' => [211.6, 90.5, 41.2],
				'valid' => true,
			],
			// hsg/hsl with 360 which is seen as 0
			'valid color hue 360' => [
				'rgb' => [200, 10, 10],
				'hsb' => [0, 95, 78.0],
				'hsb_from' => [360, 95, 78.0],
				'hsb_rgb' => [199, 10, 10], // should be rgb, but rounding error
				'hsl' => [0.0, 90.5, 41.2],
				'hsl_from' => [360.0, 90.5, 41.2],
				'valid' => true,
			],
			// invalid values
			'invalid color r/h/h low' => [
				'rgb' => [-1, 12, 12],
				'hsb' => [-1, 50, 50],
				'hsl' => [-1, 50, 50],
				'valid' => false,
			],
			'invalid color r/h/h high' => [
				'rgb' => [256, 12, 12],
				'hsb' => [361, 50, 50],
				'hsl' => [361, 50, 50],
				'valid' => false,
			],
			'invalid color g/s/s low' => [
				'rgb' => [12, -1, 12],
				'hsb' => [1, -1, 50],
				'hsl' => [1, -1, 50],
				'valid' => false,
			],
			'invalid color g/s/s high' => [
				'rgb' => [12, 256, 12],
				'hsb' => [1, 101, 50],
				'hsl' => [1, 101, 50],
				'valid' => false,
			],
			'invalid color b/b/l low' => [
				'rgb' => [12, 12, -1],
				'hsb' => [1, 50, -1],
				'hsl' => [1, 50, -1],
				'valid' => false,
			],
			'invalid color b/b/l high' => [
				'rgb' => [12, 12, 256],
				'hsb' => [1, 50, 101],
				'hsl' => [1, 50, 101],
				'valid' => false,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerRgb2hsbColor(): array
	{
		$list = [];
		foreach ($this->rgb2hslAndhsbList() as $name => $values) {
			$list[$name . ', rgb to hsb'] = [
				0 => $values['rgb'][0],
				1 => $values['rgb'][1],
				2 => $values['rgb'][2],
				3 => $values['valid'] ? $values['hsb'] : false
			];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerHsb2rgbColor(): array
	{
		$list = [];
		foreach ($this->rgb2hslAndhsbList() as $name => $values) {
			$list[$name . ', hsb to rgb'] = [
				0 => $values['hsb_from'][0] ?? $values['hsb'][0],
				1 => $values['hsb_from'][1] ?? $values['hsb'][1],
				2 => $values['hsb_from'][2] ?? $values['hsb'][2],
				3 => $values['valid'] ? $values['hsb_rgb'] : false
			];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerRgb2hslColor(): array
	{
		$list = [];
		foreach ($this->rgb2hslAndhsbList() as $name => $values) {
			$list[$name . ', rgb to hsl'] = [
				0 => $values['rgb'][0],
				1 => $values['rgb'][1],
				2 => $values['rgb'][2],
				3 => $values['valid'] ? $values['hsl'] : false
			];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerHsl2rgbColor(): array
	{
		$list = [];
		foreach ($this->rgb2hslAndhsbList() as $name => $values) {
			$list[$name . ', hsl to rgb'] = [
				0 => $values['hsl_from'][0] ?? $values['hsl'][0],
				1 => $values['hsl_from'][1] ?? $values['hsl'][1],
				2 => $values['hsl_from'][2] ?? $values['hsl'][2],
				3 => $values['valid'] ? $values['rgb'] : false
			];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 * TODO: add cross convert check
	 *
	 * @covers ::rgb2hex
	 * @dataProvider providerRgb2hexColor
	 * @testdox rgb2hex $input_r,$input_g,$input_b will be $expected [$_dataName]
	 *
	 * @param int $input_r
	 * @param int $input_g
	 * @param int $input_b
	 * @param string|bool $expected_hash
	 * @param string|bool $expected
	 * @return void
	 */
	public function testRgb2hex(
		int $input_r,
		int $input_g,
		int $input_b,
		string|bool $expected_hash,
		string|bool $expected
	) {
		// if expected hash is or expected is false, we need to check for
		// LengthException
		if ($expected_hash === false || $expected === false) {
			$this->expectException(\LengthException::class);
		}
		// with #
		$this->assertEquals(
			$expected_hash,
			\CoreLibs\Convert\Colors::rgb2hex($input_r, $input_g, $input_b)
		);
		// without #
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::rgb2hex($input_r, $input_g, $input_b, false)
		);
		// cross convert must match
		// $rgb = \CoreLibs\Convert\Colors::hex2rgb($expected_hash);
		// if ($rgb === false) {
		// 	$rgb = [
		// 		'r' => $input_r,
		// 		'g' => $input_g,
		// 		'b' => $input_b,
		// 	];
		// }
		// $this->assertEquals(
		// 	$expected_hash,
		// 	\CoreLibs\Convert\Colors::rgb2hex($rgb['r'], $rgb['g'], $rgb['b'])
		// );
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hex2rgb
	 * @dataProvider providerHex2rgbColor
	 * @testdox hex2rgb $input will be $expected, $expected_str str[,], $expected_str_sep str[$separator] [$_dataName]
	 *
	 * @param string $input
	 * @param array|bool $expected
	 * @param string|bool $expected_str
	 * @param string $separator
	 * @param string|bool $expected_str_sep
	 * @return void
	 */
	public function testHex2rgb(
		string $input,
		array|bool $expected,
		string|bool $expected_str,
		string $separator,
		string|bool $expected_str_sep
	): void {
		if ($expected === false || $expected_str === false || $expected_str_sep === false) {
			$hex_string = preg_replace("/[^0-9A-Fa-f]/", '', $input);
			if (!is_string($hex_string)) {
				$this->expectException(\InvalidArgumentException::class);
			} else {
				$this->expectException(\UnexpectedValueException::class);
			}
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::hex2rgb($input)
		);
		$this->assertEquals(
			$expected_str,
			\CoreLibs\Convert\Colors::hex2rgb($input, true)
		);
		$this->assertEquals(
			$expected_str_sep,
			\CoreLibs\Convert\Colors::hex2rgb($input, true, $separator)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::rgb2hsb
	 * @dataProvider providerRgb2hsbColor
	 * @testdox rgb2hsb $input_r,$input_g,$input_b will be $expected [$_dataName]
	 *
	 * @param integer $input_r
	 * @param integer $input_g
	 * @param integer $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testRgb2hsb(int $input_r, int $input_g, int $input_b, array|bool $expected): void
	{
		if ($expected === false) {
			$this->expectException(\LengthException::class);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::rgb2hsb($input_r, $input_g, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hsb2rgb
	 * @dataProvider providerHsb2rgbColor
	 * @testdox hsb2rgb $input_h,$input_s,$input_b will be $expected [$_dataName]
	 *
	 * @param float $input_h
	 * @param float $input_s
	 * @param float $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testHsb2rgb(float $input_h, float $input_s, float $input_b, array|bool $expected): void
	{
		if ($expected === false) {
			$this->expectException(\LengthException::class);
			$expected = [];
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::hsb2rgb($input_h, $input_s, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::rgb2hsl
	 * @dataProvider providerRgb2hslColor
	 * @testdox rgb2hsl $input_r,$input_g,$input_b will be $expected [$_dataName]
	 *
	 * @param integer $input_r
	 * @param integer $input_g
	 * @param integer $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testRgb2hsl(int $input_r, int $input_g, int $input_b, array|bool $expected): void
	{
		if ($expected === false) {
			$this->expectException(\LengthException::class);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::rgb2hsl($input_r, $input_g, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hsl2rgb
	 * @dataProvider providerHsl2rgbColor
	 * @testdox hsl2rgb $input_h,$input_s,$input_l will be $expected [$_dataName]
	 *
	 * @param integer|float $input_h
	 * @param integer $input_s
	 * @param integer $input_l
	 * @param array|bool $expected
	 * @return void
	 */
	public function testHsl2rgb(int|float $input_h, float $input_s, float $input_l, array|bool $expected): void
	{
		if ($expected === false) {
			$this->expectException(\LengthException::class);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::hsl2rgb($input_h, $input_s, $input_l)
		);
	}

	/**
	 * edge case check hsl/hsb and hue 360 (= 0)
	 *
	 * @covers ::hsl2rgb
	 * @covers ::hsb2rgb
	 * @testdox hsl2rgb/hsb2rgb hue 360 valid check
	 *
	 * @return void
	 */
	public function testHslHsb360hue(): void
	{
		$this->assertIsArray(
			\CoreLibs\Convert\Colors::hsl2rgb(360.0, 90.5, 41.2),
			'HSL to RGB with 360 hue'
		);
		$this->assertIsArray(
			\CoreLibs\Convert\Colors::hsb2rgb(360, 95, 78.0),
			'HSB to RGB with 360 hue'
		);
	}
}

// __END__
