<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Check\Json
 * @coversDefaultClass \CoreLibs\Convert\Json
 * @testdox \CoreLibs\Convert\Json method tests
 */
final class CoreLibsConvertJsonTest extends TestCase
{

	/**
	 * test list for json convert tests
	 *
	 * @return array
	 */
	public function jsonProvider(): array
	{
		return [
			'valid json' => [
				'{"m":2,"f":"sub_2"}',
				false,
				[
					'm' => 2,
					'f' => 'sub_2'
				]
			],
			'empty json' => [
				'',
				false,
				[]
			],
			'invalid json override' => [
				'not valid',
				true,
				[
					'not valid'
				]
			],
			'invalid json' => [
				'not valid',
				false,
				[]
			],
			'null json' => [
				null,
				false,
				[]
			]
		];
	}

	/**
	 * json error list
	 *
	 * @return array JSON error list
	 */
	public function jsonErrorProvider(): array
	{
		return [
			'no error' => [
				'{}',
				JSON_ERROR_NONE, ''
			],
			'depth error' => [
				'[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['
				. '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]'
				. ']]]]',
				JSON_ERROR_DEPTH, 'Maximum stack depth exceeded'
			],
			// 'state mismatch error' => [
			// 	'{foo:}',
			// 	JSON_ERROR_STATE_MISMATCH, 'Underflow or the modes mismatch'
			// ],
			// 'ctrl char error' => [
			// 	' {"data":"data","data":"data","data":"data","data":"data"}',
			// 	JSON_ERROR_CTRL_CHAR, 'Unexpected control character found'
			// ],
			'syntax error' => [
				'not valid',
				JSON_ERROR_SYNTAX, 'Syntax error, malformed JSON'
			],
			// 'utf8 error' => [
			// 	'{"invalid":"\xB1\x31"}',
			// 	JSON_ERROR_UTF8, 'Malformed UTF-8 characters, possibly incorrectly encoded'
			// ],
			// 'invalid property' => [
			// 	'{"\u0000":"abc"}',
			// 	JSON_ERROR_INVALID_PROPERTY_NAME, 'A key starting with \u0000 character was in the string'
			// ],
			// 'utf-16 error' => [
			// 	'',
			// 	JSON_ERROR_UTF16, 'Single unpaired UTF-16 surrogate in unicode escape'
			// ],
			// 'unknown error' => [
			// 	'',
			// 	-999999, 'Unknown error'
			// ]
		];
	}

	/**
	 * test json convert states
	 *
	 * @covers ::jsonConvertToArray
	 * @dataProvider jsonProvider
	 * @testdox jsonConvertToArray $input (Override: $flag) will be $expected [$_dataName]
	 *
	 * @param string|null $input
	 * @param bool $flag
	 * @param array $expected
	 * @return void
	 */
	public function testJsonConvertToArray(?string $input, bool $flag, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Json::jsonConvertToArray($input, $flag)
		);
	}

	/**
	 * test json error states
	 *
	 * @covers ::jsonGetLastError
	 * @dataProvider jsonErrorProvider
	 * @testdox jsonGetLastError $input will be $expected_i/$expected_s [$_dataName]
	 *
	 * @param string|null $input
	 * @param string $expected
	 * @return void
	 */
	public function testJsonGetLastError(?string $input, int $expected_i, string $expected_s): void
	{
		\CoreLibs\Convert\Json::jsonConvertToArray($input);
		$this->assertEquals(
			$expected_i,
			\CoreLibs\Convert\Json::jsonGetLastError()
		);
		$this->assertEquals(
			$expected_s,
			\CoreLibs\Convert\Json::jsonGetLastError(true)
		);
	}
}

// __END__
