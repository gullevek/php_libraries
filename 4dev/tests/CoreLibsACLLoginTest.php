<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for ACL\Login
 * @coversDefaultClass \CoreLibs\ACL\Login
 * @testdox \CoreLibs\ACL\Login method tests
 */
final class CoreLibsACLLoginTest extends TestCase
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
	 * @testdox ACL\Login Class tests
	 *
	 * @return void
	 */
	public function testACLLogin()
	{
		$this->assertTrue(true, 'ACL Login Tests not implemented');
		$this->markTestIncomplete(
			'ACL\Login Tests have not yet been implemented'
		);
	}
}

// __END__
