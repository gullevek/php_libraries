<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Check\Encoding
 * @coversDefaultClass \CoreLibs\Check\Encoding
 * @testdox \CoreLibs\Check\Encoding method tests
 */
final class CoreLibsCheckEncodingTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function checkConvertEncodingProvider(): array
	{
		return [
			// 0: string to test
			// 1: source encoding
			// 2: target encoding
			// 3: substitue character
			// 4: false for ok, array with error list
			'valid test UTF-8 to SJIS (default)' => [
				'日本語',
				'UTF-8',
				'SJIS',
				null,
				false
			],
			'invalid test UTF-8 to SJIS (dots as code point)' => [
				'❶',
				'UTF-8',
				'SJIS',
				0x2234,
				['❶']
			],
			'invalid test UTF-8 to SJIS (dots as string)' => [
				'❶',
				'UTF-8',
				'SJIS',
				'∴',
				['❶']
			],
			'invalid test UTF-8 to SJIS (none)' => [
				'❶',
				'UTF-8',
				'SJIS',
				'none',
				['❶']
			],
			'invalid test UTF-8 to SJIS (long)' => [
				'❶',
				'UTF-8',
				'SJIS',
				'long',
				['❶']
			],
			'invalid test UTF-8 to SJIS (entity)' => [
				'❶',
				'UTF-8',
				'SJIS',
				'entity',
				['❶']
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::checkConvertEncoding
	 * @dataProvider checkConvertEncodingProvider
	 * @testdox check encoding convert from $from_encoding to $to_encoding [$_dataName]
	 *
	 * @param  string          $input
	 * @param  string          $from_encoding
	 * @param  string          $to_encoding
	 * @param  string|int|null $error_char
	 * @param  array|bool      $expected
	 * @return void
	 */
	public function testCheckConvertEncoding(
		string $input,
		string $from_encoding,
		string $to_encoding,
		$error_char,
		$expected
	): void {
		if ($error_char !== null) {
			\CoreLibs\Check\Encoding::setErrorChar($error_char);
			if (!in_array($error_char, ['none', 'long', 'entity'])) {
				$this->assertEquals(
					\IntlChar::chr($error_char),
					\CoreLibs\Check\Encoding::getErrorChar()
				);
			} else {
				$this->assertEquals(
					$error_char,
					\CoreLibs\Check\Encoding::getErrorChar()
				);
			}
		}
		$return = \CoreLibs\Check\Encoding::checkConvertEncoding($input, $from_encoding, $to_encoding);
		$this->assertEquals(
			$expected,
			$return
		);
	}
}

// __END__
