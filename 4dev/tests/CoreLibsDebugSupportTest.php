<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Debug\Support
 * @coversDefaultClass \CoreLibs\Debug\Support
 * @testdox \CoreLibs\Debug\Support method tests
 */
final class CoreLibsDebugSupportTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function printTimeProvider(): array
	{
		return [
			'default microtime' => [
				0 => null,
				1 => "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{8}$/",
			],
			'microtime -1' => [
				0 => -1,
				1 => "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{8}$/",
			],
			'microtime 0' => [
				0 => 0,
				1 => "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/",
			],
			'microtime 4' => [
				0 => 4,
				1 => "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{4}$/",
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function printArProvider(): array
	{
		return [
			'empty array' => [
				0 => [],
				1 => "<pre>Array\n(\n)\n</pre>"
			],
			'simple array' => [
				0 => ['a', 'b'],
				1 => "<pre>Array\n(\n"
					. "    [0] => a\n"
					. "    [1] => b\n"
					. ")\n</pre>"
			],
		];
	}

	public function printToStringProvider(): array
	{
		return [
			'string' => [
				'a string',
				null,
				'a string',
			],
			'a number' => [
				1234,
				null,
				'1234',
			],
			'a float number' => [
				1234.5678,
				null,
				'1234.5678',
			],
			'bool true' => [
				true,
				null,
				'TRUE',
			],
			'bool false' => [
				false,
				null,
				'FALSE',
			],
			'an array default' => [
				['a', 'b'],
				null,
				"<pre>Array\n(\n"
					. "    [0] => a\n"
					. "    [1] => b\n"
					. ")\n</pre>",
			],
			'an array, no html' => [
				['a', 'b'],
				true,
				"##HTMLPRE##"
					. "Array\n(\n"
					. "    [0] => a\n"
					. "    [1] => b\n"
					. ")\n"
					. "##/HTMLPRE##",
			],
			// resource
			'a resource' => [
				tmpfile(),
				null,
				'/^Resource id #\d+$/',
			],
			// object
			'an object' => [
				new \CoreLibs\Debug\Support(),
				null,
				'CoreLibs\Debug\Support',
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function debugStringProvider(): array
	{
		return [
			'null string, default' => [
				0 => null,
				1 => null,
				2 => '-'
			],
			'empty string, ... replace' => [
				0 => '',
				1 => '...',
				2 => '...'
			],
			'filled string' => [
				0 => 'some string',
				1 => null,
				2 => 'some string'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::printTime
	 * @dataProvider printTimeProvider
	 * @testdox printTime test with $microtime and match to regex [$_dataName]
	 *
	 * @param int|null $mircrotime
	 * @param string $expected
	 * @return void
	 */
	public function testPrintTime(?int $microtime, string $regex): void
	{
		if ($microtime === null) {
			$this->assertMatchesRegularExpression(
				$regex,
				\CoreLibs\Debug\Support::printTime()
			);
		} else {
			$this->assertMatchesRegularExpression(
				$regex,
				\CoreLibs\Debug\Support::printTime($microtime)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::printAr
	 * @dataProvider printArProvider
	 * @testdox printAr $input will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param string $expected
	 * @return void
	 */
	public function testPrintAr(array $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Debug\Support::printAr($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::printToString
	 * @dataProvider printToStringProvider
	 * @testdox printToString $input with $flag will be $expected [$_dataName]
	 *
	 * @param mixed $input
	 * @param boolean|null $flag
	 * @param string $expected
	 * @return void
	 */
	public function testPrintToString($input, ?bool $flag, string $expected): void
	{
		if ($flag === null) {
			// if expected starts with / and ends with / then this is a regex compare
			if (substr($expected, 0, 1) == '/' && substr($expected, -1, 1) == '/') {
				$this->assertMatchesRegularExpression(
					$expected,
					\CoreLibs\Debug\Support::printToString($input)
				);
			} else {
				$this->assertEquals(
					$expected,
					\CoreLibs\Debug\Support::printToString($input)
				);
			}
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Debug\Support::printToString($input, $flag)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::getCallerMethod
	 * @testWith ["testGetCallerMethod"]
	 * @testdox getCallerMethod check if it returns $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testGetCallerMethod(string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Debug\Support::getCallerMethod()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::getCallerClass
	 * @testWith ["PHPUnit\\TextUI\\Command"]
	 * @testdox getCallerClass check if it returns $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testGetCallerClass(string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Debug\Support::getCallerClass()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::debugString
	 * @dataProvider debugStringProvider
	 * @testdox debugString $input with replace $replace will be $expected [$_dataName]
	 *
	 * @param string|null $input
	 * @param string|null $replace
	 * @param string $expected
	 * @return void
	 */
	public function testDebugString(?string $input, ?string $replace, string $expected)
	{
		if ($replace === null) {
			$this->assertEquals(
				$expected,
				\CoreLibs\Debug\Support::debugString($input)
			);
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Debug\Support::debugString($input, $replace)
			);
		}
	}
}

// __END__