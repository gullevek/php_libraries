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
	private const DATA_FOLDER = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function splitFormatStringProvider(): array
	{
		// 0: input
		// 1: format
		// 3: expected
		return [
			'all empty string' => [
				'',
				'',
				''
			],
			'empty input string' => [
				'',
				'2-2',
				''
			],
			'empty format string string' => [
				'1234',
				'',
				'1234'
			],
			'string format match' => [
				'1234',
				'2-2',
				'12-34'
			],
			'string format trailing match' => [
				'1234',
				'2-2-',
				'12-34'
			],
			'string format leading match' => [
				'1234',
				'-2-2',
				'12-34'
			],
			'string format double inside match' => [
				'1234',
				'2--2',
				'12--34',
			],
			'string format short first' => [
				'1',
				'2-2',
				'1'
			],
			'string format match first' => [
				'12',
				'2-2',
				'12'
			],
			'string format short second' => [
				'123',
				'2-2',
				'12-3'
			],
			'string format too long' => [
				'1234567',
				'2-2',
				'12-34-567'
			],
			'different split character' => [
				'1234',
				'2_2',
				'12_34'
			],
			'mixed split characters' => [
				'123456',
				'2-2_2',
				'12-34_56'
			],
			'length mixed' => [
				'ABCD12345568ABC13',
				'2-4_5-2#4',
				'AB-CD12_34556-8A#BC13'
			],
			'split with split chars in string' => [
				'12-34',
				'2-2',
				'12--3-4'
			],
		];
	}

	/**
	 * split format string
	 *
	 * @covers ::splitFormatString
	 * @dataProvider splitFormatStringProvider
	 * @testdox splitFormatString $input with format $format will be $expected [$_dataName]
	 *
	 * @param  string      $input
	 * @param  string      $format
	 * @param  string      $expected
	 * @return void
	 */
	public function testSplitFormatString(
		string $input,
		string $format,
		string $expected
	): void {
		$output = \CoreLibs\Convert\Strings::splitFormatString(
			$input,
			$format,
		);
		$this->assertEquals(
			$expected,
			$output
		);
	}

	/** check exceptions */
	public function splitFormatStringExceptionProvider(): array
	{
		return [
			'invalid format string' => [
				'1234',
				'2あ2',
			],
			'mutltibyte string' => [
				'あいうえ',
				'2-2',
			],
			'mutltibyte split string' => [
				'1234',
				'２-２',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::splitFormatStringFixed
	 * @dataProvider splitFormatStringExceptionProvider
	 * @testdox splitFormatString Exception catch checks for $input with $format[$_dataName]
	 *
	 * @return void
	 */
	public function testSplitFormatStringExceptions(string $input, string $format): void
	{
		// catch exception
		$this->expectException(\InvalidArgumentException::class);
		\CoreLibs\Convert\Strings::splitFormatString($input, $format);
	}

	/**
	 * test for split Format string fixed length
	 *
	 * @return array
	 */
	public function splitFormatStringFixedProvider(): array
	{
		return [
			'normal split, default split char' => [
				'abcdefg',
				4,
				null,
				'abcd-efg'
			],
			'noraml split, other single split char' => [
				'abcdefg',
				4,
				"=",
				'abcd=efg'
			],
			'noraml split, other multiple split char' => [
				'abcdefg',
				4,
				"-=-",
				'abcd-=-efg'
			],
			'non ascii characters' => [
				'あいうえお',
				2,
				"-",
				'あい-うえ-お'
			],
			'empty string' => [
				'',
				4,
				"-",
				''
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::splitFormatStringFixed
	 * @dataProvider splitFormatStringFixedProvider
	 * @testdox splitFormatStringFixed $input with length $split_length and split chars $split_characters will be $expected [$_dataName]
	 *
	 * @param  string      $input
	 * @param  int         $split_length
	 * @param  string|null $split_characters
	 * @param  string      $expected
	 * @return void
	 */
	public function testSplitFormatStringFixed(
		string $input,
		int $split_length,
		?string $split_characters,
		string $expected
	): void {
		if ($split_characters === null) {
			$output = \CoreLibs\Convert\Strings::splitFormatStringFixed(
				$input,
				$split_length
			);
		} else {
			$output = \CoreLibs\Convert\Strings::splitFormatStringFixed(
				$input,
				$split_length,
				$split_characters
			);
		}
		$this->assertEquals(
			$expected,
			$output
		);
	}

	public function splitFormatStringFixedExceptionProvider(): array
	{
		return [
			'split length too short' => [
				'abcdefg',
				-1,
			],
			'split length longer than string' => [
				'abcdefg',
				20,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::splitFormatStringFixed
	 * @dataProvider splitFormatStringFixedExceptionProvider
	 * @testdox splitFormatStringFixed Exception catch checks for $input with $length [$_dataName]
	 *
	 * @return void
	 */
	public function testSplitFormatStringFixedExceptions(string $input, int $length): void
	{
		// catch exception
		$this->expectException(\InvalidArgumentException::class);
		\CoreLibs\Convert\Strings::splitFormatStringFixed($input, $length);
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

	/**
	 * provider for testStripMultiplePathSlashes
	 *
	 * @return array<mixed>
	 */
	public function stripMultiplePathSlashesProvider(): array
	{
		return [
			'no slahses' => [
				'input' => 'string_abc',
				'expected' => 'string_abc',
			],
			'one slash' => [
				'input' => 'some/foo',
				'expected' => 'some/foo',
			],
			'two slashes' => [
				'input' => 'some//foo',
				'expected' => 'some/foo',
			],
			'three slashes' => [
				'input' => 'some///foo',
				'expected' => 'some/foo',
			],
			'slashes in front' => [
				'input' => '/foo',
				'expected' => '/foo',
			],
			'two slashes in front' => [
				'input' => '//foo',
				'expected' => '/foo',
			],
			'thee slashes in front' => [
				'input' => '///foo',
				'expected' => '/foo',
			],
			'slashes in back' => [
				'input' => 'foo/',
				'expected' => 'foo/',
			],
			'two slashes in back' => [
				'input' => 'foo//',
				'expected' => 'foo/',
			],
			'thee slashes in back' => [
				'input' => 'foo///',
				'expected' => 'foo/',
			],
			'multiple slashes' => [
				'input' => '/foo//bar///string/end_times',
				'expected' => '/foo/bar/string/end_times',
			]
		];
	}

	/**
	 * test multiple slashes clean up
	 *
	 * @covers ::stripMultiplePathSlashes
	 * @dataProvider stripMultiplePathSlashesProvider
	 * @testdox stripMultiplePathSlashes $input will be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testStripMultiplePathSlashes(string $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Strings::stripMultiplePathSlashes($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerStripUTF8BomBytes(): array
	{
		return [
			"utf8-bom" => [
				"file" => "UTF8BOM.csv",
				"expect" => "Asset Type,Epic,File Name\n",
			],
			"utf8" => [
				"file" => "UTF8.csv",
				"expect" => "Asset Type,Epic,File Name\n",
			],
		];
	}

	/**
	 * test utf8 bom remove
	 *
	 * @covers ::stripUTF8BomBytes
	 * @dataProvider providerStripUTF8BomBytes
	 * @testdox stripUTF8BomBytes $file will be $expected [$_dataName]
	 *
	 * @param  string $file
	 * @param  string $expected
	 * @return void
	 */
	public function testStripUTF8BomBytes(string $file, string $expected): void
	{
		// load sample file
		if (!is_file(self::DATA_FOLDER . $file)) {
			$this->markTestSkipped('File: ' . $file . ' could not be opened');
		}
		$file = file_get_contents(self::DATA_FOLDER . $file);
		if ($file === false) {
			$this->markTestSkipped('File: ' . $file . ' could not be read');
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Strings::stripUTF8BomBytes($file)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function allCharsInSetProvider(): array
	{
		return [
			'find' => [
				'abc',
				'abcdef',
				true
			],
			'not found' => [
				'abcz',
				'abcdef',
				false
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::allCharsInSet
	 * @dataProvider allCharsInSetProvider
	 * @testdox allCharsInSet $input in $haystack with expected $expected [$_dataName]
	 *
	 * @param  string $needle
	 * @param  string $haystack
	 * @param  bool   $expected
	 * @return void
	 */
	public function testAllCharsInSet(string $needle, string $haystack, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Strings::allCharsInSet($needle, $haystack)
		);
	}

	public function buildCharStringFromListsProvider(): array
	{
		return [
			'test a' => [
				'abc',
				['a', 'b', 'c'],
			],
			'test b' => [
				'abc123',
				['a', 'b', 'c'],
				['1', '2', '3'],
			],
			'test c: no params' => [
				'',
			],
			'test c: empty 1' => [
				'',
				[]
			],
			'test nested' => [
				'abc',
				[['a'], ['b'], ['c']],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::buildCharStringFromLists
	 * @dataProvider buildCharStringFromListsProvider
	 * @testdox buildCharStringFromLists all $input convert to $expected [$_dataName]
	 *
	 * @param  string $expected
	 * @param  array  ...$input
	 * @return void
	 */
	public function testBuildCharStringFromLists(string $expected, array ...$input): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Strings::buildCharStringFromLists(...$input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function removeDuplicatesProvider(): array
	{
		return [
			'test no change' => [
				'ABCDEFG',
				'ABCDEFG',
			],
			'test simple' => [
				'aa',
				'a'
			],
			'test keep lower and uppwer case' => [
				'AaBbCc',
				'AaBbCc'
			],
			'test unqiue' => [
				'aabbcc',
				'abc'
			],
			'test multibyte no change' => [
				'あいうえお',
				'あいうえお',
			],
			'test multibyte' => [
				'ああいいううええおお',
				'あいうえお',
			],
			'test multibyte special' => [
				'あぁいぃうぅえぇおぉ',
				'あぁいぃうぅえぇおぉ',
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::removeDuplicates
	 * @dataProvider removeDuplicatesProvider
	 * @testdox removeDuplicates make $input unqiue to $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testRemoveDuplicates(string $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Strings::removeDuplicates($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function isValidRegexSimpleProvider(): array
	{
		return [
			'valid regex' => [
				'/^[A-z]$/',
				true,
				[
					'valid' => true,
					'preg_error' => 0,
					'error' => null,
					'pcre_error' => null
				],
			],
			'invalid regex A' => [
				'/^[A-z]$',
				false,
				[
					'valid' => false,
					'preg_error' => 1,
					'error' => 'Internal PCRE error',
					'pcre_error' => 'Internal error'
				],
			],
			'invalid regex B' => [
				'/^[A-z$',
				false,
				[
					'valid' => false,
					'preg_error' => 1,
					'error' => 'Internal PCRE error',
					'pcre_error' => 'Internal error'
				],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::isValidRegexSimple
	 * @dataProvider isValidRegexSimpleProvider
	 * @testdox isValidRegexSimple make $input unqiue to $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  bool $expected
	 * @return void
	 */
	public function testIsValidRegexSimple(string $input, bool $expected, array $expected_extended): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Convert\Strings::isValidRegex($input),
			'Regex is not valid'
		);
		$this->assertEquals(
			$expected_extended,
			\CoreLibs\Convert\Strings::validateRegex($input),
			'Validation of regex failed'
		);
		$this->assertEquals(
			// for true null is set, so we get here No Error
			$expected_extended['error'] ?? \CoreLibs\Convert\Strings::PREG_ERROR_MESSAGES[0],
			\CoreLibs\Convert\Strings::getLastRegexErrorString(),
			'Cannot match last preg error string'
		);
	}
}

// __END__
