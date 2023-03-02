<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Create\RandomKey
 * @coversDefaultClass \CoreLibs\Create\RandomKey
 * @testdox \CoreLibs\Create\RandomKey method tests
 */
final class CoreLibsCreateRandomKeyTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function keyLenghtProvider(): array
	{
		return [
			'valid key length' => [
				0 => 6,
				1 => true,
				2 => 6,
			],
			'negative key length' => [
				0 => -1,
				1 => false,
				2 => 4,
			],
			'tpp big key length' => [
				0 => 300,
				1 => false,
				2 => 4,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function randomKeyGenProvider(): array
	{
		return [
			'default key length' => [
				0 => null,
				1 => 4
			],
			'set -1 key length default' => [
				0 => -1,
				1 => 4,
			],
			'set too large key length' => [
				0 => 300,
				1 => 4,
			],
			'set override key lenght' => [
				0 => 6,
				1 => 6,
			],
		];
	}

	/**
	 * 1
	 *
	 * @return array
	 */
	public function keepKeyLengthProvider(): array
	{
		return [
			'set too large' => [
				0 => 6,
				1 => 300,
				2 => 6,
			],
			'set too small' => [
				0 => 8,
				1 => -2,
				2 => 8,
			],
			'change valid' => [
				0 => 10,
				1 => 6,
				2 => 6,
			]
		];
	}

	/**
	 * run before each test and reset to default 4
	 *
	 * @before
	 *
	 * @return void
	 */
	public function resetKeyLength(): void
	{
		\CoreLibs\Create\RandomKey::setRandomKeyLength(4);
	}

	/**
	 * check that first length is 4
	 *
	 * @covers ::getRandomKeyLength
	 * @testWith [4]
	 * @testdox getRandomKeyLength on init will be $expected [$_dataName]
	 *
	 * @param integer $expected
	 * @return void
	 */
	public function testGetRandomKeyLengthInit(int $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\RandomKey::getRandomKeyLength()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setRandomKeyLength
	 * @covers ::getRandomKeyLength
	 * @dataProvider keyLenghtProvider
	 * @testdox setRandomKeyLength $input will be $expected, compare to $compare [$_dataName]
	 *
	 * @param integer $input
	 * @param boolean $expected
	 * @param integer $compare
	 * @return void
	 */
	public function testSetRandomKeyLength(int $input, bool $expected, int $compare): void
	{
		// set
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\RandomKey::setRandomKeyLength($input)
		);
		// read test, if false, use compare check
		if ($expected === false) {
			$input = $compare;
		}
		$this->assertEquals(
			$input,
			\CoreLibs\Create\RandomKey::getRandomKeyLength()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::randomKeyGeyn
	 * @dataProvider randomKeyGenProvider
	 * @testdox randomKeyGen use $input key length $expected [$_dataName]
	 *
	 * @param integer|null $input
	 * @param integer $expected
	 * @return void
	 */
	public function testRandomKeyGen(?int $input, int $expected): void
	{
		if ($input === null) {
			$this->assertEquals(
				$expected,
				strlen(\CoreLibs\Create\RandomKey::randomKeyGen())
			);
		} else {
			$this->assertEquals(
				$expected,
				strlen(\CoreLibs\Create\RandomKey::randomKeyGen($input))
			);
		}
	}

	/**
	 * Check that if set to n and then invalid, it keeps the previous one
	 * or if second change valid, second will be shown
	 *
	 * @covers ::setRandomKeyLength
	 * @dataProvider keepKeyLengthProvider
	 * @testdox keep setRandomKeyLength set with $input_valid and then $input_invalid will be $expected [$_dataName]
	 *
	 * @param integer $input_valid
	 * @param integer $input_invalid
	 * @param integer $expected
	 * @return void
	 */
	public function testKeepKeyLength(int $input_valid, int $input_invalid, int $expected): void
	{
		\CoreLibs\Create\RandomKey::setRandomKeyLength($input_valid);
		\CoreLibs\Create\RandomKey::setRandomKeyLength($input_invalid);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\RandomKey::getRandomKeyLength()
		);
	}
}

// __END__
