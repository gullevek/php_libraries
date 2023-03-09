<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Admin\EditPage
 * @coversDefaultClass \CoreLibs\Admin\EditPage
 * @testdox \CoreLibs\Admin\EditPage method tests
 */
final class CoreLibsAdminEditPageTest extends TestCase
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
	 * @testdox Admin\EditPage Class tests
	 *
	 * @return void
	 */
	public function testAdminEditPage()
	{
		/* $this->assertTrue(true, 'ACL Login Tests not implemented');
		$this->markTestIncomplete(
			'ACL\Login Tests have not yet been implemented'
		); */
		$this->markTestSkipped('No implementation for Admin\EditPage at the moment');
	}
}

// __END__
