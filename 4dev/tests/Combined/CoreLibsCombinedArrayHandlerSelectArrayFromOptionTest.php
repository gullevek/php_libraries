<?php

// create by Claude Sonnet 4

// testRecursiveSearchWithFlatResult had wrong retunr count

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Combined\ArrayHandler;

class CoreLibsCombinedArrayHandlerSelectArrayFromOptionTest extends TestCase
{
	private array $testData;
	private array $nestedTestData;

	protected function setUp(): void
	{
		$this->testData = [
			'item1' => [
				'name' => 'John',
				'age' => 25,
				'status' => 'active',
				'score' => 85.5
			],
			'item2' => [
				'name' => 'jane',
				'age' => 30,
				'status' => 'inactive',
				'score' => 92.0
			],
			'item3' => [
				'name' => 'Bob',
				'age' => 25,
				'status' => 'active',
				'score' => 78.3
			],
			'item4' => [
				'name' => 'Alice',
				'age' => 35,
				'status' => 'pending',
				'score' => 88.7
			]
		];

		$this->nestedTestData = [
			'level1_a' => [
				'name' => 'Level1A',
				'type' => 'parent',
				'children' => [
					'child1' => [
						'name' => 'Child1',
						'type' => 'child',
						'active' => true
					],
					'child2' => [
						'name' => 'Child2',
						'type' => 'child',
						'active' => false
					]
				]
			],
			'level1_b' => [
				'name' => 'Level1B',
				'type' => 'parent',
				'children' => [
					'child3' => [
						'name' => 'Child3',
						'type' => 'child',
						'active' => true,
						'nested' => [
							'deep1' => [
								'name' => 'Deep1',
								'type' => 'deep',
								'active' => true
							]
						]
					]
				]
			],
			'item5' => [
				'name' => 'Direct',
				'type' => 'child',
				'active' => false
			]
		];
	}

	public function testEmptyArrayReturnsEmpty(): void
	{
		$result = ArrayHandler::selectArrayFromOption([], 'name', 'John');
		$this->assertEmpty($result);
	}

	public function testBasicStringSearch(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'name', 'John');

