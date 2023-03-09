<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Convert\Strings
 * @coversDefaultClass \CoreLibs\Convert\Strings
 * @testdox \CoreLibs\Convert\Strings method tests
 */
final class CoreLibsConvertStringsTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function splitFormatStringProvider(): array
	{
		// 0: input
		// 1: format
		// 2: split characters as string, null for default
		// 3: expected
		return [
			'all empty string' => [
				'',
				'',
				null,
				''
			],
			'empty input string' => [
				'',
				'2-2',
				null,
				''
			],
			'empty format string string' => [
				'1234',
				'',
				null,
				'1234'
			],
			'string format match' => [
				'1234',
				'2-2',
				null,
				'12-34'
			],
			'string format trailing match' => [
				'1234',
				'2-2-',
				null,
				'12-34'
			],
			'string format leading match' => [
				'1234',
				'-2-2',
				null,
				'12-34'
			],
			'string format double inside match' => [
				'1234',
				'2--2',
				null,
				'12--34',
			],
			'string format short first' => [
				'1',
				'2-2',
				null,
				'1'
			],
			'string format match first' => [
				'12',
				'2-2',
				null,
				'12'
			],
			'string format short second' => [
				'123',
				'2-2',
				null,
				'12-3'
			],
			'string format too long' => [
				'1234567',
				'2-2',
				null,
				'12-34-567'
			],
			'string format invalid format string' => [
				'1234',
				'2_2',
				null,
				'1234'
			],
			'different split character' => [
				'1234',
				'2_2',
				'_',
				'12_34'
			],
			'mixed split characters' => [
				'123456',
				'2-2_2',
				'-_',
				'12-34_56'
			],
			'length mixed' => [
				'ABCD12345568ABC13',
				'2-4_5-2#4',
				'-_#',
				'AB-CD12_34556-8A#BC13'
			],
			'split with split chars in string' => [
				'12-34',
				'2-2',
				null,
				'12--3-4'
			],
			'mutltibyte string' => [
				'あいうえ',
				'2-2',
				null,
				'あいうえ'
			],
			'mutltibyte split string' => [
				'1234',
				'２-２',
				null,
				'1234'
			],
		];
	}

	/**
	 * split format string
	 *
	 * @covers ::splitFormatString
	 * @dataProvider splitFormatStringProvider
	 * @testdox splitFormatString $input with format $format and splitters $split_characters will be $expected [$_dataName]
	 *
	 * @param  string      $input
	 * @param  string      $format
	 * @param  string|null $split_characters
	 * @param  string      $expected
	 * @return void
	 */
	public function testSplitFormatString(
		string $input,
		string $format,
		?string $split_characters,
		string $expected
	): void {
		if ($split_characters === null) {
			$output = \CoreLibs\Convert\Strings::splitFormatString(
				$input,
				$format
			);
		} else {
			$output = \CoreLibs\Convert\Strings::splitFormatString(
				$input,
				$format,
				$split_characters
			);
		}
		$this->assertEquals(
			$expected,
			$output
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function countSplitPartsProvider(): array
	{
		return [
			'0 elements' => [
				'',
				null,
				0
			],
			'1 element' => [
				'1',
				null,
				1,
			],
			'2 elements, trailing' => [
				'1-2-',
				null,
				2
			],
			'2 elements, leading' => [
				'-1-2',
				null,
				2
			],
			'2 elements, midde double' => [
				'1--2',
				null,
				2
			],
			'4 elements' => [
				'1-2-3-4',
				null,
				4
			],
			'3 elemenst, other splitter' => [
				'2-3_3',
				'-_',
				3
			],
			'illegal splitter' => [
				'あsdf',
				null,
				0
			]
		];
	}

	/**
	 * count split parts
	 *
	 * @covers ::countSplitParts
	 * @dataProvider countSplitPartsProvider
	 * @testdox countSplitParts $input with splitters $split_characters will be $expected [$_dataName]
	 *
	 * @param  string      $input
	 * @param  string|null $split_characters
	 * @param  int         $expected
	 * @return void
	 */
	public function testCountSplitParts(
		string $input,
		?string $split_characters,
		int $expected
	): void {
		if ($split_characters === null) {
			$output = \CoreLibs\Convert\Strings::countSplitParts(
				$input
			);
		} else {
			$output = \CoreLibs\Convert\Strings::countSplitParts(
				$input,
				$split_characters
			);
		}
		$this->assertEquals(
			$expected,
			$output
		);
	}
}

// __END__
