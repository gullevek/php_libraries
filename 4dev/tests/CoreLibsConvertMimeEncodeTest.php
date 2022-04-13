<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\MimeEncode
 * @coversDefaultClass \CoreLibs\Convert\MimeEncode
 * @testdox \CoreLibs\Convert\MimeEncode method tests
 */
final class CoreLibsConvertMimeEncodeTest extends TestCase
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
		$encoded = \CoreLibs\Convert\MimeEncode::__mbMimeEncode($input, $encoding);
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
}

// __END__
