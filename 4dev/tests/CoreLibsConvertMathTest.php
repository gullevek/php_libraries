<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Undocumented class
 * @coversDefaultClass \CoreLibs\Convert\Math
 * @testdox \CoreLibs\Convert\Math method tests
 */
final class CoreLibsConvertMathTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array<mixed>
	 */
	public function fceilProvider(): array
	{
		return [
			'5.5 must be 6' => [5.5, 6],
			'5.1234567890 with 5 must be 6' => [5.1234567890, 6],
			'6 must be 6' => [6, 6]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::fceil
	 * @dataProvider fceilProvider
	 * @testdox fceil: Input $input must be $expected
	 *
	 * @param float $input
	 * @param int $expected
	 * @return void
	 */
	public function testMathFceilValue(float $input, int $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Math::fceil($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array<mixed>
	 */
	public function floorProvider(): array
	{
		return [
			'5123456 with -3 must be 5123000' => [5123456, -3, 5123000],
			'5123456 with -10 must be 5000000' => [5123456, -10, 5000000]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::floorp
	 * @dataProvider floorProvider
	 * @testdox floor: Input $input with cutoff $cutoff must be $expected
	 *
	 * @param int $input
	 * @param int $cutoff
	 * @param int $expected
	 * @return void
	 */
	public function testMathFloorValue(int $input, int $cutoff, int $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Math::floorp($input, $cutoff)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array<mixed>
	 */
	public function initNumericProvider(): array
	{
		return [
			'5 must be 5' => [5, 5, 'int'],
			'5.123 must be 5.123' => [5.123, 5.123, 'float'],
			"'5' must be 5" => ['5', 5, 'string'],
			"'5.123' must be 5.123" => ['5.123', 5.123, 'string'],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::initNumeric
	 * @dataProvider initNumericProvider
	 * @testdox initNumeric: Input $info $input must match $expected [$_dataName]
	 *
	 * @param int|float|string $input
	 * @param float $expected
	 * @param string $info
	 * @return void
	 */
	public function testMathInitNumericValue($input, float $expected, string $info): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Math::initNumeric($input)
		);
	}
}

// __END__
