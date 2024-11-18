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
	public function providerFceil(): array
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
	 * @dataProvider providerFceil
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
	public function providerFloor(): array
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
	 * @dataProvider providerFloor
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
	public function providerInitNumeric(): array
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
	 * @dataProvider providerInitNumeric
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

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerCbrt(): array
	{
		return [
			'cube root of 2' => [2, 1.25992, 5],
			'cube root of 3' => [3, 1.44225, 5],
			'cube root of -1' => [-1, 'NAN', 0],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::cbrt
	 * @dataProvider providerCbrt
	 * @testdox initNumeric: Input $input must match $expected [$_dataName]
	 *
	 * @param  float|int $number
	 * @param  float     $expected
	 * @param  int       $round_to
	 * @return void
	 */
	public function testCbrt(float|int $number, float|string $expected, int $round_to): void
	{
		$this->assertEquals(
			$expected,
			round(\CoreLibs\Convert\Math::cbrt($number), $round_to)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerMultiplyMatrices(): array
	{
		return  [
			'[3] x [3] => [3x1]' => [
				[1, 2, 3],
				[1, 2, 3],
				[14]
			],
			'[3] x [3x1]' => [
				[1, 2, 3],
				[[1], [2], [3]],
				[14]
			],
			'[3] x [3x1]' => [
				[1, 2, 3],
				[[1], [2], [3]],
				[14]
			],
			'[1x3L] x [3x1]' => [
				[[1, 2, 3]],
				[[1], [2], [3]],
				[14]
			],
			'[1x3] x [3x1]' => [
				[[1], [2], [3]],
				[[1], [2], [3]],
				[1, 2, 3]
			],
			'[2x3] x [3] => [3x1]' => [
				[
					[1, 2, 3],
					[1, 2, 3]
				],
				[1, 2, 3],
				[
					14,
					14
				]
			],
			'[2x3] x [3x1]' => [
				[
					[1, 2, 3],
					[1, 2, 3]
				],
				[[1], [2], [3]],
				[
					14,
					14
				]
			],
			'[2x3] x [2x3] => [3x3]' => [
				[
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[3, 6, 9],
					[3, 6, 9]
				]
			],
			'[2x3] x [3x3]' => [
				[
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[1, 2, 3],
					[1, 2, 3],
					[0, 0, 0],
				],
				[
					[3, 6, 9],
					[3, 6, 9]
				]
			],
			'[2x3] x [3x2]' => [
				'a' => [
					[1, 2, 3],
					[1, 2, 3],
				],
				'b' => [
					[1, 1],
					[2, 2],
					[3, 3],
				],
				'prod' => [
					[14, 14],
					[14, 14],
				]
			],
			'[3x3] x [3] => [1x3]' => [
				[
					[1, 2, 3],
					[1, 2, 3],
					[1, 2, 3],
				],
				[1, 2, 3],
				[
					14,
					14,
					14
				]
			],
			'[3x3] x [2x3] => [3x3]' => [
				[
					[1, 2, 3],
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[3, 6, 9],
					[3, 6, 9],
					[3, 6, 9],
				]
			],
			'[3x3] x [3x3]' => [
				[
					[1, 2, 3],
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[1, 2, 3],
					[1, 2, 3],
					// [0, 0, 0],
				],
				[
					[3, 6, 9],
					[3, 6, 9],
					[3, 6, 9],
				]
			],
			'[3] x [3x3]' => [
				[1, 2, 3],
				[
					[1, 2, 3],
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[6, 12, 18],
				]
			],
			'[2x3] x [3x3]' => [
				[
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[1, 2, 3],
					[1, 2, 3],
					[1, 2, 3],
				],
				[
					[6, 12, 18],
					[6, 12, 18],
				]
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::multiplyMatrices
	 * @dataProvider providerMultiplyMatrices
	 * @testdox initNumeric: Input $input_a x $input_b must match $expected [$_dataName]
	 *
	 * @param  array $input_a
	 * @param  array $input_b
	 * @param  array $expected
	 * @return void
	 */
	public function testMultiplyMatrices(array $input_a, array $input_b, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Math::multiplyMatrices($input_a, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerEqualWithEpsilon(): array
	{
		return [
			'equal' => [
				'a' => 0.000000000000000222,
				'b' => 0.000000000000000222,
				'epsilon' => PHP_FLOAT_EPSILON,
				'equal' => true,
			],
			'almost equal' => [
				'a' => 0.000000000000000222,
				'b' => 0.000000000000000232,
				'epsilon' => PHP_FLOAT_EPSILON,
				'equal' => true,
			],
			'not equal' => [
				'a' => 0.000000000000000222,
				'b' => 0.000000000000004222,
				'epsilon' => PHP_FLOAT_EPSILON,
				'equal' => false,
			],
			'equal, different epsilon' => [
				'a' => 0.000000000000000222,
				'b' => 0.000000000000004222,
				'epsilon' => 0.0001,
				'equal' => true,
			],
			'not equal, different epsilon' => [
				'a' => 0.0001,
				'b' => 0.0002,
				'epsilon' => 0.0001,
				'equal' => false,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::equalWithEpsilon
	 * @dataProvider providerEqualWithEpsilon
	 * @testdox equalWithEpsilon with $a and $b and Epsilon: $epsilon must be equal: $equal [$_dataName]
	 *
	 * @return void
	 */
	public function testEqualWithEpsilon(float $a, float $b, float $epsilon, bool $equal): void
	{
		$this->assertEquals(
			$equal,
			\CoreLibs\Convert\Math::equalWithEpsilon($a, $b, $epsilon)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerCompareWithEpsilon(): array
	{
		return [
			'smaller, true' => [
				'value' => 0.0001,
				'compare' => '<',
				'limit' => 0.0002,
				'epsilon' => 0.00001,
				'match' => true,
			],
			'smaller, false' => [
				'value' => 0.0001,
				'compare' => '<',
				'limit' => 0.0001,
				'epsilon' => 0.00001,
				'match' => false,
			],
			'bigger, true' => [
				'value' => 0.0002,
				'compare' => '>',
				'limit' => 0.0001,
				'epsilon' => 0.00001,
				'match' => true,
			],
			'bigger, false' => [
				'value' => 0.0001,
				'compare' => '>',
				'limit' => 0.0001,
				'epsilon' => 0.00001,
				'match' => false,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::compareWithEpsilon
	 * @dataProvider providerCompareWithEpsilon
	 * @testdox compareWithEpsilon $value $compare $limit with $epsilon must match: $match [$_dataName]
	 *
	 * @param  float  $value
	 * @param  string $compare
	 * @param  float  $limit
	 * @param  float  $epslion
	 * @param  bool   $match
	 * @return void
	 */
	public function testCompareWithEpsilon(
		float $value,
		string $compare,
		float $limit,
		float $epsilon,
		bool $match
	): void {
		$this->assertEquals(
			$match,
			\CoreLibs\Convert\Math::compareWithEpsilon($value, $compare, $limit, $epsilon)
		);
	}
}

// __END__
