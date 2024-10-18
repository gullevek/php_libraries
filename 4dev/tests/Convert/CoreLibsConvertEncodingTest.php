<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\Encoding
 * @coversDefaultClass \CoreLibs\Convert\Encoding
 * @testdox \CoreLibs\Convert\Encoding method tests
 */
final class CoreLibsConvertEncodingTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function convertEncodingProvider(): array
	{
		return [
			// 0: original string
			// 1: target encoding
			// 2: optional source encoding
			// 3: auto check (not used)
			// 4: expected string
			// 5: expected string encoding
			'simple from UTF-8 to SJIS' => [
				'input string',
				'SJIS',
				null,
				null,
				'input string',
				'SJIS'
			],
			'kanji from UTF-8 to SJIS' => [
				'日本語',
				'SJIS',
				null,
				null,
				'日本語',
				'SJIS'
			],
			'kanji from UTF-8 to SJIS with source' => [
				'日本語',
				'SJIS',
				'UTF-8',
				null,
				'日本語',
				'SJIS'
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::convertEncoding
	 * @dataProvider convertEncodingProvider
	 * @testdox convert encoding $target_encoding, source: $source_encoding, auto: $auto_check [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $target_encoding
	 * @param  string $source_encoding
	 * @param  bool   $auto_check
	 * @param  string $expected
	 * @param  string $expected_encoding
	 * @return void
	 */
	public function testConvertEncoding(
		string $input,
		string $target_encoding,
		?string $source_encoding,
		?bool $auto_check,
		string $expected,
		string $expected_encoding
	): void {
		if ($source_encoding === null and $auto_check === null) {
			$string = \CoreLibs\Convert\Encoding::convertEncoding($input, $target_encoding);
		} elseif ($auto_check === null) {
			$string = \CoreLibs\Convert\Encoding::convertEncoding($input, $target_encoding, $source_encoding);
		} else {
			$string = \CoreLibs\Convert\Encoding::convertEncoding(
				$input,
				$target_encoding,
				$source_encoding,
				$auto_check
			);
		}
		// because we can't store encoding in here anyway
		$target = mb_convert_encoding($expected, $expected_encoding, 'UTF-8');
		// print "IN: $input, $target_encoding\n";
		$this->assertEquals(
			$target,
			$string
		);
	}
}

// __END__
