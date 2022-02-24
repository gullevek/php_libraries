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
	private static $db_config = [];
	private static $log;

	public static function setUpBeforeClass(): void
	{
		// define basic connection set valid and one invalid
		self::$db_config = [
			// self localhost/ip connection
			'valid' => [
				'db_name' => 'corelibs_db_io_test',
				'db_user' => 'corelibs_db_io_test',
				'db_pass' => 'corelibs_db_io_test',
				'db_host' => 'localhost',
				'db_port' => 5432,
				'db_schema' => 'public',
				'db_type' => 'pgsql',
				'db_encoding' => '',
				'db_ssl' => 'allow', // allow, disable, require, prefer
				'db_debug' => true,
			],
			'invalid' => [
				'db_name' => '',
				'db_user' => '',
				'db_pass' => '',
				'db_host' => '',
				'db_port' => 5432,
				'db_schema' => 'public',
				'db_type' => 'pgsql',
				'db_encoding' => '',
				'db_ssl' => 'allow', // allow, disable, require, prefer
				'db_debug' => true,
			],
		];
		self::$log = new \CoreLibs\Debug\Logging([
			// 'log_folder' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'file_id' => 'CoreLibs-DB-IO-Test',
			'debug_all' => false,
			'echo_all' => false,
			'print_all' => false,
		]);
	}

	/**
	 * Check that we can actually do these tests
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
		// print_r(self::$db_config);
	}

	// - connect to DB test (getConnectionStatus)
	// - connected get dbInfo data check (show true, false)
	// - disconnect: dbClose
	// - connected get all default settings via get
	//   dbGetDebug, dbGetMaxQueryCall, dbGetSchema, dbGetEncoding,
	//   dbVerions, dbCompareVersion
	//   dbGetSetting (name, user, ecnoding, schema, host, port, ssl, debug, password)
	// - connected set
	//   dbSetMaxQueryCall, dbSetDebug, dbToggleDebug, dbSetSchema, dbSetEncoding
	// - db execution tests
	//   dbReturn, dbDumpData, dbCacheReset, dbExec, dbExecAsync, dbCheckAsync
	//   dbFetchArray, dbReturnRow, dbReturnArray, dbCursorPos, dbCursorNumRows,
	//   dbShowTableMetaData, dbPrepare, dbExecute
	//   dbEscapeString, dbEscapeLiteral, dbEscapeBytea, dbSqlEscape, dbArrayParse
	// - complex write sets
	//   dbWriteData, dbWriteDataExt
	// - non connection tests
	//   dbBoolean, dbTimeFormat
	// - internal read data (post exec)
	//   dbGetReturning, dbGetInsertPKName, dbGetInsertPK, dbGetReturningExt,
	//   dbGetReturningArray, dbGetCursorExt, dbGetNumRows,
	//   getHadError, getHadWarning,
	//   dbResetQueryCalled, dbGetQueryCalled
	// - deprecated tests
	//   getInsertReturn, getReturning, getInsertPK, getReturningExt,
	//   getCursorExt, getNumRows

	public function testConnection()
	{
		//
		$db = new \CoreLibs\DB\IO(
			self::$db_config['invalid'],
			self::$log
		);
		print "INIT ERROR INVALID: " . $db->getConnectionStatus() . "\n";
		print "LAST ERROR: " . $db->dbGetLastError(true) . "\n";
		print "ERRORS: " . print_r($db->dbGetErrorHistory(true), true) . "\n";
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		print "INIT ERROR VALID: " . $db->getConnectionStatus() . "\n";
	}

	/**
	 * grouped DB IO test
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
