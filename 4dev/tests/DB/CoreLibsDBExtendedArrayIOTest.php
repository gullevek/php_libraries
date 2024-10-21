<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for DB\Extended\ArrayIO
 * This will only test the PgSQL parts
 * @coversDefaultClass \CoreLibs\DB\Extended\ArrayIO
 * @testdox \CoreLibs\Extended\ArrayIO method tests for extended DB interface
 */
final class CoreLibsDBExtendedArrayIOTest extends TestCase
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
	 * @testdox DB\Extended\ArrayIO Class tests
	 *
	 * @return void
	 */
	public function testArrayDBIO()
	{
		// $this->assertTrue(true, 'DB Extended ArrayIO Tests not implemented');
		$this->markTestIncomplete(
			'DB\Extended\ArrayIO Tests have not yet been implemented'
		);
	}
}

// __END__
