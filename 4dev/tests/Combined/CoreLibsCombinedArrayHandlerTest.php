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
			'barbaz' => 5,
			'zapzap' => '6',
			'zipzip' => 7,
			'true' => true,
			'more' => 'other',
		],
		'd',
		4,
		'b',
		'c' => 'test',
		'single' => 'single',
		'same' => 'same',
		'deep' => [
			'sub' => [
				'nested' => 'bar',
				'same' => 'same',
				'more' => 'test',
				'barbaz' => '5',
				'zapzap' => '6',
				'zipzip' => 7,
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
		/*
		0: $needle,
		1: array $input,
		2: ?string $key_search_for,
		3: bool $flag,
		4: array $expected
		*/
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
		/*
		0: array $input,
		1: $key,
		2: $value,
		3: bool $strict,
		4: bool $expected
		*/
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
			// array tyep search
			'array type, both exist' => [
				0 => self::$array,
				1 => 'more',
				2 => ['other', 'test'],
				3 => false,
				4 => true,
			],
			'array type, one exist' => [
				0 => self::$array,
				1 => 'more',
				2 => ['other', 'not'],
				3 => false,
				4 => true,
			],
			'array type, none exist' => [
				0 => self::$array,
				1 => 'more',
				2 => ['never', 'not'],
				3 => false,
				4 => false,
			],
			'array type, both exist, not strict, int and string' => [
				0 => self::$array,
				1 => 'barbaz',
				2 => [5, '5'],
				3 => false,
				4 => true,
			],
			'array type, both exist, not strict, both string' => [
				0 => self::$array,
				1 => 'barbaz',
				2 => ['5', '5'],
				3 => false,
				4 => true,
			],
			'array type, both exist, not strict, int and int' => [
				0 => self::$array,
				1 => 'barbaz',
				2 => [5, 5],
				3 => false,
				4 => true,
			],
			'array type, both exist, strict, int and string' => [
				0 => self::$array,
				1 => 'barbaz',
				2 => [5, '5'],
				3 => true,
				4 => true,
			],
			'array type, both exist, strict, both string' => [
				0 => self::$array,
				1 => 'barbaz',
				2 => ['5', '5'],
				3 => true,
				4 => true,
			],
			'array type, both exist, strict, int and int' => [
				0 => self::$array,
				1 => 'barbaz',
				2 => [5, 5],
				3 => true,
				4 => true,
			],
			'array type, both exist, strict, int and int to string and string' => [
				0 => self::$array,
				1 => 'zapzap',
				2 => [6, 6],
				3 => true,
				4 => false,
			],
			'array type, both exist, strict, string and string to string and string' => [
				0 => self::$array,
				1 => 'zapzap',
				2 => ['6', '6'],
				3 => true,
				4 => true,
			],
			'array type, both exist, not strict, int and int to string and string' => [
				0 => self::$array,
				1 => 'zapzap',
				2 => [6, 6],
				3 => false,
				4 => true,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function arraySearchKeyProvider(): array
	{
		/*
		0: search in array
		1: search keys
		2: flat flag
		3: prefix flag
		4: expected array
		*/
		return [
			// single
			'find single, standard' => [
				0 => self::$array,
				1 => ['single'],
				2 => null,
				3 => null,
				4 => [
					0 => [
						'value' => 'single',
						'path' => ['single'],
					],
				],
			],
			'find single, prefix' => [
				0 => self::$array,
				1 => ['single'],
				2 => null,
				3 => true,
				4 => [
					'single' => [
						0 => [
							'value' => 'single',
							'path' => ['single'],
						],
					],
				],
			],
			'find single, flat' => [
				0 => self::$array,
				1 => ['single'],
				2 => true,
				3 => null,
				4 => [
					'single',
				],
			],
			'find single, flat, prefix' => [
				0 => self::$array,
				1 => ['single'],
				2 => true,
				3 => true,
				4 => [
					'single' => [
						'single',
					],
				],
			],
			// not found
			'not found, standard' => [
				0 => self::$array,
				1 => ['NOT FOUND'],
				2 => null,
				3 => null,
				4 => [],
			],
			'not found, standard, prefix' => [
				0 => self::$array,
				1 => ['NOT FOUND'],
				2 => null,
				3 => true,
				4 => [
					'NOT FOUND' => [],
				],
			],
			'not found, flat' => [
				0 => self::$array,
				1 => ['NOT FOUND'],
				2 => true,
				3 => null,
				4 => [],
			],
			'not found, flat, prefix' => [
				0 => self::$array,
				1 => ['NOT FOUND'],
				2 => true,
				3 => true,
				4 => [
					'NOT FOUND' => [],
				],
			],
			// multi
			'multiple found, standard' => [
				0 => self::$array,
				1 => ['same'],
				2 => null,
				3 => null,
				4 => [
					[
						'value' => 'same',
						'path' => ['a', 'same', ],
					],
					[
						'value' => 'same',
						'path' => ['same', ],
					],
					[
						'value' => 'same',
						'path' => ['deep', 'sub', 'same', ],
					],
				]
			],
			'multiple found, flat' => [
				0 => self::$array,
				1 => ['same'],
				2 => true,
				3 => null,
				4 => ['same', 'same', 'same', ],
			],
			// search with multiple
			'search multiple, standard' => [
				0 => self::$array,
				1 => ['single', 'nested'],
				2 => null,
				3 => null,
				4 => [
					[
						'value' => 'single',
						'path' => ['single'],
					],
					[
						'value' => 'bar',
						'path' => ['deep', 'sub', 'nested', ],
					],
				],
			],
			'search multiple, prefix' => [
				0 => self::$array,
				1 => ['single', 'nested'],
				2 => null,
				3 => true,
				4 => [
					'single' => [
						[
							'value' => 'single',
							'path' => ['single'],
						],
					],
					'nested' => [
						[
							'value' => 'bar',
							'path' => ['deep', 'sub', 'nested', ],
						],
					],
				],
			],
			'search multiple, flat' => [
				0 => self::$array,
				1 => ['single', 'nested'],
				2 => true,
				3 => null,
				4 => [
					'single', 'bar',
				],
			],
			'search multiple, flat, prefix' => [
				0 => self::$array,
				1 => ['single', 'nested'],
				2 => true,
				3 => true,
				4 => [
					'single' => ['single', ],
					'nested' => ['bar', ],
				],
			],
		];
	}

	/**
	 * provides array listing for the merge test
	 *
	 * @return array
	 */
	public function arrayMergeRecursiveProvider(): array
	{
		return [
			// 0: expected
			// 1..n: to merge arrays
			// n+1: trigger for handle keys as string
			'two arrays' => [
				['a' => 1, 'b' => 2, 'c' => 3],
				['a' => 1, 'b' => 2],
				['b' => 2, 'c' => 3],
			],
			'two arrays, string flag' => [
				['a' => 1, 'b' => 2, 'c' => 3],
				['a' => 1, 'b' => 2],
				['b' => 2, 'c' => 3],
				true,
			],
			// non hash arrays
			'non hash array merge, no string flag' => [
				[3, 4, 5],
				[1, 2, 3],
				[3, 4, 5],
			],
			'non hash array merge, string flag' => [
				[1, 2, 3, 3, 4, 5],
				[1, 2, 3],
				[3, 4, 5],
				true
			],
		];
	}

	/**
	 * for warning checks
	 *
	 * @return array
	 */
	public function arrayMergeRecursiveProviderWarning(): array
	{
		return [
			// error <2 arguments
			'too view arguments' => [
				'ArgumentCountError',
				'arrayMergeRecursive needs two or more array arguments',
				[1]
			],
			// error <2 arrays
			'only one array' => [
				'ArgumentCountError',
				'arrayMergeRecursive needs two or more array arguments',
				[1],
				true,
			],
			// error element is not array
			'non array between array' => [
				'TypeError',
				'arrayMergeRecursive encountered a non array argument',
				[1],
				'string',
				[2]
			],
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
	 * @param string|int|null $needle
	 * @param array $input
	 * @param string|int|null $key_search_for
	 * @param bool $flag
	 * @return void
	 */
	public function testArraySearchRecursiveAll(string|int|null $needle, array $input, string|int|null $key_search_for, bool $flag, array $expected): void
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
	 * @param string|int|bool|array<string|int|bool> $value
	 * @param bool $strict
	 * @param bool $expected
	 * @return void
	 */
	public function testArraySearchSimple(array $input, string|int $key, string|int|bool|array $value, bool $strict, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arraySearchSimple($input, $key, $value, $strict)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers::arraySearchKey
	 * @dataProvider arraySearchKeyProvider
	 * @testdox arraySearchKey Search array with keys and flat: $flat, prefix: $prefix [$_dataName]
	 *
	 * @param  array $input
	 * @param  array $needles
	 * @param  bool|null $flat
	 * @param  bool|null $prefix
	 * @param  array $expected
	 * @return void
	 */
	public function testArraySearchKey(
		array $input,
		array $needles,
		?bool $flat,
		?bool $prefix,
		array $expected
	): void {
		if ($flat === null && $prefix === null) {
			$result = \CoreLibs\Combined\ArrayHandler::arraySearchKey($input, $needles);
		} elseif ($flat === null) {
			$result = \CoreLibs\Combined\ArrayHandler::arraySearchKey($input, $needles, prefix: $prefix);
		} elseif ($prefix === null) {
			$result = \CoreLibs\Combined\ArrayHandler::arraySearchKey($input, $needles, flat: $flat);
		} else {
			$result = \CoreLibs\Combined\ArrayHandler::arraySearchKey($input, $needles, $flat, $prefix);
		}
		// print "E: " . print_r($expected, true) . "\n";
		// print "R: " . print_r($result, true) . "\n";
		$this->assertEquals(
			$expected,
			$result
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayMergeRecursive
	 * @dataProvider arrayMergeRecursiveProvider
	 * @testdox arrayMergeRecursive ... [$_dataName]
	 *
	 * @return void
	 *
	 */
	public function testArrayMergeRecursive(): void
	{
		$arrays = func_get_args();
		// first is expected array, always
		$expected = array_shift($arrays);
		$output = \CoreLibs\Combined\ArrayHandler::arrayMergeRecursive(
			...$arrays
		);
		$this->assertEquals(
			$expected,
			$output
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayMergeRecursive
	 * @dataProvider arrayMergeRecursiveProviderWarning
	 * @testdox arrayMergeRecursive with E_USER_WARNING [$_dataName]
	 *
	 * @return void
	 */
	public function testArrayMergeRecursiveWarningA(): void
	{
		// set_error_handler(
		// 	static function (int $errno, string $errstr): never {
		// 		throw new Exception($errstr, $errno);
		// 	},
		// 	E_USER_WARNING
		// );

		$arrays = func_get_args();
		// first is expected warning
		$exception = array_shift($arrays);
		$warning = array_shift($arrays);

		// phpunit 10.0 compatible
		$this->expectException($exception);
		$this->expectExceptionMessage($warning);

		\CoreLibs\Combined\ArrayHandler::arrayMergeRecursive(...$arrays);

		restore_error_handler();
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
	 * @param string $search
	 * @param array $expected
	 * @return void
	 */
	public function testArrayFlatForKey(array $input, string $search, array $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arrayFlatForKey($input, $search)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerArrayGetNextPrevKey(): array
	{
		return [
			'find, ok' => [
				'input' => [
					'a' => 'First',
					'b' => 'Second',
					'c' => 'Third',
				],
				'b',
				'a',
				'c'
			],
			'find, first' => [
				'input' => [
					'a' => 'First',
					'b' => 'Second',
					'c' => 'Third',
				],
				'a',
				null,
				'b'
			],
			'find, last' => [
				'input' => [
					'a' => 'First',
					'b' => 'Second',
					'c' => 'Third',
				],
				'c',
				'b',
				null
			],
			'find, not found' => [
				'input' => [
					'a' => 'First',
					'b' => 'Second',
					'c' => 'Third',
				],
				'z',
				null,
				null
			],
			'int, index' => [
				'input' => [
					'a',
					'b',
					'c'
				],
				1,
				0,
				2
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayGetPrevKey, ::arrayGetNextKey
	 * @dataProvider providerArrayGetNextPrevKey
	 * @testdox arrayGetNextPrevKey get next/prev key for $search wtih $expected_prev/$expected_next [$_dataName]
	 *
	 * @param  array           $input
	 * @param  int|string      $search
	 * @param  int|string|null $expected_prev
	 * @param  int|string|null $expected_next
	 * @return void
	 */
	public function testArrayGetNextPrevKey(
		array $input,
		int|string $search,
		int|string|null $expected_prev,
		int|string|null $expected_next
	): void {
		$this->assertEquals(
			$expected_prev,
			\CoreLibs\Combined\ArrayHandler::arrayGetPrevKey($input, $search),
			'Find prev key in array'
		);
		$this->assertEquals(
			$expected_next,
			\CoreLibs\Combined\ArrayHandler::arrayGetNextKey($input, $search),
			'Find next key in array'
		);
	}

	public function providerReturnMatchingKeyOnley(): array
	{
		return [
			'limited entries' => [
				[
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar'
				],
				[
					'a', 'b'
				],
				[
					'a' => 'foo',
					'b' => 'bar',
				],
			],
			'limited entries, with one wrong key' => [
				[
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar'
				],
				[
					'a', 'b', 'f'
				],
				[
					'a' => 'foo',
					'b' => 'bar',
				],
			],
			'wrong keys only' => [
				[
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar'
				],
				[
					'f', 'f'
				],
				[
				],
			],
			'empty keys' => [
				[
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar'
				],
				[],
				[
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar'
				],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayReturnMatchingKeyOnly
	 * @dataProvider providerReturnMatchingKeyOnley
	 * @testdox arrayReturnMatchingKeyOnly get only selected key entries from array [$_dataName]
	 *
	 * @param  array $input
	 * @param  array $key_list
	 * @param  array $expected
	 * @return void
	 */
	public function testArrayReturnMatchingKeyOnly(
		array $input,
		array $key_list,
		array $expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arrayReturnMatchingKeyOnly(
				$input,
				$key_list
			)
		);
	}

	/**
	 * provider for arrayModifyKey
	 *
	 * @return array<string,array<mixed>>
	 */
	public function providerArrayModifyKey(): array
	{
		return [
			'prefix and suffix add' => [
				'array' => [
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar',
				],
				'prefix' => 'Prefix: ',
				'suffix' => '.suffix',
				'expected' => [
					'Prefix: a.suffix' => 'foo',
					'Prefix: b.suffix' => 'bar',
					'Prefix: c.suffix' => 'foobar',
				],
			],
			'prefix add only' => [
				'array' => [
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar',
				],
				'prefix' => 'Prefix: ',
				'suffix' => '',
				'expected' => [
					'Prefix: a' => 'foo',
					'Prefix: b' => 'bar',
					'Prefix: c' => 'foobar',
				],
			],
			'suffix add only' => [
				'array' => [
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar',
				],
				'prefix' => '',
				'suffix' => '.suffix',
				'expected' => [
					'a.suffix' => 'foo',
					'b.suffix' => 'bar',
					'c.suffix' => 'foobar',
				],
			],
			'empty array' => [
				'array' => [],
				'prefix' => '',
				'suffix' => '.suffix',
				'expected' => [],
			],
			'no suffix or prefix' => [
				'array' => [
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar',
				],
				'prefix' => '',
				'suffix' => '',
				'expected' => [
					'a' => 'foo',
					'b' => 'bar',
					'c' => 'foobar',
				],
			],
			'integer index mixed' => [
				'array' => [
					'a' => 'foo',
					'b' => 'bar',
					3 => 'foobar',
				],
				'prefix' => '',
				'suffix' => '.suffix',
				'expected' => [
					'a.suffix' => 'foo',
					'b.suffix' => 'bar',
					'3.suffix' => 'foobar',
				],
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::arrayModifyKey
	 * @dataProvider providerArrayModifyKey
	 * @testdox arrayModifyKey check that key is correctly modified with $key_mod_prefix and $key_mod_suffix [$_dataName]
	 *
	 * @param  array<mixed>  $in_array
	 * @param  string $key_mod_prefix
	 * @param  string $key_mod_suffix
	 * @param  array<mixed>  $expected
	 * @return void
	 */
	public function testArrayModifyKey(
		array $in_array,
		string $key_mod_prefix,
		string $key_mod_suffix,
		array $expected
	): void {
		$this->assertEquals(
			$expected,
			\CoreLibs\Combined\ArrayHandler::arrayModifyKey($in_array, $key_mod_prefix, $key_mod_suffix)
		);
	}

	/**
	 * sort
	 *
	 * @return array
	 */
	public function providerSortArray(): array
	{
		$unsorted = [9, 5, 'A', 4, 'B', 6, 'c', 'C', 'a'];
		// for lower case the initial order of the elmenet is important:
		// A, a => A, a
		// d, D => d, D
		$unsorted_keys = ['A' => 9, 'B' => 5, 'C' => 'A', 'D' => 4, 'E' => 'B', 'F' => 6, 'G' => 'c', 'H' => 'C', 'I' => 'a'];
		return [
			// sort default
			'sort default' => [
				$unsorted,
				null,
				null,
				null,
				[4, 5, 6, 9, 'A', 'B', 'C', 'a', 'c'],
			],
			// sort param set
			'sort param set' => [
				$unsorted,
				false,
				false,
				false,
				[4, 5, 6, 9, 'A', 'B', 'C', 'a', 'c'],
			],
			// sort lower case
			'sort lower case' => [
				$unsorted,
				true,
				false,
				false,
				[4, 5, 6, 9, 'A', 'a', 'B', 'c', 'C'],
			],
			// sort reverse
			'sort reverse' => [
				$unsorted,
				false,
				true,
				false,
				['c', 'a', 'C', 'B', 'A', 9, 6, 5, 4],
			],
			// sort lower case + reverse
			'sort lower case + reverse' => [
				$unsorted,
				true,
				true,
				false,
				['c', 'C', 'B', 'A', 'a', 9, 6, 5, 4],
			],
			// keys, do not maintain, default
			'keys, do not maintain, default' => [
				$unsorted_keys,
				false,
				false,
				false,
				[4, 5, 6, 9, 'A', 'B', 'C', 'a', 'c'],
			],
			// sort maintain keys
			'sort maintain keys' => [
				$unsorted_keys,
				false,
				false,
				true,
				[
					'D' => 4,
					'B' => 5,
					'F' => 6,
					'A' => 9,
					'C' => 'A',
					'E' => 'B',
					'H' => 'C',
					'I' => 'a',
					'G' => 'c'
				],
			],
			// sort maintain keys + lower case
			'sort maintain keys + lower case' => [
				$unsorted_keys,
				true,
				false,
				true,
				[
					'D' => 4,
					'B' => 5,
					'F' => 6,
					'A' => 9,
					'C' => 'A',
					'I' => 'a',
					'E' => 'B',
					'H' => 'C',
					'G' => 'c'
				],
			],
			// sort maintain keys + reverse
			'sort maintain keys + reverse' => [
				$unsorted_keys,
				false,
				true,
				true,
				[
					'G' => 'c',
					'H' => 'C',
					'E' => 'B',
					'I' => 'a',
					'C' => 'A',
					'A' => 9,
					'F' => 6,
					'B' => 5,
					'D' => 4,
				],
			],
			// sort maintain keys + lower case + reverse
			'sort maintain keys + lower case + reverse' => [
				$unsorted_keys,
				true,
				true,
				true,
				[
					'G' => 'c',
					'H' => 'C',
					'E' => 'B',
					'I' => 'a',
					'C' => 'A',
					'A' => 9,
					'F' => 6,
					'B' => 5,
					'D' => 4,
				],
			],
			// emtpy
			'empty' => [
				[],
				false,
				false,
				false,
				[]
			],
			// with nulls
			'null entries' => [
				['d', null, 'a', null, 1],
				false,
				false,
				false,
				[null, null, 1, 'a', 'd'],
			],
			// double entries
			'double entries' => [
				[1, 2, 2, 1, 'B', 'A', 'a', 'b', 'A', 'B', 'b', 'a'],
				false,
				false,
				false,
				[1, 1, 2, 2, 'A', 'A', 'B', 'B', 'a', 'a', 'b', 'b'],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::sortArray
	 * @dataProvider providerSortArray
	 * @testdox sortArray sort $input with lower case $lower_case, reverse $reverse, maintain keys $maintain_keys with expeted $expected [$_dataName]
	 *
	 * @param  array $input
	 * @param  ?bool $lower_case
	 * @param  ?bool $reverse
	 * @param  ?bool $maintain_keys
	 * @param  array $expected
	 * @return void
	 */
	public function testSortArray(array $input, ?bool $lower_case, ?bool $reverse, ?bool $maintain_keys, array $expected): void
	{
		$original = $input;
		if ($lower_case === null && $reverse === null && $maintain_keys === null) {
			$sorted_array = \CoreLibs\Combined\ArrayHandler::sortArray($input);
		} else {
			$sorted_array = \CoreLibs\Combined\ArrayHandler::sortArray($input, $lower_case, $reverse, $maintain_keys);
		}
		$expected_count = count($expected);
		$this->assertIsArray(
			$sorted_array,
			'sortArray: result not array'
		);
		$this->assertCount(
			$expected_count,
			$sorted_array,
			'sortArray: count not matching'
		);
		$this->assertEquals(
			$expected,
			$sorted_array,
			'sortArray: result not matching'
		);
		$this->assertEquals(
			$original,
			$input,
			'sortArray: original - input was modified'
		);
		if ($maintain_keys) {
			$this->assertEqualsCanonicalizing(
				array_keys($input),
				array_keys($sorted_array),
				'sortArray: keys are not modified',
			);
		}
		if ($input != []) {
			// we only care about array values
			$this->assertNotEquals(
				array_values($input),
				array_values($sorted_array),
				'sortArray: output - input was modified'
			);
		}
	}

/**
	 * sort
	 *
	 * @return array
	 */
	public function providerKsortArray(): array
	{
		// for lower case the initial order of the elmenet is important:
		// A, a => A, a
		// d, D => d, D
		$unsorted_keys = [
			9 => 'A',
			5 => 'B',
			'A' => 'C',
			4 => 'D',
			'B' => 'E',
			6 => 'F',
			'c' => 'G',
			'C' => 'H',
			'a' => 'I',
		];
		return [
			// sort keys
			'sort keys' => [
				$unsorted_keys,
				false,
				false,
				[
					4 => 'D',
					5 => 'B',
					6 => 'F',
					9 => 'A',
					'A' => 'C',
					'B' => 'E',
					'C' => 'H',
					'a' => 'I',
					'c' => 'G',
				],
			],
			// sort keys + lower case
			'sort keys + lower case' => [
				$unsorted_keys,
				true,
				false,
				[
					4 => 'D',
					5 => 'B',
					6 => 'F',
					9 => 'A',
					'A' => 'C',
					'a' => 'I',
					'B' => 'E',
					'c' => 'G',
					'C' => 'H',
				],
			],
			// sort keys + reverse
			'sort keys + reverse' => [
				$unsorted_keys,
				false,
				true,
				[
					'c' => 'G',
					'a' => 'I',
					'C' => 'H',
					'B' => 'E',
					'A' => 'C',
					9 => 'A',
					6 => 'F',
					5 => 'B',
					4 => 'D',
				],
			],
			// sort keys + lower case + reverse
			'sort keys + lower case + reverse' => [
				$unsorted_keys,
				true,
				true,
				[
					'C' => 'H',
					'c' => 'G',
					'B' => 'E',
					'a' => 'I',
					'A' => 'C',
					9 => 'A',
					6 => 'F',
					5 => 'B',
					4 => 'D',
				],
			],
			// emtpy
			'empty' => [
				[],
				false,
				false,
				[]
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::ksortArray
	 * @dataProvider providerKsortArray
	 * @testdox ksortArray sort $input with lower case $lower_case, reverse $reverse with expeted $expected [$_dataName]
	 *
	 * @param  array $input
	 * @param  ?bool $lower_case
	 * @param  ?bool $reverse
	 * @param  array $expected
	 * @return void
	 */
	public function testKsortArray(array $input, ?bool $lower_case, ?bool $reverse, array $expected): void
	{
		$original = $input;
		if ($lower_case === null && $reverse === null) {
			$sorted_array = \CoreLibs\Combined\ArrayHandler::ksortArray($input);
		} else {
			$sorted_array = \CoreLibs\Combined\ArrayHandler::ksortArray($input, $lower_case, $reverse);
		}
		$expected_count = count($expected);
		$this->assertIsArray(
			$sorted_array,
			'ksortArray: result not array'
		);
		$this->assertCount(
			$expected_count,
			$sorted_array,
			'ksortArray: count not matching'
		);
		$this->assertEquals(
			$expected,
			$sorted_array,
			'ksortArray: result not matching'
		);
		$this->assertEquals(
			$original,
			$input,
			'ksortArray: original - input was modified'
		);
		$this->assertEqualsCanonicalizing(
			array_values($original),
			array_values($sorted_array),
			'ksortArray: values are not modified'
		);
		if ($input != []) {
			// we only care about array keys
			$this->assertNotEquals(
				array_keys($input),
				array_keys($sorted_array),
				'sortArray: output - input was modified'
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerFindArraysMissingKey(): array
	{
		$search_array = [
			'table_lookup' => [
				'match' => [
					['param' => 'access_d_cd', 'data' => 'a_cd', 'time_validation' => 'on_load',],
					['param' => 'other_block', 'data' => 'b_cd'],
					['pflaume' => 'other_block', 'data' => 'c_cd'],
					['param' => 'third_block', 'data' => 'd_cd', 'time_validation' => 'cool'],
					['special' => 'other_block', 'data' => 'e_cd', 'time_validation' => 'other'],
				]
			]
		];
		return [
			'find, no key set' => [
				$search_array,
				'other_block',
				'time_validation',
				null,
				null,
				[
					[
						'content' => [
							'param' => 'other_block',
							'data' => 'b_cd',
						],
						'path' => 'table_lookup:match:1',
						'missing_key' => ['time_validation'],
					],
					[
						'content' => [
							'data' => 'c_cd',
							'pflaume' => 'other_block',
						],
						'path' => 'table_lookup:match:2',
						'missing_key' => ['time_validation'],
					],
				]
			],
			'find, key set' => [
				$search_array,
				'other_block',
				'time_validation',
				'pflaume',
				null,
				[
					[
						'content' => [
							'data' => 'c_cd',
							'pflaume' => 'other_block',
						],
						'path' => 'table_lookup:match:2',
						'missing_key' => ['time_validation'],
					],
				]
			],
			'find, key set, different separator' => [
				$search_array,
				'other_block',
				'time_validation',
				'pflaume',
				'#',
				[
					[
						'content' => [
							'data' => 'c_cd',
							'pflaume' => 'other_block',
						],
						'path' => 'table_lookup#match#2',
						'missing_key' => ['time_validation'],
					],
				]
			],
			'find, key set, multiple check' => [
				$search_array,
				'other_block',
				['data', 'time_validation'],
				'pflaume',
				null,
				[
					[
						'content' => [
							'data' => 'c_cd',
							'pflaume' => 'other_block',
						],
						'path' => 'table_lookup:match:2',
						'missing_key' => ['time_validation'],
					],
				]
			],
			'has set' => [
				$search_array,
				'access_d_cd',
				'time_validation',
				null,
				null,
				[]
			],
			'not found' => [
				$search_array,
				'not_found',
				'value',
				null,
				null,
				[]
			],
			'empty' => [
				[],
				'something',
				'other',
				null,
				null,
				[]
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::findArraysMissingKey
	 * @dataProvider providerFindArraysMissingKey
	 * @testdox findArraysMissingKey $input find $search_value with $search_key and missing $required_key [$_dataName]
	 *
	 * @param  array<mixed>          $input
	 * @param  string|int|float|bool $search_value
	 * @param  string|array<string>  $required_key
	 * @param  string|null           $search_key
	 * @param  string|null           $path_separator
	 * @param  array<mixed>          $expected
	 * @return void
	 */
	public function testFindArraysMissingKey(
		array $input,
		string|int|float|bool $search_value,
		string|array $required_key,
		?string $search_key,
		?string $path_separator,
		array $expected
	): void {
		if ($path_separator === null) {
			$result = \CoreLibs\Combined\ArrayHandler::findArraysMissingKey(
				$input,
				$search_value,
				$required_key,
				$search_key
			);
		} else {
			$result = \CoreLibs\Combined\ArrayHandler::findArraysMissingKey(
				$input,
				$search_value,
				$required_key,
				$search_key,
				$path_separator
			);
		}
		$this->assertEquals(
			$expected,
			$result
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerSelectArrayFromOption(): array
	{
		$search_array = [
			'a' => [
				'lookup' => 1,
				'value' => 'Foo',
				'other' => 'Bar',
				'strict' => '2',
			],
			'b' => [
				'lookup' => 1,
				'value' => 'AAA',
				'other' => 'Other',
				'strict' => 2,
			],
			'c' => [
				'lookup' => 0,
				'value' => 'CCC',
				'other' => 'OTHER',
			],
			'd' => [
				'd-1' => [
					'lookup' => 1,
					'value' => 'D SUB 1',
					'other' => 'Other B',
				],
				'd-2' => [
					'lookup' => 0,
					'value' => 'D SUB 2',
					'other' => 'Other B',
				],
				'more' => [
					'd-more-1' => [
						'lookup' => 1,
						'value' => 'D MORE SUB 1',
						'other' => 'Other C',
					],
					'd-more-2' => [
						'lookup' => 0,
						'value' => 'D MORE SUB 0',
						'other' => 'Other C',
					],
				]
			]
		];
		/*
		0: input
		1: lookup
		2: search
		3: strict [false]
		4: case insensitive [false]
		5: recursive [false]
		6: flat_result [true]
		7: flat_separator [:]
		8: expected
		*/
		return [
			'search, flat with found' => [
				$search_array,
				'lookup' => 'lookup',
				'search' => 1,
				'strict' => false,
				'case_insenstivie' => false,
				'recursive' => false,
				'flat_result' => true,
				'flag_separator' => null,
				[
					'a' => [
						'lookup' => 1,
						'value' => 'Foo',
						'other' => 'Bar',
						'strict' => '2',
					],
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
				]
			],
			'search, recusrive with found' => [
				$search_array,
				'lookup' => 'lookup',
				'search' => 1,
				'strict' => false,
				'case_insenstivie' => false,
				'recursive' => true,
				'flat_result' => true,
				'flag_separator' => null,
				[
					'a' => [
						'lookup' => 1,
						'value' => 'Foo',
						'other' => 'Bar',
						'strict' => '2',
					],
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
					'd:d-1' => [
						'lookup' => 1,
						'value' => 'D SUB 1',
						'other' => 'Other B',
					],
					'd:more:d-more-1' => [
						'lookup' => 1,
						'value' => 'D MORE SUB 1',
						'other' => 'Other C',
					],
				]
			],
			'search, recusrive with found, other separator' => [
				$search_array,
				'lookup' => 'lookup',
				'search' => 1,
				'strict' => false,
				'case_insenstivie' => false,
				'recursive' => true,
				'flat_result' => true,
				'flag_separator' => '+',
				[
					'a' => [
						'lookup' => 1,
						'value' => 'Foo',
						'other' => 'Bar',
						'strict' => '2',
					],
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
					'd+d-1' => [
						'lookup' => 1,
						'value' => 'D SUB 1',
						'other' => 'Other B',
					],
					'd+more+d-more-1' => [
						'lookup' => 1,
						'value' => 'D MORE SUB 1',
						'other' => 'Other C',
					],
				]
			],
			'search, recusrive with found, not flat result' => [
				$search_array,
				'lookup' => 'lookup',
				'search' => 1,
				'strict' => false,
				'case_insenstivie' => false,
				'recursive' => true,
				'flat_result' => false,
				'flag_separator' => null,
				[
					'a' => [
						'lookup' => 1,
						'value' => 'Foo',
						'other' => 'Bar',
						'strict' => '2',
					],
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
					'd' => [
						'd-1' => [
							'lookup' => 1,
							'value' => 'D SUB 1',
							'other' => 'Other B',
						],
						'more' => [
							'd-more-1' => [
								'lookup' => 1,
								'value' => 'D MORE SUB 1',
								'other' => 'Other C',
							],
						],
					],
				],
			],
			'search case insensitive' => [
				$search_array,
				'lookup' => 'other',
				'search' => 'Other',
				'strict' => false,
				'case_insenstivie' => true,
				'recursive' => false,
				'flat_result' => true,
				'flag_separator' => null,
				[
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
					'c' => [
						'lookup' => 0,
						'value' => 'CCC',
						'other' => 'OTHER',
					],
				]
			],
			'search case sensitiv' => [
				$search_array,
				'lookup' => 'other',
				'search' => 'Other',
				'strict' => false,
				'case_insenstivie' => false,
				'recursive' => false,
				'flat_result' => true,
				'flag_separator' => null,
				[
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
				]
			],
			'search strict' => [
				$search_array,
				'lookup' => 'strict',
				'search' => '2',
				'strict' => true,
				'case_insenstivie' => false,
				'recursive' => false,
				'flat_result' => true,
				'flag_separator' => null,
				[
					'a' => [
						'lookup' => 1,
						'value' => 'Foo',
						'other' => 'Bar',
						'strict' => '2',
					],
				]
			],
			'search not strict' => [
				$search_array,
				'lookup' => 'strict',
				'search' => '2',
				'strict' => false,
				'case_insenstivie' => false,
				'recursive' => false,
				'flat_result' => true,
				'flag_separator' => null,
				[
					'a' => [
						'lookup' => 1,
						'value' => 'Foo',
						'other' => 'Bar',
						'strict' => '2',
					],
					'b' => [
						'lookup' => 1,
						'value' => 'AAA',
						'other' => 'Other',
						'strict' => 2,
					],
				]
			],
			'empty' => [
				[],
				'something',
				'NOT_SET_AT_ALL',
				false,
				false,
				false,
				true,
				null,
				[]
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::selectArrayFromOption
	 * @dataProvider providerSelectArrayFromOption
	 * @testdox selectArrayFromOption $input find $lookup with $search, strict: $strict, case sensitive: $case_sensitive, recursive: $recursive, flag result: $flag_result, flag separator: $flat_separator amd expected $expected [$_dataName]
	 *
	 * @param  array                 $input
	 * @param  string                $lookup
	 * @param  int|string|float|bool $search
	 * @param  bool                  $strict
	 * @param  bool                  $case_sensitive
	 * @param  bool                  $recursive
	 * @param  bool                  $flat_result
	 * @param  string|null           $flat_separator
	 * @param  array                 $expected
	 * @return void
	 */
	public function testSelectArrayFromOption(
		array $input,
		string $lookup,
		int|string|float|bool $search,
		bool $strict,
		bool $case_sensitive,
		bool $recursive,
		bool $flat_result,
		?string $flat_separator,
		array $expected
	): void {
		if ($flat_separator === null) {
			$result = \CoreLibs\Combined\ArrayHandler::selectArrayFromOption(
				$input,
				$lookup,
				$search,
				$strict,
				$case_sensitive,
				$recursive,
				$flat_result
			);
		} else {
			$result = \CoreLibs\Combined\ArrayHandler::selectArrayFromOption(
				$input,
				$lookup,
				$search,
				$strict,
				$case_sensitive,
				$recursive,
				$flat_result,
				$flat_separator
			);
		}
		$this->assertEquals(
			$expected,
			$result
		);
	}
}

// __END__
