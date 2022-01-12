<?php

// because we have long testdox lines
// phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Combined\ArrayHandler
 * @coversDefaultClass \CoreLibs\Combined\ArrayHandler
 * @testdox \CoreLibs\Combined\ArrayHandler method tests
 */
final class CoreLibsCombinedArrayHandlerTest extends TestCase
{
	// we use that for all
	public static $array = [
		'a' => [
			'b' => 'bar',
			'c' => 'foo',
			'same' => 'same',
			3 => 'foobar',
			'foobar' => 4,
			'true' => true,
		],
		'd',
		4,
		'b',
		'c' => 'test',
		'same' => 'same',
		'deep' => [
			'sub' => [
				'nested' => 'bar',
				'same' => 'same',
				'more' => 'test'
			]
		]
	];

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function arraySearchRecursiveProvider(): array
	{
		return [
			'find value' => [
				0 => 'bar',
				1 => self::$array,
				2 => null,
				3 => ['a', 'b'],
			],
			'find value with key' => [
				0 => 'bar',
				1 => self::$array,
				2 => 'nested',
				3 => ['deep', 'sub', 'nested']
			],
			'not existing value' => [
				0 => 'not exists',
				1 => self::$array,
				2 => null,
				3 => [],
			],
			'find value int' => [
				0 => 4,
				1 => self::$array,
				2 => null,
				3 => ['a', 'foobar']
			],
			'find value int as string' => [
				0 => '4',
				1 => self::$array,
				2 => null,
				3 => []
			],
			'find value int as string with key' => [
				0 => '4',
				1 => self::$array,
				2 => 'foobar',
				3 => []
			],
			'first level value' => [
				0 => 'd',
				1 => self::$array,
				2 => null,
				4 => [0]
			],
			'find value, return int key' => [
				0 => 'foobar',
				1 => self::$array,
				2 => null,
				3 => ['a', 3]
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function arraySearchRecursiveAllProvider(): array
	{
		return [
			'find value' => [
				0 => 'bar',
				1 => self::$array,
				2 => null,
				3 => true,
				4 => [
					'level' => -1,
					'work' => [],
					'found' => [
						0 => ['a', 'b'],
						1 => ['deep', 'sub', 'nested']
					]
				]
			],
			'find value, new type' => [
				0 => 'bar',
				1 => self::$array,
				2 => null,
				3 => false,
				4 => [
					0 => ['a', 'b'],
					1 => ['deep', 'sub', 'nested']
				]
			],
			'find value with key' => [
				0 => 'bar',
				1 => self::$array,
				2 => 'nested',
				3 => true,
				4 => [
					'level' => -1,
					'work' => [],
					'found' => [
						0 => ['deep', 'sub', 'nested']
					]
				]
			],
			'not existing value' => [
				0 => 'not exists',
				1 => self::$array,
				2 => null,
				3 => true,
				4 => [
					'level' => -1,
					'work' => [],
				],
			],
			'not existing value, new type' => [
				0 => 'not exists',
				1 => self::$array,
				2 => null,
				3 => false,
				4 => [],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function arraySearchSimpleProvider(): array
	{
		return [
			'key/value exist' => [
				0 => self::$array,
				1 => 'c',
				2 => 'foo',
				3 => false,
				4 => true,
			],
			'key/value exists twice' => [
				0 => self::$array,
				1 => 'same',
				2 => 'same',
				3 => false,
				4 => true,
			],
			'key/value not found' => [
				0 => self::$array,
				1 => 'not exists',
				2 => 'not exists',
				3 => false,
				4 => false,
			],
			'key exists, value not' => [
				0 => self::$array,
				1 => 'b',
				2 => 'not exists',
				3 => false,
				4 => false,
			],
			'key not, value exists' => [
				0 => self::$array,
				1 => 'not exists',
				2 => 'bar',
				3 => false,
				4 => false,
			],
			'numeric key, value exists' => [
				0 => self::$array,
				1 => 0,
				2 => 'd',
				3 => false,
				4 => true,
			],
			'numeric key as string, value exists' => [
				0 => self::$array,
				1 => '0',
				2 => 'd',
				3 => false,
				4 => true,
			],
			'numeric key as string, value exists, strinct' => [
				0 => self::$array,
				1 => '0',
				2 => 'd',
				3 => true,
				4 => false,
			],
			'key exists, value numeric' => [
				0 => self::$array,
				1 => 'foobar',
				2 => 4,
				3 => false,
				4 => true,
			],
			'key exists, value numeric as string' => [
				0 => self::$array,
				1 => 'foobar',
				2 => '4',
				3 => false,
				4 => true,
			],
			'key exists, value numeric as string, strict' => [
				0 => self::$array,
				1 => 'foobar',
				2 => '4',
				3 => true,
				4 => false,
			],
			'key exists, value bool' => [
				0 => self::$array,
				1 => 'true',
				2 => true,
				3 => false,
				4 => true,
			],
			'key exists, value bool as string' => [
				0 => self::$array,
				1 => 'true',
				2 => 'true',
				3 => false,
				4 => true,
			],
			'key exists, value bool as string, strict' => [
				0 => self::$array,
				1 => 'true',
				2 => 'true',
				3 => true,
				4 => false,
			],
		];
	}

	/**
	 * TODO: create provider for n array merge
	 * provides array listing for the merge test
	 *
	 * @return array
	 */
	public function arrayMergeRecursiveProvider(): array
	{
		return [
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function arrayCompareProvider(): array
	{
		return [
			'one matching' => [
				['a', 'b', 'c'],
				['c', 'd', 'e'],
				['a', 'b', 'd', 'e']
			],
			'all the same' => [
				['a', 'b', 'c'],
				['a', 'b', 'c'],
				[]
			],
			'all different' => [
				['a', 'b'],
				['c', 'd'],
				['a', 'b', 'c', 'd']
			],
			'empty arrays' => [
				[],
				[],
				[]
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function inArrayAnyProvider(): array
	{
		return [
			'all exist in haystack' => [
				[1],
				[1, 2, 3, 4],
				[1]
			],
			'not all exist in haystack' => [
				[1, 5],
				[1, 2, 3, 4],
				[1]
			],
			'none exist in haystack' => [
				[5],
				[1, 2, 3, 4],
				false
			],
		];
	}

	public function genAssocArrayProvider(): array
	{
		return [
			'non set' => [
				[
					0 => ['a' => 'a1', 'b' => 2],
					1 => ['a' => 'a2', 'b' => 3],
					2 => ['a' => '', 'b' => null],
				],
				false,
				false,
				false,
				[],
			],
			'key set' => [
				[
					0 => ['a' => 'a1', 'b' => 2],
					1 => ['a' => 'a2', 'b' => 3],
					2 => ['a' => '', 'b' => null],
				],
				'a',
				false,
				false,
				['a1' => 0, 'a2' => 1],
			],
			'value set' => [
				[
					0 => ['a' => 'a1', 'b' => 2],
					1 => ['a' => 'a2', 'b' => 3],
					2 => ['a' => '', 'b' => null],
				],
				false,
				'a',
				false,
				[0 => 'a1', 1 => 'a2', 2 => ''],
			],
			'key and value set, add empty, null' => [
				[
					0 => ['a' => 'a1', 'b' => 2],
					1 => ['a' => 'a2', 'b' => 3],
					2 => ['a' => '', 'b' => null],
				],
				'a',
				'b',
				false,
				['a1' => 2, 'a2' => 3],
			],
			'key and value set, add empty' => [
				[
					0 => ['a' => 'a1', 'b' => 2],
					1 => ['a' => 'a2', 'b' => 3],
					2 => ['a' => '', 'b' => ''],
					3 => ['a' => 'a4', 'b' => ''],
				],
				'a',
				'b',
				false,
				['a1' => 2, 'a2' => 3, 'a4' => ''],
			],
			'key/value set, skip empty' => [
				[
					0 => ['a' => 'a1', 'b' => 2],
					1 => ['a' => 'a2', 'b' => 3],
					2 => ['a' => '', 'b' => null],
				],
				'a',
				'b',
				true,
				['a1' => 2, 'a2' => 3],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function flattenArrayProvider(): array
	{
		return [
			'array key/value, single' => [
				0 => ['a' => 'foo', 1 => 'bar', 'c' => 2],
				1 => ['foo', 'bar', 2],
				2 => ['a', 1, 'c'],
				3 => ['a', 1, 'c'],
			],
			'array values, single' => [
				0 => ['foo', 'bar', 2],
				1 => ['foo', 'bar', 2],
				2 => [0, 1, 2],
				3 => [0, 1, 2],
			],
			'array key/value, multi' => [
				0 => [
					'a' => ['a1' => 'a1foo', 'a2' => 'a1bar'],
					1 => 'bar',
					'c' => [2, 3, 4],
					'd' => [
						'e' => [
							'de1' => 'subfoo', 'de2' => 'subbar', 'a2' => 'a1bar'
						]
					]
				],
				1 => ['a1foo', 'a1bar', 'bar', 2, 3, 4, 'subfoo', 'subbar', 'a1bar'],
				2 => ['a', 'a1', 'a2', 1, 'c', 0, 1, 2, 'd', 'e', 'de1', 'de2', 'a2'],
				3 => ['a1', 'a2', 1, 0, 1, 2, 'de1', 'de2', 'a2'],
			],
			'array with double values' => [
				0 => ['a', 'a', 'b'],
				1 => ['a', 'a', 'b'],
				2 => [0, 1, 2],
				3 => [0, 1, 2],
			]
		];
	}

	/**
	 * use the flattenArrayProvider and replace 1 with 2 array pos
	 *
	 * @return array
	 */
	public function flattenArrayKeyProvider(): array
	{
		$list = [];
		foreach ($this->flattenArrayProvider() as $key => $row) {
			$list[$key] = [
				$row[0],
				$row[2],
			];
		}
		return $list;
	}

	/**
	 * use the flattenArrayProvider and replace 1 with ３ array pos
	 *
	 * @return array
	 */
	public function flattenArrayKeyLeavesOnlyProvider(): array
	{
		$list = [];
		foreach ($this->flattenArrayProvider() as $key => $row) {
			$list[$key] = [
				$row[0],
				$row[3],
			];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function arrayFlatForKeyProvider(): array
	{
		return [
			'all present, single level' => [
				0 => [
					'a' => ['b1' => 'foo', 'a2' => 'a-foo'],
					'b' => ['b1' => 'bar', 'a2' => 'b-foo'],
					'c' => ['b1' => 'foobar', 'a2' => 'c-foo'],
				],
				1 => 'a2',
				2 => [
					'a' => 'a-foo',
					'b' => 'b-foo',
					'c' => 'c-foo',
				],
			],
			'no sub arrays' => [
				0 => ['a', 'b', 'c'],
				1 => 'a',
				2 => ['a', 'b', 'c'],
			],
			'sub arrays with missing' => [
				0 => [
					'a' => ['b1' => 'foo', 'a2' => 'a-foo'],
					'b' => ['b1' => 'bar'],
					'c' => ['b1' => 'foobar', 'a2' => 'c-foo'],
				],
				1 => 'a2',
				2 => [
					'a' => 'a-foo',
					'b' => ['b1' => 'bar'],
					'c' => 'c-foo',
				],
			],
			'deep nested sub arrays' => [
				0 => [
					'a' => [
						'b1' => 'foo',
						'a2' => [
							'text' => ['a-foo', 'a-bar'],
						],
					],
					'b' => [
						'b1' => 'bar',
						'a2' => [
							'text' => 'b-foo',
						],
					],
				],
				1 => 'a2',
				2 => [
					'a' => [
						'text' => ['a-foo', 'a-bar'],
					],
					'b' => [
						'text' => 'b-foo',
					],
				],
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arraySearchRecursive
	 * @dataProvider arraySearchRecursiveProvider
	 * @testdox arraySearchRecursive $needle (key $key_search_for) in $input and will be $expected [$_dataName]
	 *
	 * @param string|null $needle
	 * @param array $input
	 * @param string|null $key_search_for
	 * @return void
	 */
	public function testArraySearchRecursive($needle, array $input, ?string $key_search_for, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arraySearchRecursive($needle, $input, $key_search_for)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arraySearchRecursiveAll
	 * @dataProvider arraySearchRecursiveAllProvider
	 * @testdox arraySearchRecursiveAll $needle (key $key_search_for) in $input and will be $expected (old: $flag) [$_dataName]
	 *
	 * @param string|null $needle
	 * @param array $input
	 * @param string|null $key_search_for
	 * @param bool $flag
	 * @return void
	 */
	public function testArraySearchRecursiveAll($needle, array $input, ?string $key_search_for, bool $flag, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arraySearchRecursiveAll($needle, $input, $key_search_for, $flag)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arraySearchSimple
	 * @dataProvider arraySearchSimpleProvider
	 * @testdox arraySearchSimple $input searched with key: $key / value: $value (strict: $flag) will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param string|int $key
	 * @param string|int $value
	 * @param bool $expected
	 * @return void
	 */
	public function testArraySearchSimple(array $input, $key, $value, bool $flag, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arraySearchSimple($input, $key, $value, $flag)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayMergeRecursive
	 * @#dataProvider arrayMergeRecursiveProvider
	 * @testdox arrayMergeRecursive ... will be $expected [$_dataName]
	 *
	 * @param array $input nested array set as each parameter
	 * @param bool $flag
	 * @param bool|array $expected
	 * @return void
	 * array $input, bool $flag, $expected
	 */
	public function testArrayMergeRecursive(): void
	{
		$this->assertTrue(true, 'Implement proper test run');
		$this->markTestIncomplete(
			'testArrayMergeRecursive has not been implemented yet.'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayDiff
	 * @dataProvider arrayCompareProvider
	 * @testdox arrayDiff $input_a diff $input_b will be $expected [$_dataName]
	 *
	 * @param array $input_a
	 * @param array $input_b
	 * @param array $expected
	 * @return void
	 */
	public function testArrayDiff(array $input_a, array $input_b, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arrayDiff($input_a, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::inArrayAny
	 * @dataProvider inArrayAnyProvider
	 * @testdox inArrayAny needle $input_a in haystack $input_b will be $expected [$_dataName]
	 *
	 * @param array $input_a
	 * @param array $input_b
	 * @param array|bool $expected
	 * @return void
	 */
	public function testInArrayAny(array $input_a, array $input_b, $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::inArrayAny($input_a, $input_b)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::genAssocArray
	 * @dataProvider genAssocArrayProvider
	 * @testdox genAssocArray array $input with $key or $value and flag set only $flag will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param string|int|bool $key
	 * @param string|int|bool $value
	 * @param bool $flag
	 * @param array $expected
	 * @return void
	 */
	public function testGenAssocArray(array $input, $key, $value, bool $flag, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::genAssocArray($input, $key, $value, $flag)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::flattenArray
	 * @dataProvider flattenArrayProvider
	 * @testdox testFlattenArray array $input will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param array $expected
	 * @return void
	 */
	public function testFlattenyArray(array $input, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::flattenArray($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::flattenArrayKey
	 * @dataProvider flattenArrayKeyProvider
	 * @testdox flattenArrayKey array $input will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param array $expected
	 * @return void
	 */
	public function testFlattenArrayKey(array $input, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::flattenArrayKey($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::flattenArrayKeyLeavesOnly
	 * @dataProvider flattenArrayKeyLeavesOnlyProvider
	 * @testdox flattenArrayKeyLeavesOnly array $input will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param array $expected
	 * @return void
	 */
	public function testFlattenArrayKeyLeavesOnly(array $input, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::flattenArrayKeyLeavesOnly($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayFlatForKey
	 * @dataProvider arrayFlatForKeyProvider
	 * @testdox arrayFlatForKey array $input will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param array $expected
	 * @return void
	 */
	public function testArrayFlatForKey(array $input, $search, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arrayFlatForKey($input, $search)
		);
	}
}

// __END__
