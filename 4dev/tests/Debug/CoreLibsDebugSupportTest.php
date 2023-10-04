<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Debug\Support;

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
				Support::printTime()
			);
		} else {
			$this->assertMatchesRegularExpression(
				$regex,
				Support::printTime($microtime)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function printArrayProvider(): array
	{
		return [
			'empty array' => [
				0 => [],
				1 => "<pre>Array\n(\n)\n</pre>",
				2 => "Array\n(\n)\n",
			],
			'simple array' => [
				0 => ['a', 'b'],
				1 => "<pre>Array\n(\n"
					. "    [0] => a\n"
					. "    [1] => b\n"
					. ")\n</pre>",
				2 => "Array\n(\n"
					. "    [0] => a\n"
					. "    [1] => b\n"
					. ")\n",
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::printAr
	 * @cover ::printArray
	 * @dataProvider printArrayProvider
	 * @testdox printAr/printArray $input will be $expected [$_dataName]
	 *
	 * @param array $input
	 * @param string $expected
	 * @param string $expected_strip
	 * @return void
	 */
	public function testPrintAr(array $input, string $expected, string $expected_strip): void
	{
		$this->assertEquals(
			$expected,
			Support::printAr($input),
			'assert printAr'
		);
		$this->assertEquals(
			$expected,
			Support::printArray($input),
			'assert printArray'
		);
		$this->assertEquals(
			$expected_strip,
			Support::prAr($input),
			'assert prAr'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function printBoolProvider(): array
	{
		return [
			'true input default' => [
				0 => true,
				1 => [],
				2 => 'true',
				3 => 'true',
			],
			'false input default' => [
				0 => false,
				1 => [],
				2 => 'false',
				3 => 'false'
			],
			'false input param name' => [
				0 => false,
				1 => [
					'name' => 'param test'
				],
				2 => '<b>param test</b>: false',
				3 => 'false'
			],
			'true input param name, true override' => [
				0 => true,
				1 => [
					'name' => 'param test',
					'true' => 'ok',
				],
				2 => '<b>param test</b>: ok',
				3 => 'ok',
			],
			'false input param name, true override, false override' => [
				0 => false,
				1 => [
					'name' => 'param test',
					'true' => 'ok',
					'false' => 'not',
				],
				2 => '<b>param test</b>: not',
				3 => 'not'
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::printBool
	 * @dataProvider printBoolProvider
	 * @testdox printBool $input will be $expected [$_dataName]
	 *
	 * @param  bool   $input
	 * @param  array  $params
	 * @param  string $expected
	 * @param  string $expected_strip
	 * @return void
	 */
	public function testPrintBool(bool $input, array $params, string $expected, string $expected_strip): void
	{
		if (
			isset($params['name']) &&
			isset($params['true']) &&
			isset($params['false'])
		) {
			$string = Support::printBool(
				$input,
				$params['name'],
				$params['true'],
				$params['false']
			);
			$string_strip = Support::prBl(
				$input,
				$params['true'],
				$params['false']
			);
		} elseif (isset($params['name']) && isset($params['true'])) {
			$string = Support::printBool(
				$input,
				$params['name'],
				$params['true']
			);
			$string_strip = Support::prBl(
				$input,
				$params['true'],
			);
		} elseif (isset($params['name'])) {
			$string = Support::printBool(
				$input,
				$params['name']
			);
			$string_strip = Support::prBl(
				$input
			);
		} else {
			$string = Support::printBool($input);
			$string_strip = Support::prBl($input);
		}
		$this->assertEquals(
			$expected,
			$string,
			'assert printBool'
		);
		$this->assertEquals(
			$expected_strip,
			$string_strip,
			'assert prBl'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function printToStringProvider(): array
	{
		// 0: unput
		// 1: html flag (only for strings and arry)
		// 2: expected
		return [
			'null' => [
				null,
				null,
				'NULL',
			],
			'string' => [
				'a string',
				null,
				'a string',
			],
			'string with html chars, encode' => [
				'a string with <> &',
				true,
				'a string with &lt;&gt; &amp;',
			],
			'string with html chars' => [
				'a string with <> &',
				null,
				'a string with <> &',
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
				"Array\n(\n"
					. "    [0] => a\n"
					. "    [1] => b\n"
					. ")\n",
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
	 * @cover ::printToString
	 * @dataProvider printToStringProvider
	 * @testdox printToString $input with $flag will be $expected [$_dataName]
	 *
	 * @param mixed $input anything
	 * @param boolean|null $flag html flag, only for string and array
	 * @param string $expected always string
	 * @return void
	 */
	public function testPrintToString(mixed $input, ?bool $flag, string $expected): void
	{
		if ($flag === null) {
			// if expected starts with / and ends with / then this is a regex compare
			if (
				substr($expected, 0, 1) == '/' &&
				substr($expected, -1, 1) == '/'
			) {
				$this->assertMatchesRegularExpression(
					$expected,
					Support::printToString($input)
				);
			} else {
				$this->assertEquals(
					$expected,
					Support::printToString($input)
				);
			}
		} else {
			$this->assertEquals(
				$expected,
				Support::printToString($input, $flag)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerDumpExportVar(): array
	{
		return [
			'string' => [
				'input' => 'string',
				'flag' => null,
				'expected_dump' => 'string(6) "string"' . "\n",
				'expected_export' => "<pre>'string'</pre>",
			],
			'string, no html' => [
				'input' => 'string',
				'flag' => true,
				'expected_dump' => 'string(6) "string"' . "\n",
				'expected_export' => "'string'",
			],
			// int
			'int' => [
				'input' => 6,
				'flag' => null,
				'expected_dump' => 'int(6)' . "\n",
				'expected_export' => "<pre>6</pre>",
			],
			// float
			'float' => [
				'input' => 1.6,
				'flag' => null,
				'expected_dump' => 'float(1.6)' . "\n",
				'expected_export' => "<pre>1.6</pre>",
			],
			// bool
			'bool' => [
				'input' => true,
				'flag' => null,
				'expected_dump' => 'bool(true)' . "\n",
				'expected_export' => "<pre>true</pre>",
			],
			// array
			'array' => [
				'input' => ['string', true],
				'flag' => null,
				'expected_dump' => "array(2) {\n"
					. "  [0]=>\n"
					. "  string(6) \"string\"\n"
					. "  [1]=>\n"
					. "  bool(true)\n"
					. "}\n",
				'expected_export' => "<pre>array (\n"
					. "  0 => 'string',\n"
					. "  1 => true,\n"
					. ")</pre>",
			],
			// more
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::dumpVar
	 * @cover ::exportVar
	 * @dataProvider providerDumpExportVar
	 * @testdox dump/exportVar $input with $flag will be $expected_dump / $expected_export [$_dataName]
	 *
	 * @param  mixed     $input
	 * @param  bool|null $flag
	 * @param  string    $expected_dump
	 * @param  string    $expected_export
	 * @return void
	 */
	public function testDumpExportVar(mixed $input, ?bool $flag, string $expected_dump, string $expected_export): void
	{
		if ($flag === null) {
			$dump = Support::dumpVar($input);
			$export = Support::exportVar($input);
		} else {
			$dump = Support::dumpVar($input, $flag);
			$export = Support::exportVar($input, $flag);
		}
		$this->assertEquals(
			$expected_dump,
			$dump,
			'assert dumpVar'
		);
		$this->assertEquals(
			$expected_export,
			$export,
			'assert dumpVar'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::getCallerFileLine
	 * @testWith ["vendor/phpunit/phpunit/src/Framework/TestCase.php:6434","phar:///home/clemens/.phive/phars/phpunit-9.6.13.phar/phpunit/Framework/TestCase.php:6434"]
	 * @testdox getCallerFileLine check based on regex .../Framework/TestCase.php:\d+ [$_dataName]
	 *
	 * @param  string $expected
	 * @return void
	 */
	public function testGetCallerFileLine(): void
	{
		// regex prefix with path "/../" and then fixed vendor + \d+
		// or phar start if phiev installed
		// phar:///home/clemens/.phive/phars/phpunit-9.6.13.phar/phpunit/Framework/TestCase.php
		$regex = "/^("
			. "\/.*\/vendor\/phpunit\/phpunit\/src"
			. "|"
			. "phar:\/\/\/.*\.phive\/phars\/phpunit-\d+\.\d+\.\d+\.phar\/phpunit"
			. ")"
			. "\/Framework\/TestCase.php:\d+$/";
		$this->assertMatchesRegularExpression(
			$regex,
			Support::getCallerFileLine()
		);
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
			Support::getCallerMethod()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::getCallerMethodList
	 * @testWith [["main", "run", "run", "run", "run", "run", "run", "runBare", "runTest", "testGetCallerMethodList"]]
	 * @testdox getCallerMethodList check if it returns $expected [$_dataName]
	 *
	 * @param array $expected
	 * @return void
	 */
	public function testGetCallerMethodList(array $expected): void
	{
		$compare = Support::getCallerMethodList();
		// 10: legact
		// 11: direct
		// 12: full call
		switch (count($compare)) {
			case 10:
				// add nothing
				$this->assertEquals(
					$expected,
					$compare,
					'assert expected 10'
				);
				break;
			case 11:
				if ($compare[0] == 'include') {
					// add include at first
					array_splice(
						$expected,
						0,
						0,
						['include']
					);
				} else {
					array_splice(
						$expected,
						6,
						0,
						['run']
					);
				}
				$this->assertEquals(
					$expected,
					$compare,
					'assert expected 11'
				);
				break;
			case 12:
				// add two "run" before "runBare"
				array_splice(
					$expected,
					7,
					0,
					['run']
				);
				array_splice(
					$expected,
					0,
					0,
					['include']
				);
				$this->assertEquals(
					$expected,
					$compare,
					'assert expected 12'
				);
				break;
		}
	}

	/**
	 * test the lowest one (one above base)
	 *
	 * @cover ::getCallerClass
	 * @testWith ["tests\\CoreLibsDebugSupportTest"]
	 * @testdox getCallerClass check if it returns $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testGetCallerClass(string $expected): void
	{
		$this->assertEquals(
			$expected,
			Support::getCallerClass()
		);
	}

	/**
	 * test highest return (top level)
	 *
	 * @cover ::getCallerTopLevelClass
	 * @testWith ["PHPUnit\\TextUI\\Command"]
	 * @testdox getCallerTopLevelClass check if it returns $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testGetCallerTopLevelClass(string $expected): void
	{
		$this->assertEquals(
			$expected,
			Support::getCallerTopLevelClass()
		);
	}

	/**
	 * test highest return (top level)
	 *
	 * @cover ::getCallerClassMethod
	 * @testWith ["tests\\CoreLibsDebugSupportTest->testGetCallerClassMethod"]
	 * @testdox getCallerClassMethod check if it returns $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testGetCallerClassMethod(string $expected): void
	{
		$this->assertEquals(
			$expected,
			Support::getCallerClassMethod()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function debugStringProvider(): array
	{
		// 0: input string
		// 1: replace
		// 2: html flag
		// 3: expected
		return [
			'null string, default' => [
				null,
				null,
				null,
				'-'
			],
			'empty string, ... replace' => [
				'',
				'...',
				null,
				'...'
			],
			'filled string' => [
				'some string',
				null,
				null,
				'some string'
			],
			'string with html chars, encode' => [
				'a string with <> &',
				'-',
				true,
				'a string with &lt;&gt; &amp;',
			],
			'string with html chars' => [
				'a string with <> &',
				'-',
				null,
				'a string with <> &',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::debugString
	 * @dataProvider debugStringProvider
	 * @testdox debugString $input with replace $replace and html $flag will be $expected [$_dataName]
	 *
	 * @param string|null $input
	 * @param string|null $replace
	 * @param bool|null   $flag
	 * @param string      $expected
	 * @return void
	 */
	public function testDebugString(?string $input, ?string $replace, ?bool $flag, string $expected): void
	{
		if ($replace === null && $flag === null) {
			$this->assertEquals(
				$expected,
				Support::debugString($input),
				'assert all default'
			);
		} elseif ($flag === null) {
			$this->assertEquals(
				$expected,
				Support::debugString($input, $replace),
				'assert flag default'
			);
		} else {
			$this->assertEquals(
				$expected,
				Support::debugString($input, $replace, $flag),
				'assert all set'
			);
		}
	}
}

// __END__
