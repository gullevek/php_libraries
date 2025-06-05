<?php

// This code was create by Claude Sonnet 4
// modification for value checks with assertEqualsCanonicalizing

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Combined\ArrayHandler;

class CoreLibsCombinedArrayHandlerKsortArrayTest extends TestCase
{
	/**
	 * Test basic ascending sort (default behavior)
	 */
	public function testKsortArrayBasicAscending(): void
	{
		$input = [
			'zebra' => 'value1',
			'apple' => 'value2',
			'banana' => 'value3',
			'cherry' => 'value4'
		];

		$expected = [
			'apple' => 'value2',
			'banana' => 'value3',
			'cherry' => 'value4',
			'zebra' => 'value1'
		];

		$result = ArrayHandler::ksortArray($input);
		$this->assertEquals($expected, $result);
		$this->assertEquals(array_keys($expected), array_keys($result));
	}

	/**
	 * Test descending sort with reverse=true
	 */
	public function testKsortArrayDescending(): void
	{
		$input = [
			'zebra' => 'value1',
			'apple' => 'value2',
			'banana' => 'value3',
			'cherry' => 'value4'
		];

		$expected = [
			'zebra' => 'value1',
			'cherry' => 'value4',
			'banana' => 'value3',
			'apple' => 'value2'
		];

		$result = ArrayHandler::ksortArray($input, false, true);
		$this->assertEquals($expected, $result);
		$this->assertEquals(array_keys($expected), array_keys($result));
	}

	/**
	 * Test case-insensitive ascending sort
	 */
	public function testKsortArrayCaseInsensitiveAscending(): void
	{
		$input = [
			'Zebra' => 'value1',
			'apple' => 'value2',
			'Banana' => 'value3',
			'cherry' => 'value4'
		];

		$expected = [
			'apple' => 'value2',
			'Banana' => 'value3',
			'cherry' => 'value4',
			'Zebra' => 'value1'
		];

		$result = ArrayHandler::ksortArray($input, true);
		$this->assertEquals($expected, $result);
		$this->assertEquals(array_keys($expected), array_keys($result));
	}

	/**
	 * Test case-insensitive descending sort
	 */
	public function testKsortArrayCaseInsensitiveDescending(): void
	{
		$input = [
			'Zebra' => 'value1',
			'apple' => 'value2',
			'Banana' => 'value3',
			'cherry' => 'value4'
		];

		$expected = [
			'Zebra' => 'value1',
			'cherry' => 'value4',
			'Banana' => 'value3',
			'apple' => 'value2'
		];

		$result = ArrayHandler::ksortArray($input, true, true);
		$this->assertEquals($expected, $result);
		$this->assertEquals(array_keys($expected), array_keys($result));
	}

	/**
	 * Test with mixed case keys to verify case sensitivity behavior
	 */
	public function testKsortArrayCaseSensitivityComparison(): void
	{
		$input = [
			'B' => 'value1',
			'a' => 'value2',
			'C' => 'value3',
			'b' => 'value4'
		];

		// Case-sensitive sort (uppercase comes before lowercase in ASCII)
		$expectedCaseSensitive = [
			'B' => 'value1',
			'C' => 'value3',
			'a' => 'value2',
			'b' => 'value4'
		];

		// Case-insensitive sort
		$expectedCaseInsensitive = [
			'a' => 'value2',
			'B' => 'value1',
			'b' => 'value4',
			'C' => 'value3'
		];

		$resultCaseSensitive = ArrayHandler::ksortArray($input, false);
		$resultCaseInsensitive = ArrayHandler::ksortArray($input, true);

		$this->assertEquals($expectedCaseSensitive, $resultCaseSensitive);
		$this->assertEquals($expectedCaseInsensitive, $resultCaseInsensitive);
	}

