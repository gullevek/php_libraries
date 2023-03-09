<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Create\Uids
 * @coversDefaultClass \CoreLibs\Create\Uids
 * @testdox \CoreLibs\Create\Uids method tests
 */
final class CoreLibsCreateUidsTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function uniqIdProvider(): array
	{
		return [
			'md5 hash' => [
				0 => 'md5',
				1 => 32,
			],
			'sha256 hash' => [
				0 => 'sha256',
				1 => 64
			],
			'ripemd160 hash' => [
				0 => 'ripemd160',
				1 => 40
			],
			'adler32 hash' => [
				0 => 'adler32',
				1 => 8
			],
			'not in list hash but valid' => [
				0 => 'sha3-512',
				1 => strlen(hash('sha3-512', 'A'))
			],
			'default hash not set' => [
				0 => null,
				1 => 64,
			],
			'invalid name' => [
				0 => 'iamnotavalidhash',
				1 => 64,
			],
			'auto: ' . \CoreLibs\Create\Uids::DEFAULT_HASH => [
				0 => \CoreLibs\Create\Uids::DEFAULT_HASH,
				1 => strlen(hash(\CoreLibs\Create\Uids::DEFAULT_HASH, 'A'))
			],
			'auto: ' . \CoreLibs\Create\Uids::STANDARD_HASH_LONG => [
				0 => \CoreLibs\Create\Uids::STANDARD_HASH_LONG,
				1 => strlen(hash(\CoreLibs\Create\Uids::STANDARD_HASH_LONG, 'A'))
			],
			'auto: ' . \CoreLibs\Create\Uids::STANDARD_HASH_SHORT => [
				0 => \CoreLibs\Create\Uids::STANDARD_HASH_SHORT,
				1 => strlen(hash(\CoreLibs\Create\Uids::STANDARD_HASH_SHORT, 'A'))
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function uniqIdLongProvider(): array
	{
		return [
			'uniq id long: ' . \CoreLibs\Create\Uids::STANDARD_HASH_LONG => [
				strlen(hash(\CoreLibs\Create\Uids::STANDARD_HASH_LONG, 'A'))
			],
		];
	}


	/**
	 * must match 7e78fe0d-59b8-4637-af7f-e88d221a7d1e
	 *
	 * @covers ::uuidv4
	 * @testdox uuidv4 check that return is matching regex [$_dataName]
	 *
	 * @return void
	 */
	public function testUuidv4(): void
	{
		$uuid = \CoreLibs\Create\Uids::uuidv4();
		$this->assertMatchesRegularExpression(
			'/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/',
			$uuid
		);
		// $this->assertStringMatchesFormat(
		// 	'%4s%4s-%4s-%4s-%4s-%4s%4s%4s',
		// 	$uuid
		// );
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::uniqId
	 * @dataProvider uniqIdProvider
	 * @testdox uniqId $input will be length $expected [$_dataName]
	 *
	 * @param string|null $input
	 * @param string $expected
	 * @return void
	 */
	public function testUniqId(?string $input, int $expected): void
	{
		if ($input === null) {
			$this->assertEquals(
				$expected,
				strlen(\CoreLibs\Create\Uids::uniqId())
			);
		} else {
			$this->assertEquals(
				$expected,
				strlen(\CoreLibs\Create\Uids::uniqId($input))
			);
		}
	}

	/**
	 * Because we set a constant here, we can only run one test
	 * so we test invalid one to force check
	 *
	 * @covers ::uniqId
	 * @#dataProvider uniqIdProvider
	 * @testWith ["invalidhash", 64]
	 * @testdox uniqId use DEFAULT_HASH  set $input with length $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testUnidIdDefaultHash(string $input, int $expected): void
	{
		define('DEFAULT_HASH', $input);
		$this->assertEquals(
			$expected,
			strlen(\CoreLibs\Create\Uids::uniqId())
		);
	}

	/**
	 * Short id, always 8 in length
	 *
	 * @covers ::uniqIdShort
	 * @testWith [8]
	 * @testdox uniqIdShort will be length $expected [$_dataName]
	 *
	 * @param integer $expected
	 * @return void
	 */
	public function testUniqIdShort(int $expected): void
	{
		$this->assertEquals(
			$expected,
			strlen(\CoreLibs\Create\Uids::uniqIdShort())
		);
	}

	/**
	 * Long Id, length can change
	 *
	 * @covers ::uniqIdLong
	 * @dataProvider uniqIdLongProvider
	 * @testdox uniqIdLong will be length $expected [$_dataName]
	 *
	 * @param integer $expected
	 * @return void
	 */
	public function testUniqIdLong(int $expected): void
	{
		$this->assertEquals(
			$expected,
			strlen(\CoreLibs\Create\Uids::uniqIdLong())
		);
	}
}

// __END__
