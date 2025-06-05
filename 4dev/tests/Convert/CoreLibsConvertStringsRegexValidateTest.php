<?php

// This code was created by Claude Sonnet 4
// FIX:
// '/test{/',          // Unmatched brace -> this is valid
// '/test{1,}/',       // Invalid quantifier -> this is valid

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Convert\Strings;

/**
 * Test class for CoreLibs\Convert\Strings regex validation methods
 */
class CoreLibsConvertStringsRegexValidateTest extends TestCase
{
	/**
	 * Test isValidRegex with valid regex patterns
	 */
	public function testIsValidRegexWithValidPatterns(): void
	{
		$validPatterns = [
			'/^[a-zA-Z0-9]+$/',
			'/test/',
			'/\d+/',
			'/^hello.*world$/',
			'/[0-9]{3}-[0-9]{3}-[0-9]{4}/',
			'#^https?://.*#i',
			'~^[a-z]+~',
			'|test|',
			'/^$/m',
			'/\w+/u',
		];

		foreach ($validPatterns as $pattern) {
			$this->assertTrue(
				Strings::isValidRegex($pattern),
				"Pattern '{$pattern}' should be valid"
			);
		}
	}

	/**
	 * Test isValidRegex with invalid regex patterns
	 */
	public function testIsValidRegexWithInvalidPatterns(): void
	{
		$invalidPatterns = [
			'/[/',              // Unmatched bracket
			'/test[/',          // Unmatched bracket
			'/(?P<name>/',      // Unmatched parenthesis
			'/(?P<>test)/',     // Invalid named group
			'/test\\/',         // Invalid escape at end
			'/(test/',          // Unmatched parenthesis
			'/test)/',          // Unmatched parenthesis
			// '/test{/',          // Unmatched brace -> this is valid
			// '/test{1,}/',       // Invalid quantifier -> this is valid
			'/[z-a]/',          // Invalid character range
			'invalid',          // No delimiters
			'',                 // Empty string
			'/(?P<123>test)/',  // Invalid named group name
		];

		foreach ($invalidPatterns as $pattern) {
			$this->assertFalse(
				Strings::isValidRegex($pattern),
				"Pattern '{$pattern}' should be invalid"
			);
		}
	}

	/**
	 * Test getLastRegexErrorString returns correct error messages
	 */
	public function testGetLastRegexErrorStringReturnsCorrectMessages(): void
	{
		// Test with a valid regex first to ensure clean state
		Strings::isValidRegex('/valid/');
		$this->assertEquals('No error', Strings::getLastRegexErrorString());

		// Test with invalid regex to trigger an error
		Strings::isValidRegex('/[/');
		$errorMessage = Strings::getLastRegexErrorString();

		// The error message should be one of the defined messages
		$this->assertContains($errorMessage, array_values(Strings::PREG_ERROR_MESSAGES));
		$this->assertNotEquals('Unknown error', $errorMessage);
	}

