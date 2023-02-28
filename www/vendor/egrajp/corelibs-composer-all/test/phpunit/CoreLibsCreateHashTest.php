<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Create\Hash
 * @coversDefaultClass \CoreLibs\Create\Hash
 * @testdox \CoreLibs\Create\Hash method tests
 */
final class CoreLibsCreateHashTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function hashData(): array
	{
		return [
			'any string' => [
				'text' => 'Some String Text',
				'crc32b_reverse' => 'c5c21d91', // crc32b (in revere)
				'sha1Short' => '4d2bc9ba0', // sha1Short
				// via hash
				'crc32b' => '911dc2c5', // hash: crc32b
				'adler32' => '31aa05f1', // hash: alder32
				'fnv132' => '9df444f9', // hash: fnv132
				'fnv1a32' => '2c5f91b9', // hash: fnv1a32
				'joaat' => '50dab846', // hash: joaat
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function crc32bProvider(): array
	{
		$list = [];
		foreach ($this->hashData() as $name => $values) {
			$list[$name . ' to crc32b reverse'] = [
				0 => $values['text'],
				1 => $values['crc32b_reverse'],
			];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function sha1ShortProvider(): array
	{
		$list = [];
		foreach ($this->hashData() as $name => $values) {
			$list[$name . ' to sha1 short'] = [
				0 => $values['text'],
				1 => $values['crc32b_reverse'],
				2 => $values['sha1Short'],
			];
		}
		return $list;
	}

	/**
	 * test all hash functions
	 * NOTE: if we add new hash functions in the __hash method
	 * they need to be added here too (and in the master hashData array too)
	 *
	 * @return array
	 */
	public function hashProvider(): array
	{
		$list = [];
		foreach ($this->hashData() as $name => $values) {
			foreach ([null, 'crc32b', 'adler32', 'fnv132', 'fnv1a32', 'joaat'] as $_hash_type) {
				// default value test
				if ($_hash_type === null) {
					$hash_type = \CoreLibs\Create\Hash::STANDARD_HASH_SHORT;
				} else {
					$hash_type = $_hash_type;
				}
				$list[$name . ' to ' . $hash_type] = [
					0 => $values['text'],
					1 => $_hash_type,
					2 => $values[$hash_type]
				];
			}
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function hashLongProvider(): array
	{
		$hash_source = 'Some String Text';
		return [
			'Long Hash check: ' . \CoreLibs\Create\Hash::STANDARD_HASH_LONG => [
				$hash_source,
				hash(\CoreLibs\Create\Hash::STANDARD_HASH_LONG, $hash_source)
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__crc32b
	 * @dataProvider crc32bProvider
	 * @testdox __crc32b $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testCrc32b(string $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::__crc32b($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__sha1Short
	 * @dataProvider sha1ShortProvider
	 * @testdox __sha1Short $input will be $expected (crc32b) and $expected_sha1 (sha1 short) [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testSha1Short(string $input, string $expected, string $expected_sha1): void
	{
		// uses crc32b
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::__sha1Short($input)
		);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::__sha1Short($input, false)
		);
		// sha1 type
		$this->assertEquals(
			$expected_sha1,
			\CoreLibs\Create\Hash::__sha1Short($input, true)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__hash
	 * @dataProvider hashProvider
	 * @testdox __hash $input with $hash_type will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string|null $hash_type
	 * @param string $expected
	 * @return void
	 */
	public function testHash(string $input, ?string $hash_type, string $expected): void
	{
		if ($hash_type === null) {
			$this->assertEquals(
				$expected,
				\CoreLibs\Create\Hash::__hash($input)
			);
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Create\Hash::__hash($input, $hash_type)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__hashLong
	 * @dataProvider hashLongProvider
	 * @testdox __hashLong $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testHashLong(string $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::__hashLong($input)
		);
	}
}

// __END__