	/**
	 * Test with numeric string keys
	 */
	public function testKsortArrayNumericStringKeys(): void
	{
		$input = [
			'10' => 'value1',
			'2' => 'value2',
			'1' => 'value3',
			'20' => 'value4'
		];

		// String comparison, not numeric
		$expected = [
			'1' => 'value3',
			'10' => 'value1',
			'2' => 'value2',
			'20' => 'value4'
		];

		$result = ArrayHandler::ksortArray($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Test with special characters in keys
	 */
	public function testKsortArraySpecialCharacters(): void
	{
		$input = [
			'key_with_underscore' => 'value1',
			'key-with-dash' => 'value2',
			'key.with.dot' => 'value3',
			'key with space' => 'value4',
			'keyWithCamelCase' => 'value5'
		];

		$result = ArrayHandler::ksortArray($input);

		// Verify it doesn't throw an error and maintains all keys
		$this->assertCount(5, $result);
		$this->assertArrayHasKey('key_with_underscore', $result);
		$this->assertArrayHasKey('key-with-dash', $result);
		$this->assertArrayHasKey('key.with.dot', $result);
		$this->assertArrayHasKey('key with space', $result);
		$this->assertArrayHasKey('keyWithCamelCase', $result);
	}

	/**
	 * Test with empty array
	 */
	public function testKsortArrayEmpty(): void
	{
		$input = [];
		$result = ArrayHandler::ksortArray($input);
		$this->assertEquals([], $result);
		$this->assertIsArray($result);
	}

	/**
	 * Test with single element array
	 */
	public function testKsortArraySingleElement(): void
	{
		$input = ['onlykey' => 'onlyvalue'];
		$result = ArrayHandler::ksortArray($input);
		$this->assertEquals($input, $result);
	}

	/**
	 * Test that original array is not modified (function returns new array)
	 */
	public function testKsortArrayDoesNotModifyOriginal(): void
	{
		$original = [
			'zebra' => 'value1',
			'apple' => 'value2',
			'banana' => 'value3'
		];

		$originalCopy = $original; // Keep a copy for comparison
		$result = ArrayHandler::ksortArray($original);

		// Original array should remain unchanged
		$this->assertEquals($originalCopy, $original);
		$this->assertNotEquals(array_keys($original), array_keys($result));
	}

	/**
	 * Test with complex mixed data types as values
	 */
	public function testKsortArrayMixedValueTypes(): void
	{
		$input = [
			'string_key' => 'string_value',
			'array_key' => ['nested', 'array'],
			'int_key' => 42,
			'bool_key' => true,
			'null_key' => null
		];

		$result = ArrayHandler::ksortArray($input);

		// Check that all keys are preserved and sorted
		$expectedKeys = ['array_key', 'bool_key', 'int_key', 'null_key', 'string_key'];
		$this->assertEquals($expectedKeys, array_keys($result));

		// Check that values are preserved correctly
		$this->assertEquals('string_value', $result['string_key']);
		$this->assertEquals(['nested', 'array'], $result['array_key']);
		$this->assertEquals(42, $result['int_key']);
		$this->assertTrue($result['bool_key']);
		$this->assertNull($result['null_key']);
	}

	/**
	 * Test all parameter combinations
	 */
	public function testKsortArrayAllParameterCombinations(): void
	{
		$input = [
			'Delta' => 'value1',
			'alpha' => 'value2',
			'Charlie' => 'value3',
			'bravo' => 'value4'
		];

		// Test all 4 combinations
		$result1 = ArrayHandler::ksortArray($input, false, false); // default
		$result2 = ArrayHandler::ksortArray($input, false, true);  // reverse only
		$result3 = ArrayHandler::ksortArray($input, true, false);  // lowercase only
		$result4 = ArrayHandler::ksortArray($input, true, true);   // both

		// Each should produce different ordering
		$this->assertNotEquals(array_keys($result1), array_keys($result2));
		$this->assertNotEquals(array_keys($result1), array_keys($result3));
		$this->assertNotEquals(array_keys($result1), array_keys($result4));
		$this->assertNotEquals(array_keys($result2), array_keys($result3));
		$this->assertNotEquals(array_keys($result2), array_keys($result4));
		$this->assertNotEquals(array_keys($result3), array_keys($result4));

		// But all should have same keys and values, just different order
		$this->assertEqualsCanonicalizing(array_values($input), array_values($result1));
		$this->assertEqualsCanonicalizing(array_values($input), array_values($result2));
		$this->assertEqualsCanonicalizing(array_values($input), array_values($result3));
		$this->assertEqualsCanonicalizing(array_values($input), array_values($result4));
	}

	/**
	 * Data provider for comprehensive testing
	 */
	public function sortingParametersProvider(): array
	{
		return [
			'default' => [false, false],
			'reverse' => [false, true],
			'lowercase' => [true, false],
			'lowercase_reverse' => [true, true],
		];
	}

	/**
	 * Test that function works with all parameter combinations using data provider
	 *
	 * @dataProvider sortingParametersProvider
	 */
	public function testKsortArrayWithDataProvider(bool $lowerCase, bool $reverse): void
	{
		$input = [
			'Zebra' => 'animal1',
			'apple' => 'fruit1',
			'Banana' => 'fruit2',
			'cat' => 'animal2'
		];

		$result = ArrayHandler::ksortArray($input, $lowerCase, $reverse);

		// Basic assertions that apply to all combinations
		$this->assertIsArray($result);
		$this->assertCount(4, $result);
		$this->assertArrayHasKey('Zebra', $result);
		$this->assertArrayHasKey('apple', $result);
		$this->assertArrayHasKey('Banana', $result);
		$this->assertArrayHasKey('cat', $result);
	}
}

// __END__