	/**
	 * Test getLastRegexErrorString with unknown error
	 */
	public function testGetLastRegexErrorStringWithUnknownError(): void
	{
		// This is harder to test directly since we can't easily mock preg_last_error()
		// but we can test the fallback behavior by reflection or assume it works

		// At minimum, ensure it returns a string
		$result = Strings::getLastRegexErrorString();
		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	/**
	 * Test validateRegex with valid patterns
	 */
	public function testValidateRegexWithValidPatterns(): void
	{
		$validPatterns = [
			'/^test$/',
			'/\d+/',
			'/[a-z]+/i',
		];

		foreach ($validPatterns as $pattern) {
			$result = Strings::validateRegex($pattern);

			$this->assertIsArray($result);
			$this->assertArrayHasKey('valid', $result);
			$this->assertArrayHasKey('preg_error', $result);
			$this->assertArrayHasKey('error', $result);

			$this->assertTrue($result['valid'], "Pattern '{$pattern}' should be valid");
			$this->assertEquals(PREG_NO_ERROR, $result['preg_error']);
			$this->assertNull($result['error']);
		}
	}

	/**
	 * Test validateRegex with invalid patterns
	 */
	public function testValidateRegexWithInvalidPatterns(): void
	{
		$invalidPatterns = [
			'/[/',              // Unmatched bracket
			'/(?P<name>/',      // Unmatched parenthesis
			'/test\\/',         // Invalid escape at end
			'/(test/',          // Unmatched parenthesis
		];

		foreach ($invalidPatterns as $pattern) {
			$result = Strings::validateRegex($pattern);

			$this->assertIsArray($result);
			$this->assertArrayHasKey('valid', $result);
			$this->assertArrayHasKey('preg_error', $result);
			$this->assertArrayHasKey('error', $result);
			$this->assertArrayHasKey('pcre_error', $result);

			$this->assertFalse($result['valid'], "Pattern '{$pattern}' should be invalid");
			$this->assertNotEquals(PREG_NO_ERROR, $result['preg_error']);
			$this->assertIsString($result['error']);
			$this->assertNotNull($result['error']);
			$this->assertNotEmpty($result['error']);

			// Verify error message is from our defined messages or 'Unknown error'
			$this->assertTrue(
				in_array($result['error'], array_values(Strings::PREG_ERROR_MESSAGES)) ||
				$result['error'] === 'Unknown error'
			);
		}
	}

	/**
	 * Test validateRegex array structure
	 */
	public function testValidateRegexArrayStructure(): void
	{
		$result = Strings::validateRegex('/test/');

		// Test array structure for valid regex
		$this->assertIsArray($result);
		$this->assertCount(4, $result);
		$this->assertArrayHasKey('valid', $result);
		$this->assertArrayHasKey('preg_error', $result);
		$this->assertArrayHasKey('error', $result);

		$result = Strings::validateRegex('/[/');

		// Test array structure for invalid regex
		$this->assertIsArray($result);
		$this->assertCount(4, $result);
		$this->assertArrayHasKey('valid', $result);
		$this->assertArrayHasKey('preg_error', $result);
		$this->assertArrayHasKey('error', $result);
		$this->assertArrayHasKey('pcre_error', $result);
	}

	/**
	 * Test that methods handle edge cases properly
	 */
	public function testEdgeCases(): void
	{
		// Empty string
		$this->assertFalse(Strings::isValidRegex(''));

		$result = Strings::validateRegex('');
		$this->assertFalse($result['valid']);

		// Very long pattern
		$longPattern = '/' . str_repeat('a', 1000) . '/';
		$this->assertTrue(Strings::isValidRegex($longPattern));

		// Unicode patterns
		$this->assertTrue(Strings::isValidRegex('/\p{L}+/u'));
		$this->assertTrue(Strings::isValidRegex('/[α-ω]+/u'));
	}

	/**
	 * Test PREG_ERROR_MESSAGES constant accessibility
	 */
	public function testPregErrorMessagesConstant(): void
	{
		$this->assertIsArray(Strings::PREG_ERROR_MESSAGES);
		$this->assertNotEmpty(Strings::PREG_ERROR_MESSAGES);

		// Check that all expected PREG constants are defined
		$expectedKeys = [
			PREG_NO_ERROR,
			PREG_INTERNAL_ERROR,
			PREG_BACKTRACK_LIMIT_ERROR,
			PREG_RECURSION_LIMIT_ERROR,
			PREG_BAD_UTF8_ERROR,
			PREG_BAD_UTF8_OFFSET_ERROR,
			PREG_JIT_STACKLIMIT_ERROR,
		];

		foreach ($expectedKeys as $key) {
			$this->assertArrayHasKey($key, Strings::PREG_ERROR_MESSAGES);
			$this->assertIsString(Strings::PREG_ERROR_MESSAGES[$key]);
			$this->assertNotEmpty(Strings::PREG_ERROR_MESSAGES[$key]);
		}
	}

	/**
	 * Test error state isolation between method calls
	 */
	public function testErrorStateIsolation(): void
	{
		// Start with invalid regex
		Strings::isValidRegex('/[/');
		$firstError = Strings::getLastRegexErrorString();
		$this->assertNotEquals('No error', $firstError);

		// Use valid regex
		Strings::isValidRegex('/valid/');
		$secondError = Strings::getLastRegexErrorString();
		$this->assertEquals('No error', $secondError);

		// Verify validateRegex clears previous errors
		$result = Strings::validateRegex('/valid/');
		$this->assertTrue($result['valid']);
		$this->assertEquals(PREG_NO_ERROR, $result['preg_error']);
	}

	/**
	 * Test various regex delimiters
	 */
	public function testDifferentDelimiters(): void
	{
		$patterns = [
			'/test/',      // forward slash
			'#test#',      // hash
			'~test~',      // tilde
			'|test|',      // pipe
			'@test@',      // at symbol
			'!test!',      // exclamation
			'%test%',      // percent
		];

		foreach ($patterns as $pattern) {
			$this->assertTrue(
				Strings::isValidRegex($pattern),
				"Pattern with delimiter '{$pattern}' should be valid"
			);
		}
	}
}

// __END__
