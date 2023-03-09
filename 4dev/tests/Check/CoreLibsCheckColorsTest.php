<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Check\Colors
 * @coversDefaultClass \CoreLibs\Check\Colors
 * @testdox \CoreLibs\Check\Colors method tests
 */
final class CoreLibsCheckColorsTest extends TestCase
{
	public function validateColorProvider(): array
	{
		/*
		0: input color string
		1: flag (or flags to set)
		2: expected result (bool)
		*/
		return [
			// * hex
			'valid hex rgb, flag ALL (default)' => [
				'#ab12cd',
				null,
				true,
			],
			'valid hex rgb, flag ALL' => [
				'#ab12cd',
				\CoreLibs\Check\Colors::ALL,
				true,
			],
			'valid hex rgb, flag HEX_RGB' => [
				'#ab12cd',
				\CoreLibs\Check\Colors::HEX_RGB,
				true,
			],
			'valid hex rgb, wrong flag' => [
				'#ab12cd',
				\CoreLibs\Check\Colors::RGB,
				false,
			],
			// error
			'invalid hex rgb A' => [
				'#ab12zz',
				null,
				false,
			],
			'invalid hex rgb B' => [
				'#ZyQfo',
				null,
				false,
			],
			// other valid hex checks
			'valid hex rgb, alt　A' => [
				'#AB12cd',
				null,
				true,
			],
			// * hax alpha
			'valid hex rgb alpha, flag ALL (default)' => [
				'#ab12cd12',
				null,
				true,
			],
			'valid hex rgb alpha, flag ALL' => [
				'#ab12cd12',
				\CoreLibs\Check\Colors::ALL,
				true,
			],
			'valid hex rgb alpha, flag HEX_RGBA' => [
				'#ab12cd12',
				\CoreLibs\Check\Colors::HEX_RGBA,
				true,
			],
			'valid hex rgb alpha, wrong flag' => [
				'#ab12cd12',
				\CoreLibs\Check\Colors::RGB,
				false,
			],
			// error
			'invalid hex rgb alpha A' => [
				'#ab12dd1',
				null,
				false,
			],
			'invalid hex rgb alpha B' => [
				'#ab12ddzz',
				null,
				false,
			],
			'valid hex rgb alpha, alt A' => [
				'#ab12cdEE',
				null,
				true,
			],
			// * rgb
			'valid rgb, flag ALL (default)' => [
				'rgb(255, 10, 20)',
				null,
				true,
			],
			'valid rgb, flag ALL' => [
				'rgb(255, 10, 20)',
				\CoreLibs\Check\Colors::ALL,
				true,
			],
			'valid rgb, flag RGB' => [
				'rgb(255, 10, 20)',
				\CoreLibs\Check\Colors::RGB,
				true,
			],
			'valid rgb, wrong flag' => [
				'rgb(255, 10, 20)',
				\CoreLibs\Check\Colors::HEX_RGB,
				false,
			],
			// error
			'invalid rgb A' => [
				'rgb(356, 10, 20)',
				null,
				false,
			],
			// other valid rgb conbinations
			'valid rgb, alt A (percent)' => [
				'rgb(100%, 10%, 20%)',
				null,
				true,
			],
			// TODO check all % and non percent combinations
			'valid rgb, alt B (percent, mix)' => [
				'rgb(100%, 10, 40)',
				null,
				true,
			],
			// * rgb alpha
			'valid rgba, flag ALL (default)' => [
				'rgba(255, 10, 20, 0.5)',
				null,
				true,
			],
			'valid rgba, flag ALL' => [
				'rgba(255, 10, 20, 0.5)',
				\CoreLibs\Check\Colors::ALL,
				true,
			],
			'valid rgba, flag RGB' => [
				'rgba(255, 10, 20, 0.5)',
				\CoreLibs\Check\Colors::RGBA,
				true,
			],
			'valid rgba, wrong flag' => [
				'rgba(255, 10, 20, 0.5)',
				\CoreLibs\Check\Colors::HEX_RGB,
				false,
			],
			// error
			'invalid rgba A' => [
				'rgba(356, 10, 20, 0.5)',
				null,
				false,
			],
			// other valid rgba combinations
			'valid rgba, alt A (percent)' => [
				'rgba(100%, 10%, 20%, 0.5)',
				null,
				true,
			],
			// TODO check all % and non percent combinations
			'valid rgba, alt B (percent, mix)' => [
				'rgba(100%, 10, 40, 0.5)',
				null,
				true,
			],
			// TODO check all % and non percent combinations with percent transparent
			'valid rgba, alt C (percent transparent)' => [
				'rgba(100%, 10%, 20%, 50%)',
				null,
				true,
			],
					/*
	// hsl
	'hsl(100, 50%, 60%)',
	'hsl(100, 50.5%, 60.5%)',
	'hsla(100, 50%, 60%)',
	'hsla(100, 50.5%, 60.5%)',
	'hsla(100, 50%, 60%, 0.5)',
	'hsla(100, 50.5%, 60.5%, 0.5)',
	'hsla(100, 50%, 60%, 50%)',
	'hsla(100, 50.5%, 60.5%, 50%)',
		*/
			// * hsl
			'valid hsl, flag ALL (default)' => [
				'hsl(100, 50%, 60%)',
				null,
				true,
			],
			'valid hsl, flag ALL' => [
				'hsl(100, 50%, 60%)',
				\CoreLibs\Check\Colors::ALL,
				true,
			],
			'valid hsl, flag RGB' => [
				'hsl(100, 50%, 60%)',
				\CoreLibs\Check\Colors::HSL,
				true,
			],
			'valid hsl, wrong flag' => [
				'hsl(100, 50%, 60%)',
				\CoreLibs\Check\Colors::HEX_RGB,
				false,
			],
			'invalid hsl A' => [
				'hsl(500, 50%, 60%)',
				null,
				false,
			],
			'valid hsl, alt A' => [
				'hsl(100, 50.5%, 60.5%)',
				null,
				true,
			],
			// * hsl alpha
			'valid hsla, flag ALL (default)' => [
				'hsla(100, 50%, 60%, 0.5)',
				null,
				true,
			],
			'valid hsla, flag ALL' => [
				'hsla(100, 50%, 60%, 0.5)',
				\CoreLibs\Check\Colors::ALL,
				true,
			],
			'valid hsla, flag RGB' => [
				'hsla(100, 50%, 60%, 0.5)',
				\CoreLibs\Check\Colors::HSLA,
				true,
			],
			'valid hsla, wrong flag' => [
				'hsla(100, 50%, 60%, 0.5)',
				\CoreLibs\Check\Colors::HEX_RGB,
				false,
			],
			'invalid hsla A' => [
				'hsla(500, 50%, 60%, 0.5)',
				null,
				false,
			],
			'valid hsla, alt A (percent alpha' => [
				'hsla(100, 50%, 60%, 50%)',
				null,
				true,
			],
			'valid hsla, alt A (percent alpha' => [
				'hsla(100, 50.5%, 60.5%, 50%)',
				null,
				true,
			],
			// * combined flag checks
			'valid rgb, flag RGB|RGBA' => [
				'rgb(100%, 10%, 20%)',
				\CoreLibs\Check\Colors::RGB | \CoreLibs\Check\Colors::RGBA,
				true,
			],
			// TODO other combined flag checks all combinations
			// * invalid string
			'invalid string A' => [
				'invalid string',
				null,
				false,
			],
			'invalid string B' => [
				'(hsla(100, 100, 100))',
				null,
				false,
			],
			'invalid string C' => [
				'hsla(100, 100, 100',
				null,
				false,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::validateColor
	 * @dataProvider validateColorProvider
	 * @testdox validateColor $input with flags $flags be $expected [$_dataName]
	 *
	 * @param  string   $input
	 * @param  int|null $flags
	 * @param  bool     $expected
	 * @return void
	 */
	public function testValidateColor(string $input, ?int $flags, bool $expected)
	{
		if ($flags === null) {
			$result = \CoreLibs\Check\Colors::validateColor($input);
		} else {
			$result = \CoreLibs\Check\Colors::validateColor($input, $flags);
		}
		$this->assertEquals(
			$expected,
			$result
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::validateColor
	 * @testWith [99]
	 * @testdox Check Exception throw for $flag
	 *
	 * @param  int   $flag
	 * @return void
	 */
	public function testValidateColorException(int $flag): void
	{
		$this->expectException(\Exception::class);
		\CoreLibs\Check\Colors::validateColor('#ffffff', $flag);
	}
}

// __END__
