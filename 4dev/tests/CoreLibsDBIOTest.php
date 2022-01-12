<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for DB\IO + DB\SQL\PgSQL
 * This will only test the PgSQL parts
 * @coversDefaultClass \CoreLibs\DB\IO
 * @coversDefaultClass \CoreLibs\DB\SQL\PgSQL
 * @testdox \CoreLibs\DB\IO method tests for SQL\PgSQL
 */
final class CoreLibsDBIOTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		if (!extension_loaded('pgsql')) {
			$this->markTestSkipped(
				'The PgSQL extension is not available.'
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @testdox DB\IO Class tests
	 *
	 * @return void
	 */
	public function testDBIO()
	{
		$this->assertTrue(true, 'DB IO Tests not implemented');
		$this->markTestIncomplete(
			'DB\IO Tests have not yet been implemented'
		);
	}
}

// __END__
