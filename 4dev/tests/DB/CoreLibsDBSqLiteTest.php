<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for DB\SqLite
 * This will only test the SqLite parts
 * @coversDefaultClass \CoreLibs\DB\SqLite
 * @testdox \CoreLibs\SqLite method tests for extended DB interface
 */
final class CoreLibsDBESqLiteTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		if (!extension_loaded('sqlite')) {
			$this->markTestSkipped(
				'The SqLite extension is not available.'
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @testdox DB\SqLite Class tests
	 *
	 * @return void
	 */
	public function testSqLite()
	{
		$this->markTestIncomplete(
			'DB\SqLite Tests have not yet been implemented'
		);
	}
}

// __END__
