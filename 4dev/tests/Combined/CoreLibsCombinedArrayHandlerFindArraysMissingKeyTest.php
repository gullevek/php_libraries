<?php

// This code was created by Claude Sonnet 4

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Combined\ArrayHandler;

class CoreLibsCombinedArrayHandlerFindArraysMissingKeyTest extends TestCase
{
	private const DATA_SEPARATOR = ':'; // Updated to match your class's separator

	/**
	 * Test finding missing single key when searching by value without specific key
	 */
	public function testFindMissingSingleKeyWithValueSearch()
	{
		$array = [
			'item1' => [
				'name' => 'John',
				'age' => 25
				// missing 'email' key
			],
			'item2' => [
				'name' => 'Jane',
				'age' => 30,
				'email' => 'jane@example.com'
			],
			'item3' => [
				'name' => 'John', // same value as item1
				'age' => 35,
				'email' => 'john2@example.com'
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'John', 'email');

		$this->assertCount(1, $result);
		$this->assertEquals($array['item1'], $result[0]['content']);
		$this->assertEquals('item1', $result[0]['path']);
		$this->assertEquals(['email'], $result[0]['missing_key']);
	}

	/**
	 * Test finding missing single key when searching by specific key-value pair
	 */
	public function testFindMissingSingleKeyWithKeyValueSearch()
	{
		$array = [
			'user1' => [
				'id' => 1,
				'name' => 'Alice'
				// missing 'status' key
			],
			'user2' => [
				'id' => 2,
				'name' => 'Bob',
				'status' => 'active'
			],
			'user3' => [
				'id' => 1, // same id as user1
				'name' => 'Charlie',
				'status' => 'inactive'
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 1, 'status', 'id');

		$this->assertCount(1, $result);
		$this->assertEquals($array['user1'], $result[0]['content']);
		$this->assertEquals('user1', $result[0]['path']);
		$this->assertEquals(['status'], $result[0]['missing_key']);
	}

	/**
	 * Test finding missing multiple keys
	 */
	public function testFindMissingMultipleKeys()
	{
		$array = [
			'record1' => [
				'name' => 'Test',
				'value' => 100
				// missing both 'date' and 'status' keys
			],
			'record2' => [
				'name' => 'Test',
				'value' => 200,
				'date' => '2023-01-01'
				// missing 'status' key
			],
			'record3' => [
				'name' => 'Test',
				'value' => 300,
				'date' => '2023-01-02',
				'status' => 'complete'
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'Test', ['date', 'status']);

		$this->assertCount(2, $result);

		// First result should be record1 missing both keys
		$this->assertEquals($array['record1'], $result[0]['content']);
		$this->assertEquals('record1', $result[0]['path']);
		$this->assertContains('date', $result[0]['missing_key']);
		$this->assertContains('status', $result[0]['missing_key']);
		$this->assertCount(2, $result[0]['missing_key']);

		// Second result should be record2 missing status key
		$this->assertEquals($array['record2'], $result[1]['content']);
		$this->assertEquals('record2', $result[1]['path']);
		$this->assertEquals(['status'], $result[1]['missing_key']);
	}

	/**
	 * Test with nested arrays
	 */
	public function testFindMissingKeyInNestedArrays()
	{
		$array = [
			'section1' => [
				'items' => [
					'item1' => [
						'name' => 'Product A',
						'price' => 99.99
						// missing 'category' key
					],
					'item2' => [
						'name' => 'Product B',
						'price' => 149.99,
						'category' => 'electronics'
					]
				]
			],
			'section2' => [
				'data' => [
					'name' => 'Product A', // same name as nested item
					'category' => 'books'
				]
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'Product A', 'category');

		$this->assertCount(1, $result);
		$this->assertEquals($array['section1']['items']['item1'], $result[0]['content']);
		$this->assertEquals('section1:items:item1', $result[0]['path']);
		$this->assertEquals(['category'], $result[0]['missing_key']);
	}

	/**
	 * Test when no arrays are missing the required key
	 */
	public function testNoMissingKeys()
	{
		$array = [
			'item1' => [
				'name' => 'John',
				'email' => 'john@example.com'
			],
			'item2' => [
				'name' => 'Jane',
				'email' => 'jane@example.com'
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'John', 'email');

		$this->assertEmpty($result);
	}

	/**
	 * Test when search value is not found in any array
	 */
	public function testSearchValueNotFound()
	{
		$array = [
			'item1' => [
				'name' => 'John',
				'age' => 25
			],
			'item2' => [
				'name' => 'Jane',
				'age' => 30
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'Bob', 'email');

		$this->assertEmpty($result);
	}

	/**
	 * Test with different data types for search value
	 */
	public function testDifferentSearchValueTypes()
	{
		$array = [
			'item1' => [
				'active' => true,
				'count' => 5
				// missing 'label' key
			],
			'item2' => [
				'active' => false,
				'count' => 10,
				'label' => 'test'
			],
			'item3' => [
				'active' => true, // same boolean as item1
				'count' => 15,
				'label' => 'another'
			]
		];

		// Test with boolean
		$result = ArrayHandler::findArraysMissingKey($array, true, 'label', 'active');
		$this->assertCount(1, $result);
		$this->assertEquals('item1', $result[0]['path']);

		// Test with integer
		$result = ArrayHandler::findArraysMissingKey($array, 5, 'label', 'count');
		$this->assertCount(1, $result);
		$this->assertEquals('item1', $result[0]['path']);
	}

	/**
	 * Test with empty array
	 */
	public function testEmptyArray()
	{
		$array = [];

		$result = ArrayHandler::findArraysMissingKey($array, 'test', 'key');

		$this->assertEmpty($result);
	}

	/**
	 * Test with array containing non-array values
	 */
	public function testMixedArrayTypes()
	{
		$array = [
			'string_value' => 'hello',
			'numeric_value' => 123,
			'array_value' => [
				'name' => 'test',
				// missing 'type' key
			],
			'another_array' => [
				'name' => 'test',
				'type' => 'example'
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'test', 'type');

		$this->assertCount(1, $result);
		$this->assertEquals($array['array_value'], $result[0]['content']);
		$this->assertEquals('array_value', $result[0]['path']);
		$this->assertEquals(['type'], $result[0]['missing_key']);
	}

	/**
	 * Test path building with deeper nesting
	 */
	public function testDeepNestingPathBuilding()
	{
		$array = [
			'level1' => [
				'level2' => [
					'level3' => [
						'items' => [
							'target_item' => [
								'name' => 'deep_test',
								// missing 'required_field'
							]
						]
					]
				]
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'deep_test', 'required_field');

		$this->assertCount(1, $result);
		$this->assertEquals('level1:level2:level3:items:target_item', $result[0]['path']);
	}

	/**
	 * Test with custom path separator
	 */
	public function testCustomPathSeparator()
	{
		$array = [
			'level1' => [
				'level2' => [
					'item' => [
						'name' => 'test',
						// missing 'type' key
					]
				]
			]
		];

		$result = ArrayHandler::findArraysMissingKey($array, 'test', 'type', null, '/');

		$this->assertCount(1, $result);
		$this->assertEquals('level1/level2/item', $result[0]['path']);
	}

	/**
	 * Test default path separator behavior
	 */
	public function testDefaultPathSeparator()
	{
		$array = [
			'parent' => [
				'child' => [
					'name' => 'test',
					// missing 'value' key
				]
			]
		];

		// Using default separator (should be ':')
		$result = ArrayHandler::findArraysMissingKey($array, 'test', 'value');

		$this->assertCount(1, $result);
		$this->assertEquals('parent:child', $result[0]['path']);
	}

	/**
	 * Test different path separators don't affect search logic
	 */
	public function testPathSeparatorDoesNotAffectSearchLogic()
	{
		$array = [
			'section' => [
				'data' => [
					'id' => 123,
					'name' => 'item'
					// missing 'status'
				]
			]
		];

		// Test with different separators - results should be identical except for path
		$result1 = ArrayHandler::findArraysMissingKey($array, 123, 'status', 'id', ':');
		$result2 = ArrayHandler::findArraysMissingKey($array, 123, 'status', 'id', '.');
		$result3 = ArrayHandler::findArraysMissingKey($array, 123, 'status', 'id', '/');

		$this->assertCount(1, $result1);
		$this->assertCount(1, $result2);
		$this->assertCount(1, $result3);

		// Content and missing_key should be the same
		$this->assertEquals($result1[0]['content'], $result2[0]['content']);
		$this->assertEquals($result1[0]['content'], $result3[0]['content']);
		$this->assertEquals($result1[0]['missing_key'], $result2[0]['missing_key']);
		$this->assertEquals($result1[0]['missing_key'], $result3[0]['missing_key']);

		// Paths should be different based on separator
		$this->assertEquals('section:data', $result1[0]['path']);
		$this->assertEquals('section.data', $result2[0]['path']);
		$this->assertEquals('section/data', $result3[0]['path']);
	}

	/**
	 * test type checking
	 */
	public function testStrictTypeChecking()
	{
		$array = [
			'item1' => [
				'id' => '123', // string
				'name' => 'test'
				// missing 'status'
			],
			'item2' => [
				'id' => 123, // integer
				'name' => 'test2',
				'status' => 'active'
			]
		];

		// Search for integer 123 - should only match item2
		$result = ArrayHandler::findArraysMissingKey($array, 123, 'status', 'id');
		$this->assertEmpty($result); // item2 has the status key

		// Search for string '123' - should only match item1
		$result = ArrayHandler::findArraysMissingKey($array, '123', 'status', 'id');
		$this->assertCount(1, $result);
		$this->assertEquals('item1', $result[0]['path']);
	}
}

// __END__
