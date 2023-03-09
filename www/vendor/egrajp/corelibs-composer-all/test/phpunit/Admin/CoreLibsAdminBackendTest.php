<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Admin\Backend
 * @coversDefaultClass \CoreLibs\Admin\Backend
 * @testdox \CoreLibs\Admin\Backend method tests
 */
final class CoreLibsAdminBackendTest extends TestCase
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
	 * @testdox Admin\Backend Class tests
	 *
	 * @return void
	 */
	public function testAdminBackend()
	{
		/* $this->assertTrue(true, 'ACL Login Tests not implemented');
		$this->markTestIncomplete(
			'ACL\Login Tests have not yet been implemented'
		); */
		$this->markTestSkipped('No implementation for Admin\Backend at the moment');
	}
}

// __END__
