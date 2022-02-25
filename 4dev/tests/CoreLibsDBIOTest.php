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
	private static $db_config = [
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
	private static $log;

	public static function setUpBeforeClass(): void
	{
		// define basic connection set valid and one invalid
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

	// - connect to DB test (dbGetConnectionStatus)
	// - connected get dbInfo data check (show true, false)
	// - disconnect: dbClose

	/**
	 * connection DB strings
	 *
	 * @return array
	 */
	public function connectionProvider(): array
	{
		// 0: connection array
		// 1: status after connection
		// 2: info string
		return [
			'invalid connection' => [
				self::$db_config['invalid'],
				false,
				"-DB-info-> Connected to db '' with schema 'public' as user "
					. "'' at host '' on port '5432' with ssl mode 'allow' **** "
					. "-DB-info-> DB IO Class debug output: Yes **** ",
				null,
			],
			'valid connection' => [
				self::$db_config['valid'],
				true,
				"-DB-info-> Connected to db 'corelibs_db_io_test' with "
					. "schema 'public' as user 'corelibs_db_io_test' at host "
					. "'localhost' on port '5432' with ssl mode 'allow' **** "
					. "-DB-info-> DB IO Class debug output: Yes **** ",
				null,
			],
		];
	}

	/**
	 * Connection tests
	 *
	 * @covers ::__connectToDB
	 * @dataProvider connectionProvider
	 * @testdox Connection will be $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testConnection(
		array $connection,
		bool $expected_status,
		string $expected_string
	): void {
		$db = new \CoreLibs\DB\IO(
			$connection,
			self::$log
		);
		$this->assertEquals(
			$db->dbGetConnectionStatus(),
			$expected_status
		);
		$this->assertEquals(
			$db->dbInfo(false, true),
			$expected_string
		);

		// print "DB: " . $db->dbInfo(false, true) . "\n";
		if ($db->dbGetConnectionStatus()) {
			// db close check
			$db->dbClose();
			$this->assertEquals(
				$db->dbGetConnectionStatus(),
				false
			);
		} else {
			// TODO: error checks
			// print "LAST ERROR: " . $db->dbGetLastError(true) . "\n";
			// print "ERRORS: " . print_r($db->dbGetErrorHistory(true), true) . "\n";
		}
	}

	// - connected get all default settings via get
	//   dbGetDebug, dbGetSchema, dbGetEncoding, dbGetMaxQueryCall
	//   dbGetSetting (name, user, ecnoding, schema, host, port, ssl, debug, password)
	// - connected set
	//   dbSetMaxQueryCall, ,
	//   dbSetDebug, dbToggleDebug, dbSetSchema, dbSetEncoding

	// - db execution tests
	//   dbReturn, dbDumpData, dbCacheReset, dbExec, dbExecAsync, dbCheckAsync
	//   dbFetchArray, dbReturnRow, dbReturnArray, dbCursorPos, dbCursorNumRows,
	//   dbShowTableMetaData, dbPrepare, dbExecute
	// - connected stand alone tests
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

	// public function testDbSettings(): void
	// {
	// 	//
	// }

	// - connected version test
	//   dbVerions, dbCompareVersion

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function versionProvider(): array
	{
		return [
			'compare = ok' => [ '=13.5.0', true ],
			'compare = bad' => [ '=9.2.0', false ],
			'compare < ok' => [ '<20.0.0', true ],
			'compare < bad' => [ '<9.2.0', false ],
			'compare <= ok a' => [ '<=20.0.0', true ],
			'compare <= ok b' => [ '<=13.5.0', true ],
			'compare <= false' => [ '<=9.2.0', false ],
			'compare > ok' => [ '>9.2.0', true ],
			'compare > bad' => [ '>20.2.0', false ],
			'compare >= ok a' => [ '>=13.5.0', true ],
			'compare >= ok b' => [ '>=9.2.0', true ],
			'compare >= bad' => [ '>=20.0.0', false ],
		];
	}

	/**
	 * NOTE
	 * Version tests will fail if versions change
	 * Current base as Version 13.5 for equal check
	 * I can't mock a function on the same class when it is called in a method
	 * NOTE
	 *
	 * @covers ::dbCompareVersion
	 * @dataProvider versionProvider
	 * @testdox Version $input compares as $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testDbVerson(string $input, bool $expected): void
	{
		// connect to valid DB
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// print "DB VERSION: " . $db->dbVersion()  . "\n";

		// Create a stub for the SomeClass class.
		// $stub = $this->createMock(\CoreLibs\DB\IO::class);
		// $stub->method('dbVersion')
		// 		->willReturn('13.1.0');

		$this->assertEquals(
			$db->dbCompareVersion($input),
			$expected
		);

		// print "IT HAS TO BE 13.1.0: " . $stub->dbVersion() . "\n";
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
