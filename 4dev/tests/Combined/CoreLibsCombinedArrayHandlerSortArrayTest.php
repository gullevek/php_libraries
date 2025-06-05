<?php

// This code was create by Claude Sonnet 4

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Combined\ArrayHandler;

class CoreLibsCombinedArrayHandlerSortArrayTest extends TestCase
{
	/**
	 * Test basic ascending sort without maintaining keys
	 */
	public function testBasicAscendingSort()
	{
		$input = [3, 1, 4, 1, 5, 9];
		$expected = [1, 1, 3, 4, 5, 9];

		$result = ArrayHandler::sortArray($input);

		$this->assertEquals($expected, $result);
		$this->assertEquals(array_keys($expected), array_keys($result));
	}

	/**
	 * Test basic descending sort without maintaining keys
	 */
	public function testBasicDescendingSort()
	{
		$input = [3, 1, 4, 1, 5, 9];
		$expected = [9, 5, 4, 3, 1, 1];

		$result = ArrayHandler::sortArray($input, false, true);

		$this->assertEquals($expected, $result);
		$this->assertEquals(array_keys($expected), array_keys($result));
	}

	/**
	 * Test ascending sort with key maintenance
	 */
	public function testAscendingSortWithKeyMaintenance()
	{
		$input = ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 1, 'e' => 5];
		$expected = ['a' => 1, 'b' => 1, 'c' => 3, 'd' => 4, 'e' => 5];

		$result = ArrayHandler::sortArray($input, false, false, true);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test descending sort with key maintenance
	 */
	public function testDescendingSortWithKeyMaintenance()
	{
		$input = ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 1, 'e' => 5];
		$expected = ['e' => 5, 'd' => 4, 'c' => 3, 'a' => 1, 'b' => 1];

