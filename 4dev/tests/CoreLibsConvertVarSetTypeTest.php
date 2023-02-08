<?php // phpcs:disable Generic.Files.LineLength

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Convert\VarSetType;

/**
 * Test class for Convert\Strings
 * @coversDefaultClass \CoreLibs\Convert\VarSetType
 * @testdox \CoreLibs\Convert\VarSetType method tests
 */
final class CoreLibsConvertVarSetTypeTest extends TestCase
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
				''
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
	 * @param  string      $expected
	 * @return void
	 */
	public function testSetString(mixed $input, ?string $default, string $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::setStr($input);
		} else {
			$set_var = VarSetType::setStr($input, $default);
		}
		$this->assertIsString($set_var);
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
			// all the strange things here
			'function, override set' => [
				$foo = function () {
					return '';
				},
				'function',
				'function'
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
	 * @param  string      $expected
	 * @return void
	 */
	public function testMakeString(mixed $input, ?string $default, string $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::makeStr($input);
		} else {
			$set_var = VarSetType::makeStr($input, $default);
		}
		$this->assertIsString($set_var);
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
				0
			],
			'string, override' => [
				'string',
				-1,
				-1
			],
			'float' => [
				1.5,
				null,
				0
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
	 * @param  int      $expected
	 * @return void
	 */
	public function testSetInt(mixed $input, ?int $default, int $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::setInt($input);
		} else {
			$set_var = VarSetType::setInt($input, $default);
		}
		$this->assertIsInt($set_var);
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
	 * @param  int      $expected
	 * @return void
	 */
	public function testMakeInt(mixed $input, ?int $default, int $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::makeInt($input);
		} else {
			$set_var = VarSetType::makeInt($input, $default);
		}
		$this->assertIsInt($set_var);
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
				0.0
			],
			'string, override' => [
				'string',
				1.5,
				1.5
			],
			'int' => [
				1,
				null,
				0.0
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
	 * @param  float      $expected
	 * @return void
	 */
	public function testSetFloat(mixed $input, ?float $default, float $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::setFloat($input);
		} else {
			$set_var = VarSetType::setFloat($input, $default);
		}
		$this->assertIsFloat($set_var);
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
	 * @param  mixed    $input
	 * @param  float|null $default
	 * @param  float      $expected
	 * @return void
	 */
	public function testMakeFloat(mixed $input, ?float $default, float $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::makeFloat($input);
		} else {
			$set_var = VarSetType::makeFloat($input, $default);
		}
		$this->assertIsFloat($set_var);
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
				[]
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
	 * @param  array      $expected
	 * @return void
	 */
	public function testSetArray(mixed $input, ?array $default, array $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::setArray($input);
		} else {
			$set_var = VarSetType::setArray($input, $default);
		}
		$this->assertIsArray($set_var);
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
				false
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
	 * @param  bool      $expected
	 * @return void
	 */
	public function testSetBool(mixed $input, ?bool $default, bool $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::setBool($input);
		} else {
			$set_var = VarSetType::setBool($input, $default);
		}
		$this->assertIsBool($set_var);
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
				null,
				true
			],
			'false' => [
				false,
				null,
				false
			],
			'string on' => [
				'on',
				null,
				true
			],
			'string off' => [
				'off',
				null,
				false
			],
			'invalid string' => [
				'sulzenbacher',
				null,
				false,
			],
			'invalid string, override' => [
				'sulzenbacher',
				true,
				true,
			],
			'array to default' => [
				[],
				null,
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
	 * @param  bool      $expected
	 * @return void
	 */
	public function testMakeBool(mixed $input, ?bool $default, bool $expected): void
	{
		if ($default === null) {
			$set_var = VarSetType::makeBool($input);
		} else {
			$set_var = VarSetType::makeBool($input, $default);
		}
		$this->assertIsBool($set_var);
		$this->assertEquals(
			$expected,
			$set_var
		);
	}
}

// __END__
