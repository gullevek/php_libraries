<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Admin\EditBase
 * @coversDefaultClass \CoreLibs\Admin\EditBase
 * @testdox \CoreLibs\Admin\EditBase method tests
 */
final class CoreLibsAdminEditBaseTest extends TestCase
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
	 * @testdox Admin\EditBase Class tests
	 *
	 * @return void
	 */
	public function testAdminEditBase()
	{
		$this->markTestSkipped('No implementation for Admin\EditBase at the moment');
	}
}

// __END__
