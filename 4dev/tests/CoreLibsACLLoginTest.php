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
	private static $db;
	private static $log;

	/**
	 * start DB conneciton, setup DB, etc
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		if (!extension_loaded('pgsql')) {
			self::markTestSkipped(
				'The PgSQL extension is not available.'
			);
		}
		// logger is always needed
		// define basic connection set valid and one invalid
		self::$log = new \CoreLibs\Debug\Logging([
			// 'log_folder' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'file_id' => 'CoreLibs-ACL-Login-Test',
			'debug_all' => false,
			'echo_all' => false,
			'print_all' => false,
		]);
		// if we do have pgsql, we need to create a test DB or check that one
		// exists and clean the table to zero state
		self::$db = new \CoreLibs\DB\IO(
			[
				'db_name' => 'corelibs_acl_login_test',
				'db_user' => 'corelibs_acl_login_test',
				'db_pass' => 'corelibs_acl_login_test',
				'db_host' => 'localhost',
				'db_port' => 5432,
				'db_schema' => 'public',
				'db_type' => 'pgsql',
				'db_encoding' => '',
				'db_ssl' => 'allow', // allow, disable, require, prefer
				'db_debug' => true,
			],
			self::$log
		);
		if (!self::$db->dbGetConnectionStatus()) {
			self::markTestSkipped(
				'Cannot connect to valid Test DB for ACL\Login test.'
			);
		}
		/*
		// check if they already exist, drop them
		if ($db->dbShowTableMetaData('table_with_primary_key') !== false) {
			$db->dbExec("DROP TABLE table_with_primary_key");
			$db->dbExec("DROP TABLE table_without_primary_key");
			$db->dbExec("DROP TABLE test_meta");
		}
		*/
	}

	/**
	 * close db
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		if (self::$db->dbGetConnectionStatus()) {
			self::$db->dbClose();
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
