<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Create\Hash
 * @testdox CoreLibs\Create\Hash method tests
 */
final class CoreLibsCreateHashTest extends TestCase
{

	public function hashProvider(): array
	{
		return [
			'any string' => [
				'text' => 'Some String Text',
				'crc32b_reverse' => 'c5c21d91', // crc32b (in revere)
				'sha1Short' => '', // sha1Short
				// via hash
				'crc32b' => '', // hash: crc32b
				'alder32' => '', // hash: alder32
				'fnv132' => '', // hash: fnv132
				'fnv1a32' => '', // hash: fnv1a32
				'joaat' => '', // hash: joaat
			]
		];
	}

	public function crc32bProvider(): array
	{
		$list = [];
		foreach ($this->hashProvider() as $name => $values) {
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
}

// __END__