		$result = ArrayHandler::sortArray($input, false, true, true);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test string sorting with lowercase conversion
	 */
	public function testStringLowerCaseSort()
	{
		$input = ['Banana', 'apple', 'Cherry', 'date'];
		$expected = ['apple', 'Banana', 'Cherry', 'date'];

		$result = ArrayHandler::sortArray($input, true);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test string sorting with lowercase conversion in reverse
	 */
	public function testStringLowerCaseSortReverse()
	{
		$input = ['Banana', 'apple', 'Cherry', 'date'];
		$expected = ['date', 'Cherry', 'Banana', 'apple'];

		$result = ArrayHandler::sortArray($input, true, true);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test string sorting with lowercase conversion and key maintenance
	 */
	public function testStringLowerCaseSortWithKeys()
	{
		$input = ['b' => 'Banana', 'a' => 'apple', 'c' => 'Cherry', 'd' => 'date'];
		$expected = ['a' => 'apple', 'b' => 'Banana', 'c' => 'Cherry', 'd' => 'date'];

		$result = ArrayHandler::sortArray($input, true, false, true);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test string sorting with lowercase conversion, reverse, and key maintenance
	 */
	public function testStringLowerCaseSortReverseWithKeys()
	{
		$input = ['b' => 'Banana', 'a' => 'apple', 'c' => 'Cherry', 'd' => 'date'];
		$expected = ['d' => 'date', 'c' => 'Cherry', 'b' => 'Banana', 'a' => 'apple'];

		$result = ArrayHandler::sortArray($input, true, true, true);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test numeric string sorting with SORT_NUMERIC flag
	 */
	public function testNumericStringSorting()
	{
		$input = ['10', '2', '1', '20'];
		$expected = ['1', '2', '10', '20'];

		$result = ArrayHandler::sortArray($input, false, false, false, SORT_NUMERIC);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test natural string sorting with SORT_NATURAL flag
	 */
	public function testNaturalStringSorting()
	{
		$input = ['img1.png', 'img10.png', 'img2.png', 'img20.png'];
		$expected = ['img1.png', 'img2.png', 'img10.png', 'img20.png'];

		$result = ArrayHandler::sortArray($input, false, false, false, SORT_NATURAL);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test with empty array
	 */
	public function testEmptyArray()
	{
		$input = [];
		$expected = [];

		$result = ArrayHandler::sortArray($input);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test with single element array
	 */
	public function testSingleElementArray()
	{
		$input = [42];
		$expected = [42];

		$result = ArrayHandler::sortArray($input);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test with array containing null values
	 */
	public function testArrayWithNullValues()
	{
		$input = [3, null, 1, null, 2];
		$expected = [null, null, 1, 2, 3];

		$result = ArrayHandler::sortArray($input);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test with mixed data types
	 */
	public function testMixedDataTypes()
	{
		$input = [3, '1', 4.5, '2', 1];

		$result = ArrayHandler::sortArray($input);

		// Should sort according to PHP's natural comparison rules
		$this->assertIsArray($result);
		$this->assertCount(5, $result);
	}

	/**
	 * Test that original array is not modified (immutability)
	 */
	public function testOriginalArrayNotModified()
	{
		$original = [3, 1, 4, 1, 5, 9];
		$input = $original;

		$result = ArrayHandler::sortArray($input);

		$this->assertEquals($original, $input);
		$this->assertNotEquals($input, $result);
	}

	/**
	 * Test case sensitivity without lowercase flag
	 */
	public function testCaseSensitivityWithoutLowercase()
	{
		$input = ['Banana', 'apple', 'Cherry'];

		$result = ArrayHandler::sortArray($input);

		// Capital letters should come before lowercase in ASCII sort
		$this->assertEquals('Banana', $result[0]);
		$this->assertEquals('Cherry', $result[1]);
		$this->assertEquals('apple', $result[2]);
	}

	/**
	 * Test all parameters combination
	 */
	public function testAllParametersCombination()
	{
		$input = ['z' => 'Zebra', 'a' => 'apple', 'b' => 'Banana'];

		$result = ArrayHandler::sortArray($input, true, true, true, SORT_REGULAR);

		// Should be sorted by lowercase, reversed, with keys maintained
		$keys = array_keys($result);
		$values = array_values($result);

		$this->assertEquals(['z', 'b', 'a'], $keys);
		$this->assertEquals(['Zebra', 'Banana', 'apple'], $values);
	}

	/**
	 * Test floating point numbers
	 */
	public function testFloatingPointNumbers()
	{
		$input = [3.14, 2.71, 1.41, 1.73];
		$expected = [1.41, 1.73, 2.71, 3.14];

		$result = ArrayHandler::sortArray($input);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test with duplicate values and key maintenance
	 */
	public function testDuplicateValuesWithKeyMaintenance()
	{
		$input = ['first' => 1, 'second' => 2, 'third' => 1, 'fourth' => 2];

		$result = ArrayHandler::sortArray($input, false, false, true);

		$this->assertCount(4, $result);
		$this->assertEquals([1, 1, 2, 2], array_values($result));
		// Keys should be preserved
		$this->assertArrayHasKey('first', $result);
		$this->assertArrayHasKey('second', $result);
		$this->assertArrayHasKey('third', $result);
		$this->assertArrayHasKey('fourth', $result);
	}

	/**
	 * Data provider for comprehensive parameter testing
	 */
	public function sortParameterProvider(): array
	{
		return [
			'basic_ascending' => [
				[3, 1, 4, 2],
				false, false, false, SORT_REGULAR,
				[1, 2, 3, 4]
			],
			'basic_descending' => [
				[3, 1, 4, 2],
				false, true, false, SORT_REGULAR,
				[4, 3, 2, 1]
			],
			'lowercase_ascending' => [
				['Banana', 'apple', 'Cherry'],
				true, false, false, SORT_REGULAR,
				['apple', 'Banana', 'Cherry']
			],
			'lowercase_descending' => [
				['Banana', 'apple', 'Cherry'],
				true, true, false, SORT_REGULAR,
				['Cherry', 'Banana', 'apple']
			]
		];
	}

	/**
	 * Test various parameter combinations using data provider
	 *
	 * @dataProvider sortParameterProvider
	 */
	public function testSortParameterCombinations(
		array $input,
		bool $lowercase,
		bool $reverse,
		bool $maintainKeys,
		int $params,
		array $expected
	) {
		$result = ArrayHandler::sortArray($input, $lowercase, $reverse, $maintainKeys, $params);

		if (!$maintainKeys) {
			$this->assertEquals($expected, $result);
		} else {
			$this->assertEquals($expected, array_values($result));
		}
	}
}

// __END__