		$this->assertCount(1, $result);
		$this->assertArrayHasKey('item1', $result);
		$this->assertEquals('John', $result['item1']['name']);
	}

	public function testBasicIntegerSearch(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'age', 25);

		$this->assertCount(2, $result);
		$this->assertArrayHasKey('item1', $result);
		$this->assertArrayHasKey('item3', $result);
	}

	public function testBasicFloatSearch(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'score', 85.5);

		$this->assertCount(1, $result);
		$this->assertArrayHasKey('item1', $result);
		$this->assertEquals(85.5, $result['item1']['score']);
	}

	public function testBasicBooleanSearch(): void
	{
		$data = [
			'item1' => ['enabled' => true, 'name' => 'Test1'],
			'item2' => ['enabled' => false, 'name' => 'Test2'],
			'item3' => ['enabled' => true, 'name' => 'Test3']
		];

		$result = ArrayHandler::selectArrayFromOption($data, 'enabled', true);

		$this->assertCount(2, $result);
		$this->assertArrayHasKey('item1', $result);
		$this->assertArrayHasKey('item3', $result);
	}

	public function testStrictComparison(): void
	{
		$data = [
			'item1' => ['value' => '25', 'name' => 'String25'],
			'item2' => ['value' => 25, 'name' => 'Int25'],
			'item3' => ['value' => 25.0, 'name' => 'Float25']
		];

		// Non-strict should match all
		$nonStrictResult = ArrayHandler::selectArrayFromOption($data, 'value', 25, false);
		$this->assertCount(3, $nonStrictResult);

		// Strict should only match exact type
		$strictResult = ArrayHandler::selectArrayFromOption($data, 'value', 25, true);
		$this->assertCount(1, $strictResult);
		$this->assertArrayHasKey('item2', $strictResult);
	}

	public function testCaseInsensitiveSearch(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'name', 'JANE', false, true);

		$this->assertCount(1, $result);
		$this->assertArrayHasKey('item2', $result);
		$this->assertEquals('jane', $result['item2']['name']);
	}

	public function testCaseSensitiveSearch(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'name', 'JANE', false, false);

		$this->assertEmpty($result);
	}

	public function testRecursiveSearchWithFlatResult(): void
	{
		$result = ArrayHandler::selectArrayFromOption(
			$this->nestedTestData,
			'type',
			'child',
			false,
			false,
			true,
			true,
			':*'
		);

		$this->assertCount(4, $result);
		$this->assertArrayHasKey('level1_a:*children:*child1', $result);
		$this->assertArrayHasKey('level1_a:*children:*child2', $result);
		$this->assertArrayHasKey('level1_b:*children:*child3', $result);
		$this->assertArrayHasKey('item5', $result);
	}

	public function testRecursiveSearchWithNestedResult(): void
	{
		$result = ArrayHandler::selectArrayFromOption(
			$this->nestedTestData,
			'type',
			'child',
			false,
			false,
			true,
			false
		);

		$this->assertCount(3, $result);
		$this->assertArrayHasKey('level1_a', $result);
		$this->assertArrayHasKey('level1_b', $result);
		$this->assertArrayHasKey('item5', $result);

		// Check nested structure is preserved
		$this->assertArrayHasKey('children', $result['level1_a']);
		$this->assertArrayHasKey('child1', $result['level1_a']['children']);
		$this->assertArrayHasKey('child2', $result['level1_a']['children']);
	}

	public function testRecursiveSearchDeepNesting(): void
	{
		$result = ArrayHandler::selectArrayFromOption(
			$this->nestedTestData,
			'type',
			'deep',
			false,
			false,
			true,
			true,
			':*'
		);

		$this->assertCount(1, $result);
		$this->assertArrayHasKey('level1_b:*children:*child3:*nested:*deep1', $result);
		$this->assertEquals('Deep1', $result['level1_b:*children:*child3:*nested:*deep1']['name']);
	}

	public function testCustomFlatSeparator(): void
	{
		$result = ArrayHandler::selectArrayFromOption(
			$this->nestedTestData,
			'type',
			'child',
			false,
			false,
			true,
			true,
			'|'
		);

		$this->assertArrayHasKey('level1_a|children|child1', $result);
		$this->assertArrayHasKey('level1_a|children|child2', $result);
		$this->assertArrayHasKey('level1_b|children|child3', $result);
	}

	public function testNonRecursiveSearch(): void
	{
		$result = ArrayHandler::selectArrayFromOption(
			$this->nestedTestData,
			'type',
			'child',
			false,
			false,
			false
		);

		// Should only find direct matches, not nested ones
		$this->assertCount(1, $result);
		$this->assertArrayHasKey('item5', $result);
	}

	public function testNoMatchesFound(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'name', 'NonExistent');

		$this->assertEmpty($result);
	}

	public function testMissingLookupKey(): void
	{
		$result = ArrayHandler::selectArrayFromOption($this->testData, 'nonexistent_key', 'value');

		$this->assertEmpty($result);
	}

	public function testCombinedStrictAndCaseInsensitive(): void
	{
		$data = [
			'item1' => ['name' => 'Test', 'id' => '123'],
			'item2' => ['name' => 'test', 'id' => 123],
			'item3' => ['name' => 'TEST', 'id' => '123']
		];

		// Case insensitive but strict type matching
		$result = ArrayHandler::selectArrayFromOption($data, 'id', '123', true, true);

		$this->assertCount(2, $result);
		$this->assertArrayHasKey('item1', $result);
		$this->assertArrayHasKey('item3', $result);
	}

	public function testBooleanWithCaseInsensitive(): void
	{
		$data = [
			'item1' => ['active' => true, 'name' => 'Test1'],
			'item2' => ['active' => false, 'name' => 'Test2']
		];

		// Case insensitive flag should not affect boolean comparison
		$result = ArrayHandler::selectArrayFromOption($data, 'active', true, false, true);

		$this->assertCount(1, $result);
		$this->assertArrayHasKey('item1', $result);
	}

	public function testArrayWithNumericKeys(): void
	{
		$data = [
			0 => ['name' => 'First', 'type' => 'test'],
			1 => ['name' => 'Second', 'type' => 'test'],
			2 => ['name' => 'Third', 'type' => 'other']
		];

		$result = ArrayHandler::selectArrayFromOption($data, 'type', 'test');

		$this->assertCount(2, $result);
		$this->assertArrayHasKey(0, $result);
		$this->assertArrayHasKey(1, $result);
	}

	public function testRecursiveWithMixedKeyTypes(): void
	{
		$data = [
			'string_key' => [
				'name' => 'Parent',
				'type' => 'parent',
				0 => [
					'name' => 'Child0',
					'type' => 'child'
				],
				'child_key' => [
					'name' => 'ChildKey',
					'type' => 'child'
				]
			]
		];

		$result = ArrayHandler::selectArrayFromOption($data, 'type', 'child', false, false, true, true, ':*');

		$this->assertCount(2, $result);
		$this->assertArrayHasKey('string_key:*0', $result);
		$this->assertArrayHasKey('string_key:*child_key', $result);
	}

	public function testAllParametersCombined(): void
	{
		$data = [
			'parent1' => [
				'name' => 'Parent1',
				'status' => 'ACTIVE',
				'children' => [
					'child1' => [
						'name' => 'Child1',
						'status' => 'active'
					]
				]
			]
		];

		$result = ArrayHandler::selectArrayFromOption(
			$data,
			'status',
			'active',
			false,      // not strict
			true,       // case insensitive
			true,       // recursive
			true,       // flat result
			'|'         // custom separator
		);

		$this->assertCount(2, $result);
		$this->assertArrayHasKey('parent1', $result);
		$this->assertArrayHasKey('parent1|children|child1', $result);
	}
}

// __END__
