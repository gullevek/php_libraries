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
			'default hash DEFAULT_HASH not set' => [
				0 => null,
				1 => 64,
			],
			'invalid name' => [
				0 => 'iamnotavalidhash',
				1 => 64,
			]
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
}

// __END__
