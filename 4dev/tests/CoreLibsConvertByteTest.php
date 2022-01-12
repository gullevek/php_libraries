<?php

// because we have long testdox lines
// phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\Byte
 * @coversDefaultClass \CoreLibs\Convert\Byte
 * @testdox \CoreLibs\Convert\Byte method tests
 */
final class CoreLibsConvertByteTest extends TestCase
{

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function byteProvider(): array
	{
		return [
			'negative number' => [
				0 => -123123123,
				1 => '-117.42 MB',
				2 => '-123.12 MiB',
				3 => '-117.42MB',
				4 => '-117.42 MB',
				5 => '-123.12MiB',
			],
			'kilobyte minus one' => [
				0 => 999999, // KB-1
				1 => '976.56 KB',
				2 => '1 MiB',
				3 => '976.56KB',
				4 => '976.56 KB',
				5 => '1MiB',
			],
			'megabyte minus one' => [
				0 => 999999999, // MB-1
				1 => '953.67 MB',
				2 => '1 GiB',
				3 => '953.67MB',
				4 => '953.67 MB',
				5 => '1GiB',
			],
			'megabyte' => [
				0 => 254779258,
				1 => '242.98 MB',
				2 => '254.78 MiB',
				3 => '242.98MB',
				4 => '242.98 MB',
				5 => '254.78MiB',
			],
			'terabyte minus one' => [
				0 => 999999999999999, // TB-1
				1 => '909.49 TB',
				2 => '1 PiB',
				3 => '909.49TB',
				4 => '909.49 TB',
				5 => '1PiB',
			],
			'terabyte' => [
				0 => 588795544887632, // TB-n
				1 => '535.51 TB',
				2 => '588.8 TiB',
				3 => '535.51TB',
				4 => '535.51 TB',
				5 => '588.8TiB',
			],
			'petabyte minus one' => [
				0 => 999999999999999999, // PB-1
				1 => '888.18 PB',
				2 => '1 EiB',
				3 => '888.18PB',
				4 => '888.18 PB',
				5 => '1EiB',
			],
			'max int value' => [
				0 => 9223372036854775807, // MAX INT
				1 => '8 EB',
				2 => '9.22 EiB',
				3 => '8EB',
				4 => '8.00 EB',
				5 => '9.22EiB',
			],
			'exabyte minus 1' => [
				0 => 999999999999999999999, // EB-1
				1 => '867.36 EB',
				2 => '1000 EiB',
				3 => '867.36EB',
				4 => '867.36 EB',
				5 => '1000EiB',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function byteStringProvider(): array
	{
		return [
			'negative number' => [
				0 => '-117.42 MB',
				1 => -123123794,
				2 => -117420000,
			],
			'megabyte' => [
				0 => '242.98 MB',
				1 => 254782996,
				2 => 242980000
			],
			'megabyte si' => [
				0 => '254.78 MiB',
				1 => 267156193,
				2 => 254780000
			],
			'petabyte' => [
				0 => '1 EiB',
				1 => 1152921504606846976,
				2 => 1000000000000000000,
			],
			'max int' => [
				0 => '8 EB',
				1 => -9223372036854775807 - 1,
				2 => 8000000000000000000,
			],
			'exabyte, overflow' => [
				0 => '867.36EB',
				1 => 3873816255479021568,
				2 => 363028535651074048,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::humanReadableByteFormat
	 * @dataProvider byteProvider
	 * @testdox humanReadableByteFormat $input will be $expected, $expected_si SI, $expected_no_space no space, $expected_adjust adjust, $expected_si_no_space SI/no space [$_dataName]
	 *
	 * @param string|int|float $input
	 * @param string $expected
	 * @param string $expected_si
	 * @param string $expected_no_space
	 * @param string $expected_adjust
	 * @param string $expected_si_no_space
	 * @return void
	 */
	public function testHumanReadableByteFormat(
		$input,
		string $expected,
		string $expected_si,
		string $expected_no_space,
		string $expected_adjust,
		string $expected_si_no_space
	): void {
		// 1024
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Byte::humanReadableByteFormat($input)
		);
		// 1000
		$this->assertEquals(
			$expected_si,
			\CoreLibs\Convert\Byte::humanReadableByteFormat($input, \CoreLibs\Convert\Byte::BYTE_FORMAT_SI)
		);
		// no space
		$this->assertEquals(
			$expected_no_space,
			\CoreLibs\Convert\Byte::humanReadableByteFormat($input, \CoreLibs\Convert\Byte::BYTE_FORMAT_NOSPACE)
		);
		// always 2 decimals
		$this->assertEquals(
			$expected_adjust,
			\CoreLibs\Convert\Byte::humanReadableByteFormat($input, \CoreLibs\Convert\Byte::BYTE_FORMAT_ADJUST)
		);
		// combined si + no space
		$this->assertEquals(
			$expected_si_no_space,
			\CoreLibs\Convert\Byte::humanReadableByteFormat(
				$input,
				\CoreLibs\Convert\Byte::BYTE_FORMAT_SI | \CoreLibs\Convert\Byte::BYTE_FORMAT_NOSPACE
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::stringByteFormat
	 * @dataProvider byteStringProvider
	 * @testdox stringByteFormat $input will be $expected and $expected_si SI [$_dataName]
	 *
	 * @param string|int|float $input
	 * @param string|int|float $expected
	 * @param string|int|float $expected_si
	 * @return void
	 */
	public function testStringByteFormat($input, $expected, $expected_si): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Byte::stringByteFormat($input)
		);
		$this->assertEquals(
			$expected_si,
			\CoreLibs\Convert\Byte::stringByteFormat($input, \CoreLibs\Convert\Byte::BYTE_FORMAT_SI)
		);
	}
}

// __END__
