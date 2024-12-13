<?php

// because we have long testdox lines
// phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use Exception;
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
		'single' => 'single',
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
		3: bool $flag,
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
	 * @param string|int|bool $value
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
}

// __END__
