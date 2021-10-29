<?php

/*
 * Tests for
 * \CoreLibs\Convert\Math
 */

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Undocumented class
 * @testdox CoreLibs\Convert\Math method tests
 */
final class CoreLibsConvertMathTest extends TestCase
{

	/**
	 * Undocumented function
	 *
	 * @return array
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
	 * @dataProvider fceilProvider
	 * @testdox Math::fceil: Input $input must be $expected
	 *
	 * @param int $input
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
	 * @return array
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
	 * @dataProvider floorProvider
	 * @testdox Math::floor: Input $input with cutoff $cutoff must be $expected
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
	 * @return array
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
	 * @dataProvider initNumericProvider
	 * @testdox Math::initNumeric: Input $info $input must match $expected [$_dataName]
	 *
	 * @param int|float|string $input
	 * @param float $expected
	 * @return void
	 */
	public function testMathInitNumericValue($input, float $expected, string $info): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Math::initNumeric($input)
		);
	}

	/**
	 * A testWith sample
	 *
	 * @testdox Math::initNumeric: alternate tests $input => $expected ($info) [$_dataName]
	 * @testWith [123.123, 123.123, "float"]
	 *           ["123.123", 123.123, "string"]
	 *
	 * @param [type] $input
	 * @param float $expected
	 * @param string $info
	 * @return void
	 */
	public function testMathInitNumericValueAlt($input, float $expected, string $info): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Math::initNumeric($input)
		);
	}
}
