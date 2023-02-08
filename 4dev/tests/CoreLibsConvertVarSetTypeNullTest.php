<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Convert\VarSetTypeNull;

/**
 * Test class for Convert\Strings
 * @coversDefaultClass \CoreLibs\Convert\VarSetTypeNull
 * @testdox \CoreLibs\Convert\VarSetTypeNull method tests
 */
final class CoreLibsConvertVarSetTypeNullTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varSetTypeStringProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'empty string' => [
				'',
				null,
				''
			],
			'filled string' => [
				'string',
				null,
				'string'
			],
			'valid string, override set' => [
				'string',
				'override',
				'string'
			],
			'int, no override' => [
				1,
				null,
				null
			],
			'int, override set' => [
				1,
				'not int',
				'not int'
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::setStr
	 * @dataProvider varSetTypeStringProvider
	 * @testdox setStr $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed       $input
	 * @param  string|null $default
	 * @param  string|null $expected
	 * @return void
	 */
	public function testSetString(mixed $input, ?string $default, ?string $expected): void
	{
		$set_var = VarSetTypeNull::setStr($input, $default);
		if ($expected !== null) {
			$this->assertIsString($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varMakeTypeStringProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'empty string' => [
				'',
				null,
				''
			],
			'filled string' => [
				'string',
				null,
				'string'
			],
			'valid string, override set' => [
				'string',
				'override',
				'string'
			],
			'int, no override' => [
				1,
				null,
				'1'
			],
			'int, override set' => [
				1,
				'not int',
				'1'
			],
			'float, no override' => [
				1.5,
				null,
				'1.5'
			],
			// all the strange things here
			'function, override set' => [
				$foo = function () {
					return '';
				},
				'function',
				'function'
			],
			'function, no override' => [
				$foo = function () {
					return '';
				},
				null,
				null
			],
			'hex value, override set' => [
				0x55,
				'hex',
				'85'
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::makeStr
	 * @dataProvider varMakeTypeStringProvider
	 * @testdox makeStr $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed       $input
	 * @param  string|null $default
	 * @param  string|null $expected
	 * @return void
	 */
	public function testMakeString(mixed $input, ?string $default, ?string $expected): void
	{
		$set_var = VarSetTypeNull::makeStr($input, $default);
		if ($expected !== null) {
			$this->assertIsString($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varSetTypeIntProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'int' => [
				1,
				null,
				1
			],
			'int, override set' => [
				1,
				-1,
				1
			],
			'string, no override' => [
				'string',
				null,
				null
			],
			'string, override' => [
				'string',
				-1,
				-1
			],
			'float' => [
				1.5,
				null,
				null
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::setInt
	 * @dataProvider varSetTypeIntProvider
	 * @testdox setInt $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed    $input
	 * @param  int|null $default
	 * @param  int|null $expected
	 * @return void
	 */
	public function testSetInt(mixed $input, ?int $default, ?int $expected): void
	{
		$set_var = VarSetTypeNull::setInt($input, $default);
		if ($expected !== null) {
			$this->assertIsInt($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varMakeTypeIntProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'int' => [
				1,
				null,
				1
			],
			'int, override set' => [
				1,
				-1,
				1
			],
			'string, no override' => [
				'string',
				null,
				0
			],
			'string, override' => [
				'string',
				-1,
				0
			],
			'float' => [
				1.5,
				null,
				1
			],
			// all the strange things here
			'function, override set' => [
				$foo = function () {
					return '';
				},
				-1,
				-1
			],
			'function, no override ' => [
				$foo = function () {
					return '';
				},
				null,
				null
			],
			'hex value, override set' => [
				0x55,
				-1,
				85
			],
		];
	}

	/**
	 * Undocumented function
	 * @covers ::makeInt
	 * @dataProvider varMakeTypeIntProvider
	 * @testdox makeInt $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed    $input
	 * @param  int|null $default
	 * @param  int|null $expected
	 * @return void
	 */
	public function testMakeInt(mixed $input, ?int $default, ?int $expected): void
	{
		$set_var = VarSetTypeNull::makeInt($input, $default);
		if ($expected !== null) {
			$this->assertIsInt($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varSetTypeFloatProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'float' => [
				1.5,
				null,
				1.5
			],
			'float, override set' => [
				1.5,
				-1.5,
				1.5
			],
			'string, no override' => [
				'string',
				null,
				null
			],
			'string, override' => [
				'string',
				1.5,
				1.5
			],
			'int' => [
				1,
				null,
				null
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::setFloat
	 * @dataProvider varSetTypeFloatProvider
	 * @testdox setFloat $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed    $input
	 * @param  float|null $default
	 * @param  float|null $expected
	 * @return void
	 */
	public function testSetFloat(mixed $input, ?float $default, ?float $expected): void
	{
		$set_var = VarSetTypeNull::setFloat($input, $default);
		if ($expected !== null) {
			$this->assertIsFloat($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varMakeTypeFloatProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'float' => [
				1.5,
				null,
				1.5
			],
			'float, override set' => [
				1.5,
				-1.5,
				1.5
			],
			'string, no override' => [
				'string',
				null,
				0.0
			],
			'string, override' => [
				'string',
				1.5,
				0.0
			],
			'int' => [
				1,
				null,
				1.0
			],
			// all the strange things here
			'function, override set' => [
				$foo = function () {
					return '';
				},
				-1.0,
				-1.0
			],
			// all the strange things here
			'function, no override' => [
				$foo = function () {
					return '';
				},
				null,
				null
			],
			'hex value, override set' => [
				0x55,
				-1,
				85.0
			],
		];
	}

	/**
	 * Undocumented function
	 * @covers ::makeFloat
	 * @dataProvider varMakeTypeFloatProvider
	 * @testdox makeFloat $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed      $input
	 * @param  float|null $default
	 * @param  float|null $expected
	 * @return void
	 */
	public function testMakeFloat(mixed $input, ?float $default, ?float $expected): void
	{
		$set_var = VarSetTypeNull::makeFloat($input, $default);
		if ($expected !== null) {
			$this->assertIsFloat($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varSetTypeArrayProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'array, empty' => [
				[],
				null,
				[]
			],
			'array, filled' => [
				['array'],
				null,
				['array']
			],
			'string, no override' => [
				'string',
				null,
				null
			],
			'string, override' => [
				'string',
				['string'],
				['string']
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::setArray
	 * @dataProvider varSetTypeArrayProvider
	 * @testdox setArray $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed      $input
	 * @param  array|null $default
	 * @param  array|null $expected
	 * @return void
	 */
	public function testSetArray(mixed $input, ?array $default, ?array $expected): void
	{
		$set_var = VarSetTypeNull::setArray($input, $default);
		if ($expected !== null) {
			$this->assertIsArray($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varSetTypeBoolProvider(): array
	{
		// 0: input
		// 1: default (null default)
		// 2: expected
		return [
			'bool true' => [
				true,
				null,
				true
			],
			'bool false' => [
				false,
				null,
				false
			],
			'string, no override' => [
				'string',
				null,
				null
			],
			'string, override' => [
				'string',
				true,
				true
			]
		];
	}

	/**
	 * Undocumented function
	 * @covers ::setBool
	 * @dataProvider varSetTypeBoolProvider
	 * @testdox setBool $input with override $default will be $expected [$_dataName]
	 *
	 * @param  mixed      $input
	 * @param  bool|null $default
	 * @param  bool|null $expected
	 * @return void
	 */
	public function testSetBool(mixed $input, ?bool $default, ?bool $expected): void
	{
		$set_var = VarSetTypeNull::setBool($input, $default);
		if ($expected !== null) {
			$this->assertIsBool($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function varMakeTypeBoolProvider(): array
	{
		// 0: input
		// 2: expected
		return [
			'true' => [
				true,
				true
			],
			'false' => [
				false,
				false
			],
			'string on' => [
				'on',
				true
			],
			'string off' => [
				'off',
				false
			],
			'invalid string' => [
				'sulzenbacher',
				null,
			],
			'invalid string, override' => [
				'sulzenbacher',
				null,
			],
			'array to default' => [
				[],
				false
			],
		];
	}

	/**
	 * Undocumented function
	 * @covers ::setBool
	 * @dataProvider varMakeTypeBoolProvider
	 * @testdox setBool $input will be $expected [$_dataName]
	 *
	 * @param  mixed     $input
	 * @param  bool|null $default
	 * @param  bool|null $expected
	 * @return void
	 */
	public function testMakeBool(mixed $input, ?bool $expected): void
	{
		$set_var = VarSetTypeNull::makeBool($input);
		if ($expected !== null) {
			$this->assertIsBool($set_var);
		} else {
			$this->assertNull($set_var);
		}
		$this->assertEquals(
			$expected,
			$set_var
		);
	}
}

// __END__
