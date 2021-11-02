<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Undocumented class
 * @testdox CoreLibs\Convert\Math method tests
 */
final class CoreLibsCheckFileTest extends TestCase
{
	protected function setUp(): void
	{
		// write a dummy file for testing
	}

	protected function tearDown(): void
	{
		// unlink file
	}

	public function testGetFilenameEnding(string $input, string $expected): void
	{
		// getFilenameEnding
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\File::getFilenameEnding($input)
		);
	}

	public function testGetLinesFromFile(): void
	{
		// getLinesFromFile
	}
}

// __END__
