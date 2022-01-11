<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Check\PHPVersion
 * @testdox CoreLibs\Check\PHPVersion method tests
 */
final class CoreLibsCheckPHPVersionTest extends TestCase
{
	/**
	 * NOTE: The checks must be adapted to the PHP version or they will fail
	 *
	 * @return array
	 */
	public function phpVersionProvider(): array
	{
		return [
			// min
			'min 7' => ['7', '', true],
			'min 7.4' => ['7.4', '', true],
			'min 7.4.1' => ['7.4.1', '', true],
			// NOTE: update if php version bigger than 10
			'min 10' => ['10', '', false],
			'min 10.0' => ['10.0', '', false],
			'min 10.0.0' => ['10.0.0', '', false],
			// max version, NOTE: update if php version bigger than 10
			'max 10' => ['7', '10', true],
			'max 10.0' => ['7', '10.0', true],
			'max 10.0.0' => ['7', '10.0.0', true],
			// max version
			'max 7' => ['5', '7', false],
			'max 7.4' => ['5', '7.4', false],
			'max 7.4.1' => ['5', '7.4.1', false],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider phpVersionProvider
	 * @testdox checkPHPVersion $input_min and $input_max will be $expected [$_dataName]
	 *
	 * @param string $input_min
	 * @param string $input_max
	 * @param string $expected
	 * @return void
	 */
	public function testCheckPHPVersion(string $input_min, string $input_max, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\PhpVersion::checkPHPVersion($input_min, $input_max)
		);
	}
}

// __END__
