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
	public function randomKeyGenProvider(): array
	{
		return [
			// just key length
			'default key length, default char set' => [
				0 => null,
				1 => \CoreLibs\Create\RandomKey::KEY_LENGTH_DEFAULT
			],
			'set -1 key length, default char set' => [
				0 => -1,
				1 => \CoreLibs\Create\RandomKey::KEY_LENGTH_DEFAULT,
			],
			'set 0 key length, default char set' => [
				0 => -1,
				1 => \CoreLibs\Create\RandomKey::KEY_LENGTH_DEFAULT,
			],
			'set too large key length, default char set' => [
				0 => 300,
				1 => \CoreLibs\Create\RandomKey::KEY_LENGTH_DEFAULT,
			],
			'set override key lenght, default char set' => [
				0 => 6,
				1 => 6,
			],
			// just character set
			'default key length, different char set A' => [
				0 => \CoreLibs\Create\RandomKey::KEY_LENGTH_DEFAULT,
				1 => \CoreLibs\Create\RandomKey::KEY_LENGTH_DEFAULT,
				2 => [
					'A', 'B', 'C'
				],
			],
			'different key length, different char set B' => [
				0 => 16,
				1 => 16,
				2 => [
					'A', 'B', 'C'
				],
				3 => [
					'1', '2', '3'
				]
			],
		];
	}

	// Alternative more efficient version using strpos
	/**
	 * check if all characters are in set
	 *
	 * @param  string $input
	 * @param  string $allowed_chars
	 * @return bool
	 */
	private function allCharsInSet(string $input, string $allowed_chars): bool
	{
		$inputLength = strlen($input);

		for ($i = 0; $i < $inputLength; $i++) {
			if (strpos($allowed_chars, $input[$i]) === false) {
				return false;
			}
		}

		return true;
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
	public function testRandomKeyGen(?int $input, int $expected, array ...$key_range): void
	{
		$__key_data = \CoreLibs\Create\RandomKey::KEY_CHARACTER_RANGE_DEFAULT;
		if (count($key_range)) {
			$__key_data = join('', array_unique(array_merge(...$key_range)));
		}
		if ($input === null) {
			$this->assertEquals(
				$expected,
				strlen(\CoreLibs\Create\RandomKey::randomKeyGen())
			);
		} elseif ($input !== null && !count($key_range)) {
			$random_key = \CoreLibs\Create\RandomKey::randomKeyGen($input);
			$this->assertTrue(
				$this->allCharsInSet($random_key, $__key_data),
				'Characters not valid'
			);
			$this->assertEquals(
				$expected,
				strlen($random_key),
				'String length not matching'
			);
		} elseif (count($key_range)) {
			$random_key = \CoreLibs\Create\RandomKey::randomKeyGen($input, ...$key_range);
			$this->assertTrue(
				$this->allCharsInSet($random_key, $__key_data),
				'Characters not valid'
			);
			$this->assertEquals(
				$expected,
				strlen($random_key),
				'String length not matching'
			);
		}
	}
}

// __END__
