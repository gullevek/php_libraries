<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Language\Encoding
 * @coversDefaultClass \CoreLibs\Language\Encoding
 * @testdox \CoreLibs\Language\Encoding method tests
 */
final class CoreLibsLanguageEncodingTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function mbMimeEncodeProvider(): array
	{
		return [
			// 0: input string
			// 1: encoding
			// 2: expected
			'standard UTF-8' => [
				'Test string',
				'UTF-8',
				'Test string'
			],
			'long text UTF-8' => [
				'The quick brown fox jumps over the lazy sheep that sleeps in the ravine '
					. 'and has no idea what is going on here',
				'UTF-8',
				'The quick brown fox jumps over the lazy sheep that sleeps in the ravine '
					. 'and has no idea what is going on here'
			],
			'standard with special chars UTF-8' => [
				'This is ümläßtと漢字もカタカナ！!^$%&',
				'UTF-8',
				'This is =?UTF-8?B?w7xtbMOkw59044Go5ryi5a2X44KC44Kr44K/44Kr44OK77yBIV4k?='
					. "\r\n"
					. ' =?UTF-8?B?JQ==?=&'
			],
			'35 chars and space at the end UTF-8' => [
				'12345678901234567890123456789012345 '
					. 'is there a space?',
				'UTF-8',
				'12345678901234567890123456789012345 '
					. 'is there a =?UTF-8?B?c3BhY2U/?='
			],
			'36 chars and space at the end UTF-8' => [
				'123456789012345678901234567890123456 '
					. 'is there a space?',
				'UTF-8',
				'123456789012345678901234567890123456 '
					. 'is there a =?UTF-8?B?c3BhY2U/?='
			],
			'36 kanji and space UTF-8' => [
				'カタカナカタカナかなカタカナカタカナかなカタカナカタカナかなカタカナカタ '
					. 'is there a space?',
				'UTF-8',
				"=?UTF-8?B?44Kr44K/44Kr44OK44Kr44K/44Kr44OK44GL44Gq44Kr44K/44Kr44OK44Kr?=\r\n"
					. " =?UTF-8?B?44K/44Kr44OK?=\r\n"
					. " =?UTF-8?B?44GL44Gq44Kr44K/44Kr44OK44Kr44K/44Kr44OK44GL44Gq44Kr44K/44Kr?=\r\n"
					. " =?UTF-8?B?44OK44Kr44K/?= is there a =?UTF-8?B?c3BhY2U/?="
			]
		];
	}

	/**
	 * mb mime header encoding test
	 *
	 * @covers ::__mbMimeEncode
	 * @dataProvider mbMimeEncodeProvider
	 * @testdox mb encoding target $encoding [$_dataName]
	 *
	 * @return void
	 */
	public function testUuMbMimeEncode(string $input, string $encoding, string $expected): void
	{
		// encode string first
		$encoded = \CoreLibs\Language\Encoding::__mbMimeEncode($input, $encoding);
		// print "MIME: -" . $encoded . "-\n";
		$this->assertEquals(
			$expected,
			$encoded
		);
		$decoded = mb_decode_mimeheader($encoded);
		// print "INPUT  : " . $input . "\n";
		// print "DECODED: " . $decoded . "\n";
		// back compare decoded
		$this->assertEquals(
			$input,
			$decoded
		);
	}

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
			$string = \CoreLibs\Language\Encoding::convertEncoding($input, $target_encoding);
		} elseif ($auto_check === null) {
			$string = \CoreLibs\Language\Encoding::convertEncoding($input, $target_encoding, $source_encoding);
		} else {
			$string = \CoreLibs\Language\Encoding::convertEncoding(
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
			'invalid test UTF-8 to SJIS (dots)' => [
				'❶',
				'UTF-8',
				'SJIS',
				0x2234,
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
			\CoreLibs\Language\Encoding::setErrorChar($error_char);
			if (!in_array($error_char, ['none', 'long', 'entity'])) {
				$this->assertEquals(
					\IntlChar::chr($error_char),
					\CoreLibs\Language\Encoding::getErrorChar()
				);
			} else {
				$this->assertEquals(
					$error_char,
					\CoreLibs\Language\Encoding::getErrorChar()
				);
			}
		}
		$return = \CoreLibs\Language\Encoding::checkConvertEncoding($input, $from_encoding, $to_encoding);
		$this->assertEquals(
			$expected,
			$return
		);
	}
}

// __END__
