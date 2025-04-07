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
			'hash tests' => [
				// this is the string
				'text' => 'Some String Text',
				// hash list special
				'crc32b_reverse' => 'c5c21d91', // crc32b (in revere)
				'sha1Short' => '4d2bc9ba0', // sha1Short
				// via hash
				'crc32b' => '911dc2c5', // hash: crc32b
				'adler32' => '31aa05f1', // hash: alder32
				'fnv132' => '9df444f9', // hash: fnv132
				'fnv1a32' => '2c5f91b9', // hash: fnv1a32
				'joaat' => '50dab846', // hash: joaat
				'ripemd160' => 'aeae3f041b20136451519edd9361570909300342', // hash: ripemd160,
				'sha256' => '9055080e022f224fa835929b80582b3c71c672206fa3a49a87412c25d9d42ceb', // hash: sha256
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
			foreach ([null, 'crc32b', 'adler32', 'fnv132', 'fnv1a32', 'joaat', 'ripemd160', 'sha256'] as $_hash_type) {
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
	 * @return array
	 */
	public function hashStandardProvider(): array
	{
		$hash_source = 'Some String Text';
		return [
			'Long Hash check: ' . \CoreLibs\Create\Hash::STANDARD_HASH => [
				$hash_source,
				hash(\CoreLibs\Create\Hash::STANDARD_HASH, $hash_source)
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
	 * phpcs:disable Generic.Files.LineLength
	 * @covers ::__sha1Short
	 * @covers ::__crc32b
	 * @covers ::sha1Short
	 * @dataProvider sha1ShortProvider
	 * @testdox __sha1Short/__crc32b/sha1short $input will be $expected (crc32b) and $expected_sha1 (sha1 short) [$_dataName]
	 * phpcs:enable Generic.Files.LineLength
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
			\CoreLibs\Create\Hash::__sha1Short($input),
			'__sha1Short depreacted'
		);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::__sha1Short($input, false),
			'__sha1Short (false) depreacted'
		);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::__crc32b($input),
			'__crc32b'
		);
		// sha1 type
		$this->assertEquals(
			$expected_sha1,
			\CoreLibs\Create\Hash::__sha1Short($input, true),
			'__sha1Short (true) depreacted'
		);
		$this->assertEquals(
			$expected_sha1,
			\CoreLibs\Create\Hash::sha1Short($input),
			'sha1Short'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__hash
	 * @covers ::hashShort
	 * @covers ::hashShort
	 * @dataProvider hashProvider
	 * @testdox __hash/hashShort/hash $input with $hash_type will be $expected [$_dataName]
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
				\CoreLibs\Create\Hash::__hash($input),
				'__hash'
			);
			$this->assertEquals(
				$expected,
				\CoreLibs\Create\Hash::hashShort($input),
				'hashShort'
			);
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Create\Hash::__hash($input, $hash_type),
				'__hash with hash type'
			);
			$this->assertEquals(
				$expected,
				\CoreLibs\Create\Hash::hash($input, $hash_type),
				'hash with hash type'
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::__hashLong
	 * @covers ::hashLong
	 * @dataProvider hashLongProvider
	 * @testdox __hashLong/hashLong $input will be $expected [$_dataName]
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
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::hashLong($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hash
	 * @covers ::hashStd
	 * @dataProvider hashStandardProvider
	 * @testdox hash/hashStd $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testHashStandard(string $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::hashStd($input)
		);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::hash($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hash
	 * @testdox hash with invalid type
	 *
	 * @return void
	 */
	public function testInvalidHashType(): void
	{
		$hash_source = 'Some String Text';
		$expected = hash(\CoreLibs\Create\Hash::STANDARD_HASH, $hash_source);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::hash($hash_source, 'DOES_NOT_EXIST')
		);
	}

	/**
	 * Note: this only tests default sha256
	 *
	 * @covers ::hashHmac
	 * @testdox hash hmac test
	 *
	 * @return void
	 */
	public function testHashMac(): void
	{
		$hash_key = 'FIX KEY';
		$hash_source = 'Some String Text';
		$expected = '16479b3ef6fa44e1cdd8b2dcfaadf314d1a7763635e8738f1e7996d714d9b6bf';
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::hashHmac($hash_source, $hash_key)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::hashHmac
	 * @testdox hash hmac with invalid type
	 *
	 * @return void
	 */
	public function testInvalidHashMacType(): void
	{
		$hash_key = 'FIX KEY';
		$hash_source = 'Some String Text';
		$expected = hash_hmac(\CoreLibs\Create\Hash::STANDARD_HASH, $hash_source, $hash_key);
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Hash::hashHmac($hash_source, $hash_key, 'DOES_NOT_EXIST')
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array<mixed>
	 */
	public function providerHashTypes(): array
	{
		return [
			'Hash crc32b' => [
				'crc32b',
				true,
				false,
			],
			'Hash adler32' => [
				'adler32',
				true,
				false,
			],
			'HAsh fnv132' => [
				'fnv132',
				true,
				false,
			],
			'Hash fnv1a32' => [
				'fnv1a32',
				true,
				false,
			],
			'Hash: joaat' => [
				'joaat',
				true,
				false,
			],
			'Hash: ripemd160' => [
				'ripemd160',
				true,
				true,
			],
			'Hash: sha256' => [
				'sha256',
				true,
				true,
			],
			'Hash: invalid' => [
				'invalid',
				false,
				false
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::isValidHashType
	 * @covers ::isValidHashHmacType
	 * @dataProvider providerHashTypes
	 * @testdox check if $hash_type is valid for hash $hash_ok and hash hmac $hash_hmac_ok [$_dataName]
	 *
	 * @param  string $hash_type
	 * @param  bool   $hash_ok
	 * @param  bool   $hash_hmac_ok
	 * @return void
	 */
	public function testIsValidHashAndHashHmacTypes(string $hash_type, bool $hash_ok, bool $hash_hmac_ok): void
	{
		$this->assertEquals(
			$hash_ok,
			\CoreLibs\Create\Hash::isValidHashType($hash_type),
			'hash valid'
		);
		$this->assertEquals(
			$hash_hmac_ok,
			\CoreLibs\Create\Hash::isValidHashHmacType($hash_type),
			'hash hmac valid'
		);
	}
}

// __END__
