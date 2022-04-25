<?php // phpcs:disable Generic.Files.LineLength

/*
*** IMPORTANT ***
This test needs a working database with certain tables setup
DB Host: localhost
DB Name: corelibs_db_io_test
DB User: corelibs_db_io_test
DB Password: corelibs_db_io_test
DB Encoding: UTF8 (MUST!)
User must be able to drop/create tables
In case of changes the valid_* $db_config entries must be changed
*** IMPORTANT ***

Below tables will be automatically created
Table with Primary Key: table_with_primary_key
Table without Primary Key: table_without_primary_key

Table with primary key has additional row:
row_primary_key	SERIAL PRIMARY KEY,
Each table has the following rows
row_int INT,
row_numeric NUMERIC,
row_varchar VARCHAR,
row_json JSON
row_jsonb JSONB,
row_bytea BYTEA,
row_timestamp TIMESTAMP WITHOUT TIME ZONE,
row_date DATE,
row_interval INTERVAL,

*/

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
		// same as valid, but db debug is off
		'valid_debug_false' => [
			'db_name' => 'corelibs_db_io_test',
			'db_user' => 'corelibs_db_io_test',
			'db_pass' => 'corelibs_db_io_test',
			'db_host' => 'localhost',
			'db_port' => 5432,
			'db_schema' => 'public',
			'db_type' => 'pgsql',
			'db_encoding' => '',
			'db_ssl' => 'allow', // allow, disable, require, prefer
			'db_debug' => false,
		],
		// same as valid, but encoding is set
		'valid_with_encoding_utf8' => [
			'db_name' => 'corelibs_db_io_test',
			'db_user' => 'corelibs_db_io_test',
			'db_pass' => 'corelibs_db_io_test',
			'db_host' => 'localhost',
			'db_port' => 5432,
			'db_schema' => 'public',
			'db_type' => 'pgsql',
			'db_encoding' => 'UTF-8',
			'db_ssl' => 'allow', // allow, disable, require, prefer
			'db_debug' => true,
		],
		// valid with no schema set
		'valid_no_schema' => [
			'db_name' => 'corelibs_db_io_test',
			'db_user' => 'corelibs_db_io_test',
			'db_pass' => 'corelibs_db_io_test',
			'db_host' => 'localhost',
			'db_port' => 5432,
			'db_schema' => '',
			'db_type' => 'pgsql',
			'db_encoding' => '',
			'db_ssl' => 'allow', // allow, disable, require, prefer
			'db_debug' => true,
		],
		// invalid (missing db name)
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

	/**
	 * Test if pgsql module loaded
	 * Check if valid DB connection works
	 * Check if tables exist and remove them
	 * Create test tables
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
		// define basic connection set valid and one invalid
		self::$log = new \CoreLibs\Debug\Logging([
			// 'log_folder' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'file_id' => 'CoreLibs-DB-IO-Test',
			'debug_all' => false,
			'echo_all' => false,
			'print_all' => false,
		]);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		if (!$db->dbGetConnectionStatus()) {
			self::markTestSkipped(
				'Cannot connect to valid Test DB for DB\IO test.'
			);
		}
		// check if they already exist, drop them
		if ($db->dbShowTableMetaData('table_with_primary_key') !== false) {
			$db->dbExec("DROP TABLE table_with_primary_key");
			$db->dbExec("DROP TABLE table_without_primary_key");
			$db->dbExec("DROP TABLE test_meta");
		}
		$base_table = "uid VARCHAR, " // uid is for internal reference tests
			. "row_int INT, "
			. "row_numeric NUMERIC, "
			. "row_varchar VARCHAR, "
			. "row_varchar_literal VARCHAR, "
			. "row_json JSON, "
			. "row_jsonb JSONB, "
			. "row_bytea BYTEA, "
			. "row_timestamp TIMESTAMP WITHOUT TIME ZONE, "
			. "row_date DATE, "
			. "row_interval INTERVAL, "
			. "row_array_int INT ARRAY, "
			. "row_array_varchar VARCHAR ARRAY"
			. ") WITHOUT OIDS";
		// create the tables
		$db->dbExec(
			"CREATE TABLE table_with_primary_key ("
			// primary key name is table + '_id'
			. "table_with_primary_key_id SERIAL PRIMARY KEY, "
			. $base_table
		);
		$db->dbExec(
			"CREATE TABLE table_without_primary_key ("
			. $base_table
		);
		// create simple table for meta test
		$db->dbExec(
			"CREATE TABLE test_meta ("
			. "row_1 VARCHAR, "
			. "row_2 INT"
			. ") WITHOUT OIDS"
		);
		// set some test schema
		$db->dbExec("CREATE SCHEMA IF NOT EXISTS testschema");
		// end connection
		$db->dbClose();
	}

	/**
	 * Check that we can actually do these tests
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		// print_r(self::$db_config);
	}

	// - connected version test
	//   dbVerions, dbVersionNum, dbVersionInfo, dbVersionInfoParameters,
	//   dbCompareVersion

	/**
	 * Just checks that the return value of dbVersion matches basic regex
	 *
	 * @covers ::dbVersion
	 * @testdox test db version string return matching retex
	 *
	 * @return void
	 */
	public function testDbVersion(): void
	{
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		$this->assertMatchesRegularExpression(
			"/^\d+\.\d+(\.\d+)?$/",
			$db->dbVersion()
		);

		$db->dbClose();
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbVersionNumeric
	 * @testdox test db version numeric return matching retex
	 *
	 * @return void
	 */
	public function testDbVersionNumeric(): void
	{
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		$version = $db->dbVersionNumeric();
		// must be int
		$this->assertIsInt($version);
		// assume 90606 or 130006
		// should this change, the regex below must change
		$this->assertMatchesRegularExpression(
			"/^\d{5,6}?$/",
			(string)$version
		);

		$db->dbClose();
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbVersionInfoParameters
	 * @testdox test db version parameters are returned as array
	 *
	 * @return void
	 */
	public function testDbVersionInfoParameters(): void
	{
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		$parameters = $db->dbVersionInfoParameters();

		$this->assertIsArray($parameters);
		$this->assertGreaterThan(
			1,
			count($parameters)
		);
		// must have at least this
		$this->assertContains(
			'server',
			$parameters
		);

		$db->dbClose();
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function versionInfoProvider(): array
	{
		return [
			'client' => [
				'client',
				"/^\d+\.\d+/"
			],
			'session authorization' => [
				'session_authorization',
				"/^\w+$/"
			],
			'test non existing' => [
				'non_existing',
				'/^$/'
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbVersionInfo
	 * @dataProvider versionInfoProvider
	 * @testdox Version Info $parameter matches as $expected [$_dataName]
	 *
	 * @param string $parameter
	 * @param string $expected
	 * @return void
	 */
	public function testDbVersionInfo(string $parameter, string $expected): void
	{
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		$this->assertMatchesRegularExpression(
			$expected,
			$db->dbVersionInfo($parameter)
		);

		$db->dbClose();
	}

	/**
	 * Returns test list for dbCompareVersion check
	 * NOTE: unless we fully mock the =version check needs to be updated
	 *
	 * @return array
	 */
	public function versionProvider(): array
	{
		// 1: vesion to compare to
		// 2: expected outcome
		return [
			'compare = ok' => [ '=13.6.0', true ],
			'compare = bad' => [ '=9.2.0', false ],
			'compare < ok' => [ '<99.0.0', true ],
			'compare < bad' => [ '<9.2.0', false ],
			'compare <= ok a' => [ '<=99.0.0', true ],
			'compare <= ok b' => [ '<=13.6.0', true ],
			'compare <= false' => [ '<=9.2.0', false ],
			'compare > ok' => [ '>9.2.0', true ],
			'compare > bad' => [ '>99.2.0', false ],
			'compare >= ok a' => [ '>=13.6.0', true ],
			'compare >= ok b' => [ '>=9.2.0', true ],
			'compare >= bad' => [ '>=99.0.0', false ],
		];
	}

	/**
	 * NOTE
	 * Version tests will fail if versions change
	 * Current base as Version 13.6 for equal check
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

		// TODO: Mock \CoreLibs\DB\SQL\PgSQL somehow or Mock \CoreLibsDB\IO::dbVersion
		// Create a stub for the SomeClass class.
		// $stub = $this->createMock(\CoreLibs\DB\IO::class);
		// $stub->method('dbVersion')
		// 	->willReturn('13.1.0');
		// print "DB: " . $stub->dbVersion() . "\n";
		// print "TEST: " . ($stub->dbCompareVersion('=13.1.0') ? 'YES' : 'NO') . "\n";
		// print "TEST: " . ($stub->dbCompareVersion('=13.6.0') ? 'YES' : 'NO') . "\n";
		// $mock = $this->getMockBuilder(CoreLibs\DB\IO::class)
		// 	->addMethods(['dbVersion'])
		// 	->ge‌​tMock();

		$this->assertEquals(
			$expected,
			$db->dbCompareVersion($input)
		);

		// print "IT HAS TO BE 13.1.0: " . $stub->dbVersion() . "\n";
	}

	// - connect to DB test (dbGetConnectionStatus)
	// - connected get dbInfo data check (show true, false)
	// - disconnect: dbClose

	/**
	 * connection DB strings list with info blocks for connection testing
	 *
	 * @return array
	 */
	public function connectionProvider(): array
	{
		// 0: connection array
		// 1: status after connection
		// 2: info string
		// 3: ???
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
	 * Connection tests and confirmation with info blocks
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
			$expected_status,
			$db->dbGetConnectionStatus(),
		);
		$this->assertEquals(
			$expected_string,
			$db->dbInfo(false, true)
		);

		// print "DB: " . $db->dbInfo(false, true) . "\n";
		if ($db->dbGetConnectionStatus()) {
			// db close check
			$db->dbClose();
			$this->assertEquals(
				false,
				$db->dbGetConnectionStatus()
			);
		} else {
			// TODO: error checks
			// print "LAST ERROR: " . $db->dbGetLastError(true) . "\n";
			// print "ERRORS: " . print_r($db->dbGetCombinedErrorHistory(), true) . "\n";
		}
	}

	// - debug flag sets
	//   dbGetDebug,  dbSetDebug, dbToggleDebug

	/**
	 * test set for setDebug
	 *
	 * @return array
	 */
	public function debugSetProvider(): array
	{
		return [
			'default debug set' => [
				// what base connection
				'valid',
				// actions (set)
				null,
				// set exepected
				self::$db_config['valid']['db_debug'],
			],
			'set debug to true' => [
				'valid_debug_false',
				true,
				true,
			],
			'set debug to false' => [
				'valid',
				false,
				false,
			]
		];
	}

	/**
	 * test set for toggleDEbug
	 *
	 * @return array
	 */
	public function debugToggleProvider(): array
	{
		return [
			'default debug set' => [
				// what base connection
				'valid',
				// actions
				null,
				// toggle is inverse
				self::$db_config['valid']['db_debug'] ? false : true,
			],
			'toggle debug to true' => [
				'valid_debug_false',
				true,
				true,
			],
			'toggle debug to false' => [
				'valid',
				false,
				false,
			]
		];
	}

	/**
	 * Test dbSetDbug, dbGetDebug
	 *
	 * @covers ::dbGetDbug
	 * @covers ::dbSetDebug
	 * @dataProvider debugSetProvider
	 * @testdox Setting debug $set will be $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testDbSetDebug(
		string $connection,
		?bool $set,
		bool $expected
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$set === null ?
				$db->dbSetDebug() :
				$db->dbSetDebug($set)
		);
		// must always match
		$this->assertEquals(
			$expected,
			$db->dbGetDebug()
		);
		$db->dbClose();
	}

	/**
	 * Test dbToggleDebug, dbGetDebug
	 *
	 * @covers ::dbGetDbug
	 * @covers ::dbSetDebug
	 * @dataProvider debugToggleProvider
	 * @testdox Toggle debug $toggle will be $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testDbToggleDebug(
		string $connection,
		?bool $toggle,
		bool $expected
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$toggle === null ?
				$db->dbToggleDebug() :
				$db->dbToggleDebug($toggle)
		);
		// must always match
		$this->assertEquals(
			$expected,
			$db->dbGetDebug()
		);
		$db->dbClose();
	}

	// - set max query call sets
	//   dbSetMaxQueryCall, dbGetMaxQueryCall

	/**
	 * test list for max query run settings
	 *
	 * @return array
	 */
	public function maxQueryCallProvider(): array
	{
		// 0: max call number
		// 1: expected flag from set call
		// 2: expected number from get call
		// 3: expected last warning id
		// 4: expected last error id
		return [
			'set default' => [
				null,
				true,
				\CoreLibs\DB\IO::DEFAULT_MAX_QUERY_CALL,
				// expected warning
				'',
				// expected error
				'',
			],
			'set to -1 with warning' => [
				-1,
				true,
				-1,
				// warning 50
				'50',
				'',
			],
			'set to 0 with error' => [
				0,
				false,
				\CoreLibs\DB\IO::DEFAULT_MAX_QUERY_CALL,
				'',
				'51',
			],
			'set to -2 with error' => [
				-2,
				false,
				\CoreLibs\DB\IO::DEFAULT_MAX_QUERY_CALL,
				'',
				'51',
			],
			'set to valid value' => [
				10,
				true,
				10,
				'',
				'',
			]
		];
	}

	/**
	 * Test max query call set and get flow with warging/errors
	 *
	 * @covers ::dbSetMaxQueryCall
	 * @covers ::dbGetMaxQueryCall
	 * @dataProvider maxQueryCallProvider
	 * @testdox Set max query call with $max_calls out with $expected_flag and $expected_max_calls (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param integer|null $max_calls
	 * @param boolean $expected_flag
	 * @param integer $expected_max_calls
	 * @param string $warning
	 * @param string $error
	 * @return void
	 */
	public function testMaxQueryCall(
		?int $max_calls,
		bool $expected_flag,
		int $expected_max_calls,
		string $warning,
		string $error
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$this->assertEquals(
			$expected_flag,
			// TODO special test with null call too
			$max_calls === null ?
				$db->dbSetMaxQueryCall() :
				$db->dbSetMaxQueryCall($max_calls)
		);
		$this->assertEquals(
			$expected_max_calls,
			$db->dbGetMaxQueryCall()
		);
		// if string for warning or error is not empty check
		$this->assertEquals(
			$warning,
			$db->dbGetLastWarning()
		);
		$this->assertEquals(
			$error,
			$db->dbGetLastError()
		);
		$db->dbClose();
	}

	// - all general data from connection array
	//   dbGetSetting (name, user, ecnoding, schema, host, port, ssl, debug, password)

	/**
	 * returns ALL connections sets as a group with
	 * conneciton name on pos 0 and the connection settings on pos 1
	 *
	 * @return array
	 */
	public function connectionCompleteProvider(): array
	{
		$connections = [];
		foreach (self::$db_config as $connection => $settings) {
			$connections['DB Connection: ' . $connection] = [
				$connection,
				$settings,
			];
		}
		return $connections;
	}

	/**
	 * Test connection array settings return call
	 *
	 * @covers ::dbGetSetting
	 * @dataProvider connectionCompleteProvider
	 * @testdox Get settings for connection $connection [$_dataName]
	 *
	 * @param string $connection,
	 * @param array $settings
	 * @return void
	 */
	public function testGetSetting(string $connection, array $settings): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);

		// each must match
		foreach (
			[
				'name' => 'db_name',
				'user' => 'db_user',
				'encoding' => 'db_encoding',
				'schema' => 'db_schema',
				'host' => 'db_host',
				'port' => 'db_port',
				'ssl' => 'db_ssl',
				'debug' => 'db_debug',
				'password' => '***',
			] as $read => $compare
		) {
			$this->assertEquals(
				$read == 'password' ? $compare : $settings[$compare],
				$db->dbGetSetting($read)
			);
		}

		$db->dbClose();
	}

	// - test boolean convert
	//   dbBoolean

	/**
	 * test list for dbBoolean
	 *
	 * @return array
	 */
	public function booleanProvider(): array
	{
		// 0: set
		// 1: reverse flag
		// 2: expected
		return [
			'source "t" to true' => [
				't',
				false,
				true,
			],
			'source "t" to true null flag' => [
				't',
				null,
				true,
			],
			'source "true" to true' => [
				'true',
				false,
				true,
			],
			'source "f" to false' => [
				'f',
				false,
				false,
			],
			'source "false" to false' => [
				'false',
				false,
				false,
			],
			'source anything to true' => [
				'something',
				false,
				true,
			],
			'source empty to false' => [
				'',
				false,
				false,
			],
			'source bool true to "t"' => [
				true,
				true,
				't',
			],
			'source bool false to "f"' => [
				false,
				true,
				'f',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbBoolean
	 * @dataProvider booleanProvider
	 * @testdox Have $source and convert ($reverse) to $expected [$_dataName]
	 *
	 * @param string|bool $source
	 * @param bool|null $reverse
	 * @param string|bool $expected
	 * @return void
	 */
	public function testDbBoolean($source, ?bool $reverse, $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$reverse === null ?
				$db->dbBoolean($source) :
				$db->dbBoolean($source, $reverse)
		);
		$db->dbClose();
	}

	// - test interval/age string conversion to
	//   \CoreLibs\Combined\DateTime::stringToTime/timeStringFormat compatbile
	//   dbTimeFormat

	/**
	 * test list for timestamp parsers
	 *
	 * @return array
	 */
	public function timeFormatProvider(): array
	{
		// 0: set
		// 1: micro seconds flag
		// 2: expected
		return [
			'interval a' => [
				'41 years 9 mons 18 days',
				false,
				'41 years 9 mons 18 days'
			],
			'interval a null micro time' => [
				'41 years 9 mons 18 days',
				null,
				'41 years 9 mons 18 days'
			],
			'interval a-1' => [
				'41 years 9 mons 18 days 12:31:11',
				false,
				'41 years 9 mons 18 days 12h 31m 11s'
			],
			'interval a-2' => [
				'41 years 9 mons 18 days 12:31:11.87418',
				false,
				'41 years 9 mons 18 days 12h 31m 11s'
			],
			'interval a-2-1' => [
				'41 years 9 mons 18 days 12:31:11.87418',
				true,
				'41 years 9 mons 18 days 12h 31m 11s 87418ms'
			],
			'interval a-3' => [
				'41 years 9 mons 18 days 12:00:11',
				false,
				'41 years 9 mons 18 days 12h 11s'
			],
			'interval b' => [
				'1218 days',
				false,
				'1218 days'
			],
			'interval c' => [
				'1 year 1 day',
				false,
				'1 year 1 day'
			],
			'interval d' => [
				'12:00:05',
				false,
				'12h 5s'
			],
			'interval e' => [
				'00:00:00.12345',
				true,
				'12345ms'
			],
			'interval e-1' => [
				'00:00:00',
				true,
				'0s'
			],
			'interval a (negative)' => [
				'-41 years 9 mons 18 days 00:05:00',
				false,
				'-41 years 9 mons 18 days 5m'
			],
		];
	}

	/**
	 * Test parsing of interval strings into human readable format
	 *
	 * @covers ::dbTimeFormat
	 * @dataProvider timeFormatProvider
	 * @testdox Have $source and convert ($show_micro) to $expected [$_dataName]
	 *
	 * @param string $source
	 * @param bool|null $show_micro
	 * @param string $expected
	 * @return void
	 */
	public function testDbTimeFormat(string $source, ?bool $show_micro, string $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$show_micro === null ?
				$db->dbTimeFormat($source) :
				$db->dbTimeFormat($source, $show_micro)
		);
		$db->dbClose();
	}

	// - convert PostreSQL arrays into PHP
	//   dbArrayParse

	/**
	 * test list for array convert
	 *
	 * @return array
	 */
	public function arrayProvider(): array
	{
		// 0: postgresql array string
		// 1: php array
		return [
			'array 1' => [
				'{1,2,3,"4 this is shit"}',
				[1, 2, 3, "4 this is shit"]
			],
			'array 2' => [
				'{{1,2,3},{4,5,6}}',
				[[1, 2, 3], [4, 5, 6]]
			],
			'array 3' => [
				'{{{1,2},{3}},{{4},{5,6}}}',
				[[[1, 2], [3]], [[4], [5, 6]]]
			],
			'array 4' => [
				'{dfasdf,"qw,,e{q\"we",\'qrer\'}',
				['dfasdf', 'qw,,e{q"we', 'qrer']
			]
		];
	}

	/**
	 * test convert PostgreSQL array to PHP array
	 *
	 * @covers ::dbArrayParse
	 * @dataProvider arrayProvider
	 * @testdox Input array string $input to $expected [$_dataName]
	 *
	 * @param string $input
	 * @param array|bool $expected
	 * @return void
	 */
	public function testDbArrayParse(string $input, $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$db->dbArrayParse($input)
		);
		$db->dbClose();
	}

	// - string escape tests
	//   dbEscapeString, dbEscapeLiteral, dbEscapeIdentifier,

	/**
	 * test list for string encodig
	 *
	 * @return array
	 */
	public function stringProvider(): array
	{
		// 0: input
		// 1: expected string
		// 2: expected literal
		// 3: expected identifier
		return [
			'string normal' => [
				'Foo Bar',
				'Foo Bar',
				'\'Foo Bar\'',
				'"Foo Bar"',
			],
			'string quotes' => [
				'Foo \'" Bar',
				'Foo \'\'" Bar',
				'\'Foo \'\'" Bar\'',
				'"Foo \'"" Bar"',
			],
			'string backslash' => [
				'Foo \ Bar',
				'Foo \ Bar',
				' E\'Foo \\\\ Bar\'',
				'"Foo \ Bar"',
			],
		];
	}

	/**
	 * Check all string escape functions
	 * NOTE:
	 * This depends on the SETTINGS of the DB
	 * The expected setting is the default encoding setting in PostgreSQL
	 * #backslash_quote = safe_encoding
	 * #escape_string_warning = on
	 * TODO: Load current settings from DB and ajust comapre string
	 *
	 * @covers ::dbEscapeString
	 * @covers ::dbEscapeLiteral
	 * @covers ::dbEscapeIdentifier
	 * @dataProvider stringProvider
	 * @testdox Input string $input to $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testStringEscape(
		string $input,
		string $expected_string,
		string $expected_literal,
		string $expected_identifier
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// print "String: " . $input . "\n";
		// print "Escape String: -" . $db->dbEscapeString($input) . "-\n";
		// print "Escape Literal: -" . $db->dbEscapeLiteral($input) . "-\n";
		// print "Escape Identifier: -" . $db->dbEscapeIdentifier($input) . "-\n";
		$this->assertEquals(
			$expected_string,
			$db->dbEscapeString($input)
		);
		$this->assertEquals(
			$expected_literal,
			$db->dbEscapeLiteral($input)
		);
		$this->assertEquals(
			$expected_identifier,
			$db->dbEscapeIdentifier($input)
		);

		$db->dbClose();
	}

	// - bytea encoding
	//   dbEscapeBytea

	/**
	 * test bytea encoding list
	 *
	 * @return array
	 */
	public function byteaProvider(): array
	{
		// 0: string in
		// 1: bytea expected
		return [
			'standard empty string' => [
				'',
				'\x'
			],
			'random values' => [
				'""9f8a!1012938123712378a../%(\'%)"!"#0"#$%\'"#$00"#$0"#0$0"#$',
				'\x2222396638612131303132393338313233373132333738612e2e2f2528272529222122233022232425272223243030222324302223302430222324'
			],
			'random text' => [
				'string d',
				'\x737472696e672064'
			]
		];
	}

	/**
	 * Test bytea escape
	 * NOTE:
	 * This depends on bytea encoding settings on the server
	 * #bytea_output = 'hex'
	 *
	 * @covers ::dbEscapeBytea
	 * @dataProvider byteaProvider
	 * @testdox Input bytea $input to $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testByteaEscape(string $input, string $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		$this->assertEquals(
			$expected,
			$db->dbEscapeBytea($input)
		);

		$db->dbClose();
	}

	// - string escape catcher
	//   dbSqlEscape

	/**
	 * test list for sql escape function
	 *
	 * @return array
	 */
	public function sqlEscapeProvider(): array
	{
		// 0: data in
		// 1: flag
		// 2: expected output
		return [
			// int (standard)
			'integer value' => [1, 'i', 1,],
			'bad integer value' => ['za', 'i', 0,],
			'empty integer value' => ['', 'i', 'NULL',],
			'null integer value' => [null, 'i', 'NULL',],
			// float (standard)
			'float value' => [1.1, 'f', 1.1,],
			'bad float value' => ['za', 'f', 0,],
			'empty float value' => ['', 'f', 'NULL',],
			'null float value' => [null, 'f', 'NULL',],
			// text (varchar)
			'string value' => ['string value', 't', '\'string value\'',],
			'empty string value' => ['', 't', '\'\'',],
			'null string value' => [null, 't', 'NULL',],
			// text literal (don't worry about ' around string)
			'string value literal' => ['string literal', 'tl', '\'string literal\'',],
			'empty string value literal' => ['', 'tl', '\'\'',],
			'null string value literal' => [null, 'tl', 'NULL',],
			// ?d (I have no idea what that does, is like string)
			'string value d' => ['string d', 'd', '\'string d\'',],
			'empty string value d' => ['', 'd', 'NULL',],
			'null string value d' => [null, 'd', 'NULL',],
			// by bytea
			'string value d' => ['string d', 'by', '\x737472696e672064',],
			'empty string value d' => ['', 'by', 'NULL',],
			'null string value d' => [null, 'by', 'NULL',],
			// b (bool)
			'bool true value' => [true, 'b', '\'t\'',],
			'bool false value' => [false, 'b', '\'f\'',],
			'empty bool value' => ['', 'b', 'NULL',],
			'null bool value' => [null, 'b', 'NULL',],
			// i2 (integer but with 0 instead of NULL for empty)
			'integer2 value' => [1, 'i2', 1,],
			'bad integer2 value' => ['za', 'i2', 0,],
			'empty integer2 value' => ['', 'i2', 0,],
			'null integer2 value' => [null, 'i2', 0,],
		];
	}

	/**
	 * Test for the sql escape/null wrapper function
	 *
	 * @covers ::dbSqlEscape
	 * @dataProvider sqlEscapeProvider
	 * @testdox Input value $input as $flag to $expected [$_dataName]
	 *
	 * @param int|float|string|null $input
	 * @param string $flag
	 * @param int|float|string $expected
	 * @return void
	 */
	public function testSqlEscape($input, string $flag, $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		$this->assertEquals(
			$expected,
			$db->dbSqlEscape($input, $flag)
		);

		$db->dbClose();
	}

	// - show table data
	//   dbShowTableMetaData

	/**
	 * table meta data return test
	 *
	 * @return array
	 */
	public function tableProvider(): array
	{
		// 0: table
		// 1: schema
		// 2: expected array
		return [
			// disable the default tables, they might change
			/* 'table with primary key' => [
				'table_with_primary_key',
				'',
				[]
			],
			'table without primary key' => [
				'table_without_primary_key',
				'public',
				[]
			], */
			'simple table' => [
				'test_meta',
				'',
				[
					'row_1' => [
						'num' => 1,
						'type' => 'varchar',
						'len' => -1,
						'not null' => false,
						'has default' => false,
						'array dims' => 0,
						'is enum' => false,
						'is base' => 1,
						'is composite' => false,
						'is pesudo' => false,
						'description' => '',
					],
					'row_2' => [
						'num' => 2,
						'type' => 'int4',
						'len' => 4,
						'not null' => false,
						'has default' => false,
						'array dims' => 0,
						'is enum' => false,
						'is base' => 1,
						'is composite' => false,
						'is pesudo' => false,
						'description' => '',
					]
				]
			],
			'simple table null schema' => [
				'test_meta',
				null,
				[
					'row_1' => [
						'num' => 1,
						'type' => 'varchar',
						'len' => -1,
						'not null' => false,
						'has default' => false,
						'array dims' => 0,
						'is enum' => false,
						'is base' => 1,
						'is composite' => false,
						'is pesudo' => false,
						'description' => '',
					],
					'row_2' => [
						'num' => 2,
						'type' => 'int4',
						'len' => 4,
						'not null' => false,
						'has default' => false,
						'array dims' => 0,
						'is enum' => false,
						'is base' => 1,
						'is composite' => false,
						'is pesudo' => false,
						'description' => '',
					]
				]
			],
			'table does not exist' => [
				'non_existing',
				'public',
				false
			],
		];
	}

	/**
	 * test the table meta data return flow
	 *
	 * @covers ::dbShowTableMetaData
	 * @dataProvider tableProvider
	 * @testdox Check table $table in schema $schema with $expected [$_dataName]
	 *
	 * @param string $table
	 * @param string|null $schema
	 * @param array<mixed>|bool $expected
	 * @return void
	 */
	public function testDbShowTableMetaData(string $table, ?string $schema, $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// print "TABLE\n" . print_r($db->dbShowTableMetaData($table, $schema), true) . "\n";

		$this->assertEquals(
			$expected,
			$schema === null ?
				$db->dbShowTableMetaData($table) :
				$db->dbShowTableMetaData($table, $schema)
		);

		$db->dbClose();
	}

	// - db exec test for insert/update/select/etc
	//   dbExec, dbResetQueryCalled, dbGetQueryCalled

	/**
	 * provide queries with return results
	 *
	 * @return array
	 */
	public function queryDbExecProvider(): array
	{
		// 0: query
		// 1: optional primary key name, null for empty test
		// 2: expectes result (bool, object (>=8.1)/resource (<8.1))
		// 3: warning
		// 4: error
		// 5: run times, not set is once, true is max + 1
		return [
			// insert
			'table with pk insert' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				'',
				'resource/object',
				'',
				'',
			],
			// insert but null primary key
			'table with pk insert null' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				null,
				'resource/object',
				'',
				'',
			],
			// insert to table with no pk (31?)
			'table with no pk insert' => [
				'INSERT INTO table_without_primary_key (row_date) VALUES (NOW())',
				'',
				'resource/object',
				'',
				'',
			],
			// INSERT: returning array possible multi insert (32)
			'table with pk insert multile' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES'
					. '(NOW()), '
					. '(NOW()), '
					. '(NOW()), '
					. '(NOW())',
				'',
				'resource/object',
				'32',
				'',
			],
			// Skip PK READING
			'table with pk insert and NULL pk name' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				'NULL',
				'resource/object',
				'',
				'',
			],
			// insert with pk set
			'table with pk insert and pk name' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				'table_with_primary_key_id',
				'resource/object',
				'',
				'',
			],
			// update
			'table with pk update' => [
				'UPDATE table_with_primary_key SET row_date = NOW()',
				'',
				'resource/object',
				'',
				'',
			],
			'table with pk select' => [
				'SELECT * FROM table_with_primary_key',
				'',
				'resource/object',
				'',
				'',
			],
			// no query set, error 11
			'no query set' => [
				'',
				'',
				false,
				'',
				'11',
			],
			// no db connection setable (16) [needs Mocking]
			// TODO failed db connection
			// connection busy [async] (41)
			// TODO connection busy
			// same query run too many times (30)
			'same query run too many times' => [
				'SELECT row_date FROM table_with_primary_key',
				'',
				'resource/object',
				'',
				'30',
				true,
			],
			// execution failed (13)
			'invalid query' => [
				'INVALID',
				'',
				false,
				'',
				'13'
			],
			// INSERT: cursor invalid for fetch PK (34) [unreachable code]
			// INSERT: returning has no data (33)
			// invalid RETURNING columns
			// NOTE: After an error was encountered, queries after this
			//       will return a true connection busy although it was error
			//       https://bugs.php.net/bug.php?id=36469
			//       FIX with socket check type
			'invalid returning' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW()) RETURNING invalid',
				'',
				false,
				'',
				'13'
			],
		];
	}

	/**
	 * pure dbExec checker
	 * does not check __dbPostExec run, this will be done in the dbGet* functions
	 * tests (internal read data post exec group)
	 *
	 * @covers ::dbExec
	 * @covers ::dbGetQueryCalled
	 * @covers ::dbResetQueryCalled
	 * @dataProvider queryDbExecProvider
	 * @testdox dbExec $query and pk $pk_name with $expected_return (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param string|null $pk_name
	 * @param object|resource|bool $expected_return
	 * @param string $warning
	 * @param string $error
	 * @param bool $run_many_times
	 * @return void
	 */
	public function testDbExec(
		string $query,
		?string $pk_name,
		$expected_return,
		string $warning,
		string $error,
		bool $run_many_times = false
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// clear any current query
		// $db->dbResetQuery();

		// if expected result is not a bool
		// for PHP 8.1 or higher it has to be an object
		// for anything before PHP 8.1 this has to be a resource

		if (is_bool($expected_return)) {
			$this->assertEquals(
				$expected_return,
				// supress ANY errors here
				$pk_name === null ?
					@$db->dbExec($query) :
					@$db->dbExec($query, $pk_name)
			);
			$last_warning = $db->dbGetLastWarning();
			$last_error = $db->dbGetLastError();
		} else {
			$result = $pk_name === null ?
				$db->dbExec($query) :
				$db->dbExec($query, $pk_name);
			$last_warning = $db->dbGetLastWarning();
			$last_error = $db->dbGetLastError();
			// if PHP or newer, must be Object PgSql\Result
			if (\CoreLibs\Check\PhpVersion::checkPHPVersion('8.1')) {
				$this->assertIsObject(
					$result
				);
				// also check that this is correct instance type
				$this->assertInstanceOf(
					'PgSql\Result',
					$result
				);
			} else {
				$this->assertIsResource(
					$result
				);
			}
		}
		// if we have more than one run time
		// re-run same query and then catch error
		if ($run_many_times) {
			for ($i = 1; $i <= $db->dbGetMaxQueryCall() + 1; $i++) {
				$pk_name === null ?
					$db->dbExec($query) :
					$db->dbExec($query, $pk_name);
			}
			// will fail now
			$this->assertFalse(
				$pk_name === null ?
					$db->dbExec($query) :
					$db->dbExec($query, $pk_name)
			);
			$last_warning = $db->dbGetLastWarning();
			$last_error = $db->dbGetLastError();
			// check query called matching
			$current_count = $db->dbGetQueryCalled($query);
			$this->assertEquals(
				$db->dbGetMaxQueryCall() + 1,
				$current_count
			);
			// reset query called and check again
			$this->assertEquals(
				0,
				$db->dbResetQueryCalled($query)
			);
		}

		// if string for warning or error is not empty check
		$this->assertEquals(
			$warning,
			$last_warning
		);
		$this->assertEquals(
			$error,
			$last_error
		);

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - return one database row
	//   dbReturnRow

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function returnRowProvider(): array
	{
		$insert_query = "INSERT INTO table_with_primary_key (row_int, uid) VALUES (1, 'A')";
		$read_query = "SELECT row_int, uid FROM table_with_primary_key WHERE uid = 'A'";
		// 0: query
		// 1: flag (assoc)
		// 2: result
		// 3: warning
		// 4: error
		// 5: insert query
		return [
			'valid select' => [
				$read_query,
				null,
				[
					'row_int' => 1,
					0 => 1,
					'uid' => 'A',
					1 => 'A'
				],
				'',
				'',
				$insert_query,
			],
			'valid select, assoc only false' => [
				$read_query,
				false,
				[
					'row_int' => 1,
					0 => 1,
					'uid' => 'A',
					1 => 'A'
				],
				'',
				'',
				$insert_query,
			],
			'valid select, assoc only true' => [
				$read_query,
				true,
				[
					'row_int' => 1,
					'uid' => 'A',
				],
				'',
				'',
				$insert_query,
			],
			'empty select' => [
				'',
				null,
				false,
				'',
				'11',
				$insert_query,
			],
			'insert query' => [
				$insert_query,
				null,
				false,
				'',
				'17',
				$insert_query
			],
			// invalid QUERY
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbReturnRow
	 * @dataProvider returnRowProvider
	 * @testdox dbReturnRow $query and assoc $flag_assoc with $expected (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param bool|null $flag_assoc
	 * @param array<mixed>|bool $expected
	 * @param string $warning
	 * @param string $error
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbReturnRow(
		string $query,
		?bool $flag_assoc,
		$expected,
		string $warning,
		string $error,
		string $insert_data
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		$db->dbExec($insert_data);
		// compare
		$this->assertEquals(
			$expected,
			$flag_assoc === null ?
				$db->dbReturnRow($query) :
				$db->dbReturnRow($query, $flag_assoc)
		);
		// get last error/warnings
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		// print "ER: " . $last_error . "/" . $last_warning . "\n";
		// if string for warning or error is not empty check
		$this->assertEquals(
			$warning,
			$last_warning
		);
		$this->assertEquals(
			$error,
			$last_error
		);

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - return all database rows
	//   dbReturnArray

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function returnArrayProvider(): array
	{
		$insert_query = "INSERT INTO table_with_primary_key (row_int, uid) VALUES "
			. "(1, 'A'), (2, 'B')";
		$read_query = "SELECT row_int, uid FROM table_with_primary_key";
		// 0: query
		// 1: flag (assoc)
		// 2: result
		// 3: warning
		// 4: error
		// 5: insert query
		return [
			'valid select' => [
				$read_query,
				null,
				[
					[
						'row_int' => 1,
						'uid' => 'A',
					],
					[
						'row_int' => 2,
						'uid' => 'B',
					],
				],
				'',
				'',
				$insert_query,
			],
			'valid select, assoc ' => [
				$read_query,
				false,
				[
					[
						'row_int' => 1,
						0 => 1,
						'uid' => 'A',
						1 => 'A'
					],
					[
						'row_int' => 2,
						0 => 2,
						'uid' => 'B',
						1 => 'B'
					],
				],
				'',
				'',
				$insert_query,
			],
			'empty select' => [
				'',
				null,
				false,
				'',
				'11',
				$insert_query,
			],
			'insert query' => [
				$insert_query,
				null,
				false,
				'',
				'17',
				$insert_query
			],
			// invalid QUERY
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbReturnArray
	 * @dataProvider returnArrayProvider
	 * @testdox dbReturnArray $query and assoc $flag_assoc with $expected (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param boolean|null $flag_assoc
	 * @param array<mixed>|bool $expected
	 * @param string $warning
	 * @param string $error
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbReturnArrray(
		string $query,
		?bool $flag_assoc,
		$expected,
		string $warning,
		string $error,
		string $insert_data
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		$db->dbExec($insert_data);
		// compare
		$this->assertEquals(
			$expected,
			$flag_assoc === null ?
				$db->dbReturnArray($query) :
				$db->dbReturnArray($query, $flag_assoc)
		);
		// get last error/warnings
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		// if string for warning or error is not empty check
		$this->assertEquals(
			$warning,
			$last_warning
		);
		$this->assertEquals(
			$error,
			$last_error
		);

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - loop data return flow
	//   dbReturn, dbCacheReset, dbCursorPos, dbCursorNumRows, dbGetCursorExt

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function dbReturnProvider(): array
	{
		$insert_query = "INSERT INTO table_with_primary_key (row_int, uid) VALUES "
			. "(1, 'A'), (2, 'B')";
		$read_query = "SELECT row_int, uid FROM table_with_primary_key";
		// 0: read query
		// 1: reset flag, null for default
		// 2: assoc flag, null for default
		// 3: expected return
		// 4: read first, read all flag
		// 5: read all check array
		// 6: warning
		// 7: error
		// 8: insert data
		return [
			'valid select' => [
				$read_query,
				null,
				null,
				[
					'row_int' => 1,
					0 => 1,
					'uid' => 'A',
					1 => 'A'
				],
				true,
				[],
				'',
				'',
				$insert_query
			],
			'valid select, default cache, assoc only' => [
				$read_query,
				\CoreLibs\DB\IO::USE_CACHE,
				true,
				[
					'row_int' => 1,
					'uid' => 'A',
				],
				true,
				[],
				'',
				'',
				$insert_query
			],
			'empty select' => [
				'',
				null,
				null,
				false,
				true,
				[],
				'',
				'11',
				$insert_query,
			],
			'insert query' => [
				$insert_query,
				null,
				null,
				false,
				true,
				[],
				'',
				'17',
				$insert_query
			],
			// from here on a complex read all full tests
			'valid select, full read' => [
				$read_query,
				null,
				null,
				[
					'row_int' => 1,
					0 => 1,
					'uid' => 'A',
					1 => 'A'
				],
				false,
				[],
				'',
				'',
				$insert_query
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbReturn
	 * @covers ::dbCacheReset
	 * @covers ::dbGetCursorExt
	 * @covers ::dbCursorPos
	 * @covers ::dbCursorNumRows
	 * @dataProvider dbReturnProvider
	 * @testdox dbReturn $query and cache $flag_cache and assoc $flag_assoc with $expected (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param integer|null $flag_cache
	 * @param boolean|null $flag_assoc
	 * @param array<mixed>|bool $expected
	 * @param bool $read_first_only
	 * @param array $cursor_ext_checks
	 * @param string $warning
	 * @param string $error
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbReturn(
		string $query,
		?int $flag_cache,
		?bool $flag_assoc,
		$expected,
		bool $read_first_only,
		array $cursor_ext_checks,
		string $warning,
		string $error,
		string $insert_data
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		$db->dbExec($insert_data);

		// all checks below
		if ($read_first_only === true) {
			// simple assert first read, then discard result
			// compare
			$this->assertEquals(
				$expected,
				$flag_cache === null && $flag_assoc === null ?
					$db->dbReturn($query) :
					($flag_assoc === null ?
						$db->dbReturn($query, $flag_cache) :
						$db->dbReturn($query, $flag_cache, $flag_assoc)
					)
			);
			// get last error/warnings
			$last_warning = $db->dbGetLastWarning();
			$last_error = $db->dbGetLastError();
			// if string for warning or error is not empty check
			$this->assertEquals(
				$warning,
				$last_warning
			);
			$this->assertEquals(
				$error,
				$last_error
			);
		} else {
			// all tests here have valid returns already, error checks not needed
			// read all, and then do result compare
			// cursor ext data checks (field names, rows, pos, data)
			// do cache reset test
			$data = [];
			$pos = 0;
			while (
				is_array(
					$res = $flag_cache === null && $flag_assoc === null ?
						$db->dbReturn($query) :
						($flag_assoc === null ?
							$db->dbReturn($query, $flag_cache) :
							$db->dbReturn($query, $flag_cache, $flag_assoc)
						)
				)
			) {
				$data[] = $res;
				$pos++;
				// check cursor pos
				$this->assertEquals(
					$pos,
					$db->dbGetCursorPos($query)
				);
			}
			// does count match for returned data and the cursor num rows
			$this->assertEquals(
				count($data),
				$db->dbGetCursorNumRows($query)
			);
			// does data match
			// try get cursor data for non existing, must be null
			$this->assertNull(
				$db->dbGetCursorExt($query, 'nonexistingfield')
			);
			// does reset data work, query cursor must be null
			$db->dbCacheReset($query);
			$this->assertNull(
				$db->dbGetCursorExt($query)
			);
		}

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - prepared query execute
	//   dbPrepare, dbExecute, dbFetchArray

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function preparedProvider(): array
	{
		$insert_query = "INSERT INTO table_with_primary_key (row_int, uid) VALUES "
			. "(1, 'A'), (2, 'B')";
		$read_query = "SELECT row_int, uid FROM table_with_primary_key";
		// 0: statement name
		// 1: query to prepare
		// 2: primary key name: null for default run
		// 3: arguments for query (single array for all)
		// 4: expected prepare return
		// 5: prepare warning
		// 6: prepare error
		// 7: expected execute return
		// 8: execute warning
		// 9: execute error
		// 11: read query (if insert/update)
		// 11: execute data to check (array)
		// 12: insert data
		return [
			// insert
			'prepare query insert' => [
				// base statements 0-3
				'insert',
				"INSERT INTO table_with_primary_key (row_int, uid) VALUES "
					. "($1, $2)",
				null,
				[990, 'TEST A'],
				// prepare (4-6)
				'result', '', '',
				// execute
				'result', '', '',
				// check query and compare data (for insert/update)
				$read_query,
				[
					[
						'row_int' => 990,
						'uid' => 'TEST A',
					],
				],
				// insert data (for select)
				'',
			],
			// update
			'prepare query update' => [
				'update',
				"UPDATE table_with_primary_key SET "
					. "row_int = $1, "
					. "row_varchar = $2 "
					. "WHERE uid = $3",
				null,
				[550, 'I AM NEW TEXT', 'TEST A'],
				//
				'result', '', '',
				//
				'result', '', '',
				//
				"SELECT row_int, row_varchar FROM table_with_primary_key "
					. "WHERE uid = 'TEST A'",
				[
					[
						'row_int' => 550,
						'row_varchar' => 'I AM NEW TEXT',
					],
				],
				//
				"INSERT INTO table_with_primary_key (row_int, uid) VALUES "
					. "(111, 'TEST A')",
			],
			// select
			'prepare select query' => [
				'select',
				$read_query
					. " WHERE uid = $1",
				null,
				['A'],
				//
				'result', '', '',
				// execute here needs to read too
				'result', '', '',
				//
				'',
				[
					[
						'row_int' => 1,
						'uid' => 'A',
					],
				],
				//
				$insert_query
			],
			// any query but with no parameters
			'prepare select query no parameter' => [
				'select_noparam',
				$read_query,
				null,
				null,
				//
				'result', '', '',
				// execute here needs to read too
				'result', '', '',
				//
				'',
				[
					[
						'row_int' => 1,
						'uid' => 'A',
					],
					[
						'row_int' => 2,
						'uid' => 'B',
					],
				],
				//
				$insert_query
			],
			// no statement name (25)
			'empty statement' => [
				'',
				'SELECT',
				null,
				[],
				//
				false, '', '25',
				//
				false, '', '25',
				//
				'',
				[],
				//
				'',
			],
			// no query (prepare 11)
			// no prepared cursor found with statement name (execute 24)
			'empty query' => [
				'Empty Query',
				'',
				null,
				[],
				//
				false, '', '11',
				//
				false, '', '24',
				//
				'',
				[],
				//
				'',
			],
			// no db connection (prepare/execute 16)
			// TODO no db connection test
			// connection busy (prepare/execute 41)
			// TODO connection busy test
			// query could not be prepare (prepare 21)
			// TODO query could not be prepared test
			// some query with same statement name exists (prepare W20)
			'prepare query with same statement name' => [
				'double',
				$read_query,
				null,
				null,
				//
				true, '20', '',
				//
				'result', '', '',
				// no query but data for data only compare
				'',
				[],
				//,
				$insert_query
			],
			// insert wrong data count compared to needed (execute 23)
			'wrong parmeter count' => [
				'wrong_param_count',
				"INSERT INTO table_with_primary_key (row_int, uid) VALUES "
					. "($1, $2)",
				null,
				[],
				//
				'result', '', '',
				//
				false, '', '23',
				//
				'',
				[],
				//
				''
			],
			// execute does not return a result (22)
			// TODO execute does not return a result
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbPrepare
	 * @covers ::dbExecute
	 * @covers ::dbFetchArray
	 * @dataProvider preparedProvider
	 * @testdox prepared query $stm_name with $expected_prepare (warning $warning_prepare/error $error_prepare) and $expected_execute (warning $warning_execute/error $error_execute) [$_dataName]
	 *
	 * @param string $stm_name
	 * @param string $query
	 * @param string|null $pk_name
	 * @param array|null $query_data
	 * @param bool|string $expected_prepare
	 * @param string $warning_prepare
	 * @param string $error_prepare
	 * @param bool|string $expected_execute
	 * @param string $warning_execute
	 * @param string $error_execute
	 * @param string $expected_data_query
	 * @param array $expected_data
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbPrepared(
		string $stm_name,
		string $query,
		?string $pk_name,
		?array $query_data,
		$expected_prepare,
		string $warning_prepare,
		string $error_prepare,
		$expected_execute,
		string $warning_execute,
		string $error_execute,
		string $expected_data_query,
		array $expected_data,
		string $insert_data
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		if (!empty($insert_data)) {
			$db->dbExec($insert_data);
		}

		// test prepare
		$prepare_result = $pk_name === null ?
			$db->dbPrepare($stm_name, $query) :
			$db->dbPrepare($stm_name, $query, $pk_name);
		// if warning is 20, call prepare again
		if ($warning_prepare == '20') {
			$prepare_result = $pk_name === null ?
				$db->dbPrepare($stm_name, $query) :
				$db->dbPrepare($stm_name, $query, $pk_name);
		}
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		// if result type, or if forced bool
		if (is_string($expected_prepare) && $expected_prepare == 'result') {
			// if PHP or newer, must be Object PgSql\Result
			if (\CoreLibs\Check\PhpVersion::checkPHPVersion('8.1')) {
				$this->assertIsObject(
					$prepare_result
				);
				// also check that this is correct instance type
				$this->assertInstanceOf(
					'PgSql\Result',
					$prepare_result
				);
			} else {
				$this->assertIsResource(
					$prepare_result
				);
			}
		} else {
			$this->assertEquals(
				$expected_prepare,
				$prepare_result
			);
		}
		// error/warning check
		$this->assertEquals(
			$warning_prepare,
			$last_warning,
		);
		$this->assertEquals(
			$error_prepare,
			$last_error,
		);

		// for non fail prepare test exec
		// check test result
		$execute_result = $query_data === null ?
			$db->dbExecute($stm_name) :
			$db->dbExecute($stm_name, $query_data);
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		if ($expected_execute == 'result') {
			// if PHP or newer, must be Object PgSql\Result
			if (\CoreLibs\Check\PhpVersion::checkPHPVersion('8.1')) {
				$this->assertIsObject(
					$execute_result
				);
				// also check that this is correct instance type
				$this->assertInstanceOf(
					'PgSql\Result',
					$execute_result
				);
				// if this is an select use dbFetchArray to get data and test
			} else {
				$this->assertIsResource(
					$execute_result
				);
			}
		} else {
			$this->assertEquals(
				$expected_execute,
				$execute_result
			);
		}
		// error/warning check
		$this->assertEquals(
			$warning_execute,
			$last_warning,
		);
		$this->assertEquals(
			$error_execute,
			$last_error,
		);
		// now check test result if expected return is result
		if (
			$expected_execute == 'result' &&
			!empty($expected_data_query)
		) {
			// $expected_data_query
			// $expected_data
			$rows = $db->dbReturnArray($expected_data_query);
			$this->assertEquals(
				$expected_data,
				$rows
			);
		}
		if (
			$expected_execute == 'result' &&
			$execute_result !== false &&
			empty($expected_data_query) &&
			count($expected_data)
		) {
			// compare previously read data to compare data
			$compare_data = [];
			// read in the query data
			while (is_array($row = $db->dbFetchArray($execute_result, true))) {
				$compare_data[] = $row;
			}
			$this->assertEquals(
				$expected_data,
				$compare_data
			);
		}

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - schema set/get tests
	//   dbGetSchema, dbSetSchema

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function schemaProvider(): array
	{
		// 0: db connection
		// 1: schema to set
		// 2: set result
		// 3: set error
		// 4: get result flagged
		// 5: get result DB
		return [
			'schema get check only' => [
				'valid',
				null,
				true,
				'',
				'public',
				'public',
			],
			'new schema set' => [
				'valid',
				'public',
				true,
				'',
				'public',
				'public',
			],
			'no schema set, set new schema' => [
				'valid_no_schema',
				'public',
				true,
				'',
				'public',
				'public',
			],
			'try to set empty schema' => [
				'valid',
				'',
				false,
				'70',
				'public',
				'public',
			],
			// invalid schema (does not throw error)
			'try to set empty schema' => [
				'valid',
				'invalid',
				false,
				'71',
				'public',
				'public',
			],
			// valid different schema
			'try to set new valid schema' => [
				'valid',
				'testschema',
				true,
				'',
				'testschema',
				'testschema',
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbSetSchema
	 * @covers ::dbGetSchema
	 * @dataProvider schemaProvider
	 * @testdox set schema $schema on $connection with $expected_set (error $error_set) and get $expected_get_var/$expected_get_db [$_dataName]
	 *
	 * @param string $connection
	 * @param string|null $schema
	 * @param boolean $expected_set
	 * @param string $error_set
	 * @param string $expected_get_var
	 * @param string $expected_get_db
	 * @return void
	 */
	public function testDbSchema(
		string $connection,
		?string $schema,
		bool $expected_set,
		string $error_set,
		string $expected_get_var,
		string $expected_get_db
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);

		// schema is not null, we do set testing
		if ($schema !== null) {
			$result_set = $db->dbSetSchema($schema);
			$last_error = $db->dbGetLastError();
			$this->assertEquals(
				$expected_set,
				$result_set
			);
			// error/warning check
			$this->assertEquals(
				$error_set,
				$last_error,
			);
		}

		// get current set from db
		$result_get_var = $db->dbGetSchema(true);
		$this->assertEquals(
			$expected_get_var,
			$result_get_var
		);
		$result_get_db = $db->dbGetSchema();
		$this->assertEquals(
			$expected_get_db,
			$result_get_db
		);

		// close connection
		$db->dbClose();
	}

	// - check error and warning handling
	//   dbGetCombinedErrorHistory, dbGetLastError, dbGetLastWarning

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function errorHandlingProvider(): array
	{
		// 0: some error call
		// 1: type (error/warning)
		// 2: error/warning code
		// 3: return array matcher (excluding time)
		return [
			'trigger error' => [
				0,
				'error',
				'51',
				[
					'timestamp' => "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{1,}$/",
					'level' => 'error',
					'id' => '51',
					'error' => 'Max query call needs to be set to at least 1',
					// run:: can be +1 if called in set and not direct
					'source' => "/^main::run::run::run::run::run::run::(run::)?runBare::runTest::testDbErrorHandling::dbSetMaxQueryCall$/",
					'pg_error' => '',
					'msg' => '',
				]
			],
			'trigger warning' => [
				-1,
				'warning',
				'50',
				[]
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbGetLastError
	 * @covers ::dbGetLastWarning
	 * @covers ::dbGetCombinedErrorHistory
	 * @dataProvider errorHandlingProvider
	 * @testdox error $call_value for type $type with $error_id [$_dataName]
	 *
	 * @param integer $call_value
	 * @param string $type
	 * @param string $error_id
	 * @param array $error_history
	 * @return void
	 */
	public function testDbErrorHandling(
		int $call_value,
		string $type,
		string $error_id,
		array $expected_history
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// trigger the error call
		$db->dbSetMaxQueryCall($call_value);
		if ($type == 'error') {
			$last_error = $db->dbGetLastError();
		} else {
			$last_error = $db->dbGetLastWarning();
		}
		$this->assertEquals(
			$error_id,
			$last_error
		);

		$error_history = $db->dbGetCombinedErrorHistory();
		// pop first error off
		$first_error_element = array_shift($error_history);
		// get first row element
		// comarep all, except timestamp that is a regex
		foreach ($expected_history as $key => $value) {
			// check if starts with / because this is regex (timestamp)
			// if (substr($expected_2, 0, 1) == '/) {
			if (strpos($value, '/') === 0) {
				// this is regex
				$this->assertMatchesRegularExpression(
					$value,
					$first_error_element[0][$key]
				);
			} else {
				// assert equal
				$this->assertEquals(
					$value,
					$first_error_element[0][$key],
				);
			}
		}

		// close connection
		$db->dbClose();
	}

	// - encoding settings (exclude encoding test, just set)
	//   dbGetEncoding, dbSetEncoding

	/**
	 * test encoding change list for dbSetEncoding
	 *
	 * @return array
	 */
	public function encodingProvider(): array
	{
		// 0: connection
		// 1: set encoding
		// 2: expected return from set
		// 2: expected to get
		// 3: error id
		return [
			'default set no encoding' => [
				'valid',
				'',
				false,
				// I expect that the default DB is set to UTF8
				'UTF8',
				'',
			],
			'set to Shift JIS' => [
				'valid',
				'ShiftJIS',
				true,
				'SJIS',
				'',
			],
			'set to Invalid' => [
				'valid',
				'Invalid',
				false,
				'UTF8',
				'81',
			],
			'set to empty' => [
				'valid',
				'',
				false,
				'UTF8',
				'80',
			]
		];
	}

	/**
	 * change DB encoding, only function set test, not test of encoding change
	 * TODO: add encoding changed test with DB insert
	 *
	 * @covers ::dbSetEncoding
	 * @covers ::dbGetEncoding
	 * @dataProvider encodingProvider
	 * @testdox Set encoding on $connection to $set_encoding expect $expected_set_flag and $expected_get_encoding [$_dataName]
	 *
	 * @param string $connection
	 * @param string $set_encoding
	 * @param boolean $expected_set_flag
	 * @param string $expected_get_encoding
	 * @return void
	 */
	public function testEncoding(
		string $connection,
		string $set_encoding,
		bool $expected_set_flag,
		string $expected_get_encoding
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);
		$this->assertEquals(
			$expected_set_flag,
			// avoid bubbling up error
			@$db->dbSetEncoding($set_encoding)
		);
		// show query
		$this->assertEquals(
			$expected_get_encoding,
			$db->dbGetEncoding()
		);
		// pg_version
		$this->assertEquals(
			$expected_get_encoding,
			$db->dbVersionInfo('client_encoding')
		);
		$db->dbClose();
	}

	// - encoding conversion on read and test encoding conversion on db connection
	//   dbSetToEncoding, dbGetToEncoding
	//   [and test encoding transfer with both types]
	//   dbSetEncoding, dbGetEncoding

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function encodingConversionProvider(): array
	{
		// 0: connection
		// 1: target encoding (or alias)
		// 2: optional name for php for proper alias matching
		// 3: text to check
		return [
			'convert from UTF8 to SJIS' => [
				'valid',
				'SJIS',
				null,
				'日本語カタカナひらがな'
			],
			// SHIFT_JIS_2004/SJIS-win
			// EUC_JP/EUC-JP
			//
		];
	}

	/**
	 * tests actually text conversion and not only just setting
	 * NOTE: database is always stored as UTF8 in our case so all
	 * tests check conversion FROM utf8 to a target.
	 * Also because only SJIS is of interest, only this one is tested
	 * https://www.postgresql.org/docs/current/multibyte.html#MULTIBYTE-CHARSET-SUPPORTED
	 * SHIFT_JIS_2004
	 * SJIS (Mskanji, ShiftJIS, WIN932, Windows932)
	 * EUC_JP
	 * EUC_JIS_2004
	 *
	 * @covers ::dbSetToEncoding
	 * @covers ::dbGetToEncoding
	 * @covers ::dbSetEncoding
	 * @covers ::dbGetEncoding
	 * @dataProvider encodingConversionProvider
	 * @testdox Check encoding on $connection with $encoding [$_dataName]
	 *
	 * @param string $connection
	 * @param string $encoding
	 * @param string|null $encoding_php
	 * @param string $text
	 * @return void
	 */
	public function testEncodingConversion(
		string $connection,
		string $encoding,
		?string $encoding_php,
		string $text
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);

		// convert in php unless encoding is the smae
		if (strtolower($encoding) != 'utf8') {
			$encoded = mb_convert_encoding($text, $encoding_php ?? $encoding, 'UTF-8');
		} else {
			$encoded = $text;
		}

		// insert data
		$insert_query = "INSERT INTO table_with_primary_key (row_varchar, uid) VALUES "
			. "(" . $db->dbEscapeLiteral($text) . ", 'A')";
		$db->dbExec($insert_query);
		// for check read
		$read_query = "SELECT row_varchar, uid FROM table_with_primary_key WHERE uid = 'A'";

		// TEST 1 in class
		// test to encoding (conversion with mb_convert_encoding)
		$db->dbSetToEncoding($encoding);
		$this->assertEquals(
			$encoding,
			$db->dbGetToEncoding()
		);
		// read query, check that row_varchar matches
		$row = $db->dbReturnRow($read_query, true);
		$this->assertEquals(
			$encoded,
			$row['row_varchar']
		);
		// reset to encoding to empty
		$db->dbSetToEncoding('');
		// and check
		$this->assertEquals(
			'',
			$db->dbGetToEncoding()
		);

		// TEST 2 DB side
		// same test with setting database encoding
		// if connection encoding differts
		if (strtolower($db->dbGetSetting('encoding')) != strtolower($encoding)) {
			$db->dbSetEncoding($encoding);
		}
		// read from DB and check encoding
		$row = $db->dbReturnRow($read_query, true);
		$this->assertEquals(
			$encoded,
			$row['row_varchar']
		);

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - get primary key
	//   dbGetReturning, dbGetInsertPK, dbGetInsertPKName

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function primaryKeyProvider(): array
	{
		// 0: insert query add (returning, etc)
		// 1: pk_name, null for default
		// 2: table name
		// 3: primary key or empty if none
		return [
			'normal all auto' => [
				'',
				null,
				'table_with_primary_key',
				'table_with_primary_key_id',
			],
			'table without primary key' => [
				'',
				null,
				'table_without_primary_key',
				''
			],
			// valid primary key name
			'normal, with pk_name' => [
				'',
				'table_with_primary_key_id',
				'table_with_primary_key',
				'table_with_primary_key_id',
			],
			// returning name no pk name
			'normal, with returning' => [
				'RETURNING table_with_primary_key_id',
				null,
				'table_with_primary_key',
				'table_with_primary_key_id',
			],
			// both pk name and returning
			'normal, with returning' => [
				'RETURNING table_with_primary_key_id',
				'table_with_primary_key_id',
				'table_with_primary_key',
				'table_with_primary_key_id',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbGetInsertPK
	 * @covers ::dbGetInsertPKName
	 * @dataProvider primaryKeyProvider
	 * @testdox Check returning pk $insert with $pk_name [$_dataName]
	 *
	 * @param string $insert
	 * @param string|null $pk_name
	 * @return void
	 */
	public function testGetPrimaryKey(
		string $insert,
		?string $pk_name,
		string $table,
		string $primary_key
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// basic query
		$insert_query = "INSERT INTO " . $db->dbEscapeIdentifier($table)
			. " (uid) "
			. "VALUES ('A') " . $insert;
		$pk_name === null ?
			$db->dbExec($insert_query) :
			$db->dbExec($insert_query, $pk_name);
		// $db_get_returning = $db->dbGetReturning();
		$db_get_insert_pk_name = $db->dbGetInsertPKName();
		$db_get_insert_pk = $db->dbGetInsertPK();

		$read_query = "SELECT "
			. (!empty($primary_key) ?
				$db->dbEscapeIdentifier($primary_key) . ", " : ""
			)
			. "uid "
			. "FROM " . $db->dbEscapeIdentifier($table)
			. " WHERE uid = 'A'";
		$row = $db->dbReturnRow($read_query, true);
		$this->assertEquals(
			$row[$primary_key] ?? null,
			$db_get_insert_pk
		);
		$this->assertEquals(
			$primary_key,
			$db_get_insert_pk_name
		);
		$this->assertTrue(true);


		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - complex returning data checks
	//   dbGetReturningExt, dbGetReturningArray

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function returingProvider(): array
	{
		// NOTE that query can have multiple inserts
		// NOTE if there are different INSERTS before the primary keys will not match anymore
		$table_with_primary_key_id = 43;
		// 0: query + returning
		// 1: pk name for db exec
		// 2: key name/value or null (dbGetReturningExt)
		// 3: pos or null (dbGetReturningExt)
		// 4: matching return value (dbGetReturningExt)
		// 5: full returning array value (dbGetReturningArray)
		return [
			'single insert (PK)' => [
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, '2022-03-01') "
					. "RETURNING row_varchar, row_varchar_literal, row_int, row_date",
				null,
				null,
				null,
				[
					'row_varchar' => 'Text',
					'row_varchar_literal' => 'Other',
					'row_int' => 123,
					'row_date' => '2022-03-01',
					// 'table_with_primary_key_id' => "/^\d+$/",
					'table_with_primary_key_id' => $table_with_primary_key_id + 1,
				],
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
						// 'table_with_primary_key_id' => "/^\d+$/",
						'table_with_primary_key_id' => $table_with_primary_key_id + 1,
					]
				]
			],
			// double insert (PK)
			'dobule insert (PK)' => [
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, '2022-03-01'), "
					. "('Foxtrott', 'Tango', 789, '1982-10-15') "
					. "RETURNING row_varchar, row_varchar_literal, row_int, row_date",
				null,
				null,
				null,
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
						'table_with_primary_key_id' => $table_with_primary_key_id + 2,
					],
					1 => [
						'row_varchar' => 'Foxtrott',
						'row_varchar_literal' => 'Tango',
						'row_int' => 789,
						'row_date' => '1982-10-15',
						'table_with_primary_key_id' => $table_with_primary_key_id + 3,
					],
				],
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
						'table_with_primary_key_id' => $table_with_primary_key_id + 2,
					],
					1 => [
						'row_varchar' => 'Foxtrott',
						'row_varchar_literal' => 'Tango',
						'row_int' => 789,
						'row_date' => '1982-10-15',
						'table_with_primary_key_id' => $table_with_primary_key_id + 3,
					],
				]
			],
			// insert into table with no primary key
			'single insert (No PK)' => [
				"INSERT INTO table_without_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, '2022-03-01') "
					. "RETURNING row_varchar, row_varchar_literal, row_int, row_date",
				null,
				null,
				null,
				[
					'row_varchar' => 'Text',
					'row_varchar_literal' => 'Other',
					'row_int' => 123,
					'row_date' => '2022-03-01',
				],
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
					]
				]
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbGetReturningExt
	 * @covers ::dbGetReturningArray
	 * @dataProvider returingProvider
	 * @testdox Check returning cursor using $pk_name with $key and $pos [$_dataName]
	 *
	 * @param string $query
	 * @param string|null $pk_name
	 * @param string|null $key
	 * @param integer|null $pos
	 * @param array<mixed>|string|int|null $expected_ret_ext
	 * @param array $expected_ret_arr
	 * @return void
	 */
	public function testDbReturning(
		string $query,
		?string $pk_name,
		?string $key,
		?int $pos,
		$expected_ret_ext,
		array $expected_ret_arr
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// insert data
		$pk_name === null ?
			$db->dbExec($query) :
			$db->dbExec($query, $pk_name);

		// get the last value for PK and match that somehow

		$returning_ext = $db->dbGetReturningExt($key, $pos);
		$returning_arr = $db->dbGetReturningArray();

		$this->assertEquals(
			$expected_ret_ext,
			$returning_ext
		);
		$this->assertEquals(
			$expected_ret_arr,
			$returning_arr
		);

		// print "EXT: " . print_r($returning_ext, true) . "\n";
		// print "ARR: " . print_r($returning_arr, true) . "\n";

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - internal read data (post exec)
	//   dbGetNumRows, dbGetNumFields, dbGetFieldNames,
	//   dbGetQuery, dbGetQueryHash, dbGetDbh

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function getMethodsProvider(): array
	{
		// 0: run query
		// 1: optional insert query (if select or needed)
		// 2: optional compare query, if not set 0 is used
		// 3: num rows
		// 4: column count
		// 5: column names
		return [
			'select data' => [
				"SELECT row_varchar, row_varchar_literal, row_int, row_date "
					. "FROM table_with_primary_key",
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, '2022-03-01'), "
					. "('Foxtrott', 'Tango', 789, '1982-10-15') ",
				null,
				//
				2,
				4,
				['row_varchar', 'row_varchar_literal', 'row_int', 'row_date'],
			],
			// insert
			'insert data' => [
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_numeric, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, 1.0, '2022-03-01'), "
					. "('Foxtrott', 'Tango', 789, 2.2, '1982-10-15'), "
					. "('Schlamm', 'Beizinger', 100, 3.14, '1990-1-1') ",
				null,
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_numeric, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, 1.0, '2022-03-01'), "
					. "('Foxtrott', 'Tango', 789, 2.2, '1982-10-15'), "
					. "('Schlamm', 'Beizinger', 100, 3.14, '1990-1-1') "
					. " RETURNING table_with_primary_key_id",
				//
				3,
				0,
				[],
			],
			// update
			'update data' => [
				"UPDATE table_with_primary_key SET "
					. "row_varchar = 'CHANGE A', row_int = 999 "
					. "WHERE uid = 'A'",
				"INSERT INTO table_with_primary_key "
					. "(uid, row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('A', 'Text', 'Other', 123, '2022-03-01'), "
					. "('B', 'Foxtrott', 'Tango', 789, '1982-10-15') ",
				null,
				//
				1,
				0,
				[],
			]
			// something other (schema change?)
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbGetNumRows
	 * @covers ::dbGetNumFields
	 * @covers ::dbGetFieldNames
	 * @covers ::dbGetQuery
	 * @covers ::dbGetQueryHash
	 * @covers ::dbGetDbh
	 * @dataProvider getMethodsProvider
	 * @testdox Check check rows: $expected_rows and cols: $expected_cols [$_dataName]
	 *
	 * @param string $query
	 * @param string|null $insert_query
	 * @param string|null $compare_query
	 * @param integer $expected_rows
	 * @param integer $expected_cols
	 * @param array $expected_col_names
	 * @return void
	 */
	public function testDbGetMethods(
		string $query,
		?string $insert_query,
		?string $compare_query,
		int $expected_rows,
		int $expected_cols,
		array $expected_col_names
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		if (!empty($insert_query)) {
			$db->dbExec($insert_query);
		}

		$db->dbExec($query);

		$this->assertEquals(
			$compare_query ?? $query,
			$db->dbGetQuery()
		);
		$this->assertEquals(
			// perhaps move that somewhere else?
			\CoreLibs\Create\Hash::__hashLong($query),
			$db->dbGetQueryHash($query)
		);
		$this->assertEquals(
			$expected_rows,
			$db->dbGetNumRows()
		);
		$this->assertEquals(
			$expected_cols,
			$db->dbGetNumFields()
		);
		$this->assertEquals(
			$expected_col_names,
			$db->dbGetFieldNames()
		);
		$dbh = $db->dbGetDbh();
		if (\CoreLibs\Check\PhpVersion::checkPHPVersion('8.1')) {
			$this->assertIsObject(
				$dbh
			);
			// also check that this is correct instance type
			$this->assertInstanceOf(
				'PgSql\Connection',
				$dbh
			);
		} else {
			$this->assertIsResource(
				$dbh
			);
		}

		// if this is a select query, db dbReturn, dbReturnRow, dbReturnArray too
		if (preg_match("/^(select|show|with) /i", $query)) {
			// dbReturn
			$db->dbReturn($query);
			$this->assertEquals(
				$expected_rows,
				$db->dbGetNumRows()
			);
			$this->assertEquals(
				$expected_cols,
				$db->dbGetNumFields()
			);
			$this->assertEquals(
				$expected_col_names,
				$db->dbGetFieldNames()
			);
			// dbReturnRow
			// will return ALL rows there, but returns only the first
			$db->dbReturnRow($query);
			$this->assertEquals(
				$expected_rows,
				$db->dbGetNumRows()
			);
			$this->assertEquals(
				$expected_cols,
				$db->dbGetNumFields()
			);
			$this->assertEquals(
				$expected_col_names,
				$db->dbGetFieldNames()
			);
			// dbReturnArray
			$db->dbReturnArray($query);
			$this->assertEquals(
				$expected_rows,
				$db->dbGetNumRows()
			);
			$this->assertEquals(
				$expected_cols,
				$db->dbGetNumFields()
			);
			$this->assertEquals(
				$expected_col_names,
				$db->dbGetFieldNames()
			);
		}

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// TODO implement below checks
	// - complex write sets
	//   dbWriteData, dbWriteDataExt
	// - data debug
	//   dbDumpData

	// ASYNC at the end because it has 1s timeout
	// - asynchronous executions
	//   dbExecAsync, dbCheckAsync

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function asyncProvider(): array
	{
		// 0: query
		// 1: primary key
		// 2: exepected exec return
		// 3: warning
		// 4: error
		// 5: exepcted check return 1st
		// 6: final result
		// 7: warning
		// 8: error
		return [
			'run simple async query' => [
				"SELECT pg_sleep(1)",
				null,
				// exec result
				true,
				'',
				'',
				// check first
				true,
				// check final
				'result',
				'',
				''
			],
			// send query failed (E40)
			// result failed (E43)
			// no query running (E42)
			'no async query running' => [
				'',
				null,
				//
				false,
				'',
				'11',
				//
				false,
				//
				false,
				'',
				'42'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbExecAsync
	 * @covers ::dbCheckAsync
	 * @dataProvider asyncProvider
	 * @testdox async query $query with $expected_exec (warning $warning_exec/error $error_exec) and $expected_check/$expected_final (warning $warning_final/error $error_final) [$_dataName]
	 *
	 * @param string $query
	 * @param string|null $pk_name
	 * @param boolean $expected_exec
	 * @param string $warning_exec
	 * @param string $error_exec
	 * @param bool $expected_check
	 * @param bool|object|resource $expected_final
	 * @param string $warning_final
	 * @param string $error_final
	 * @return void
	 */
	public function testDbExecAsync(
		string $query,
		?string $pk_name,
		bool $expected_exec,
		string $warning_exec,
		string $error_exec,
		bool $expected_check,
		$expected_final,
		string $warning_final,
		string $error_final
	): void {
		// self::$log->setLogLevelAll('debug', true);
		// self::$log->setLogLevelAll('print', true);
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// exec the query
		$result_exec = $pk_name === null ?
			$db->dbExecAsync($query) :
			$db->dbExecAsync($query, $pk_name);
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		$this->assertEquals(
			$expected_exec,
			$result_exec
		);
		// error/warning check
		$this->assertEquals(
			$warning_exec,
			$last_warning,
		);
		$this->assertEquals(
			$error_exec,
			$last_error,
		);

		$run = 1;
		// first loop check
		while (($result_check = $db->dbCheckAsync()) === true) {
			if ($run == 1) {
				$this->assertEquals(
					$expected_check,
					$result_check
				);
			}
			$run++;
		}
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		// check after final
		if ($expected_final == 'result') {
			// post end check
			if (\CoreLibs\Check\PhpVersion::checkPHPVersion('8.1')) {
				$this->assertIsObject(
					$result_check
				);
				// also check that this is correct instance type
				$this->assertInstanceOf(
					'PgSql\Result',
					$result_check
				);
			} else {
				$this->assertIsResource(
					$result_check
				);
			}
		} else {
			// else compar check
			$this->assertEquals(
				$expected_final,
				$result_check
			);
		}
		// error/warning check
		$this->assertEquals(
			$warning_final,
			$last_warning,
		);
		$this->assertEquals(
			$error_final,
			$last_error,
		);

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}
}

// __END__
