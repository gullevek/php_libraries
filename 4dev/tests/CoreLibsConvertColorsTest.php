<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\Colors
 * @coversDefaultClass \CoreLibs\Convert\Colors
 * @testdox \CoreLibs\Convert\Colors method tests
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
	public function rgb2hexColorProvider(): array
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
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function hex2rgbColorProvider(): array
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
			'invalid color' => [
				'rgb' => [-12, 300, 12],
				'hsb' => [-12, 300, 12],
				'hsl' => [-12, 300, 12],
				'valid' => false,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function rgb2hsbColorProvider(): array
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
	public function hsb2rgbColorProvider(): array
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
	public function rgb2hslColorProvider(): array
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
	public function hsl2rgbColorProvider(): array
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
	 * @dataProvider rgb2hexColorProvider
	 * @testdox rgb2hex $input_r,$input_g,$input_b will be $expected [$_dataName]
	 *
	 * @param int $input_r
	 * @param int $input_g
	 * @param int $input_b
	 * @param string|bool $expected
	 * @return void
	 */
	public function testRgb2hex(int $input_r, int $input_g, int $input_b, $expected_hash, $expected)
	{
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
	 * @dataProvider hex2rgbColorProvider
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
		$expected,
		$expected_str,
		string $separator,
		$expected_str_sep
	): void {
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
	 * @dataProvider rgb2hsbColorProvider
	 * @testdox rgb2hsb $input_r,$input_g,$input_b will be $expected [$_dataName]
	 *
	 * @param integer $input_r
	 * @param integer $input_g
	 * @param integer $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testRgb2hsb(int $input_r, int $input_g, int $input_b, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::rgb2hsb($input_r, $input_g, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hsb2rgb
	 * @dataProvider hsb2rgbColorProvider
	 * @testdox hsb2rgb $input_h,$input_s,$input_b will be $expected [$_dataName]
	 *
	 * @param float $input_h
	 * @param float $input_s
	 * @param float $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testHsb2rgb(float $input_h, float $input_s, float $input_b, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::hsb2rgb($input_h, $input_s, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::rgb2hsl
	 * @dataProvider rgb2hslColorProvider
	 * @testdox rgb2hsl $input_r,$input_g,$input_b will be $expected [$_dataName]
	 *
	 * @param integer $input_r
	 * @param integer $input_g
	 * @param integer $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testRgb2hsl(int $input_r, int $input_g, int $input_b, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Colors::rgb2hsl($input_r, $input_g, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hsl2rgb
	 * @dataProvider hsl2rgbColorProvider
	 * @testdox hsl2rgb $input_h,$input_s,$input_l will be $expected [$_dataName]
	 *
	 * @param integer|float $input_h
	 * @param integer $input_s
	 * @param integer $input_l
	 * @param array|bool $expected
	 * @return void
	 */
	public function testHsl2rgb($input_h, float $input_s, float $input_l, $expected): void
	{
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
		$this->assertNotFalse(
			\CoreLibs\Convert\Colors::hsl2rgb(360.0, 90.5, 41.2),
			'HSL to RGB with 360 hue'
		);
		$this->assertNotFalse(
			\CoreLibs\Convert\Colors::hsb2rgb(360, 95, 78.0),
			'HSB to RGB with 360 hue'
		);
	}
}

// __END__
