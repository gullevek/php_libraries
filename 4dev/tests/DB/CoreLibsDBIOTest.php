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
row_primary_key	INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
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
use PHPUnit\Framework\MockObject\MockObject;
use CoreLibs\Logging;
use CoreLibs\DB\Options\Convert;
use CoreLibs\DB\Support\ConvertPlaceholder;

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
		self::$log = new Logging\Logging([
			// 'log_folder' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'log_file_id' => 'CoreLibs-DB-IO-Test',
		]);
		// will be true, default logging is true
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
			$db->dbExec("CREATE EXTENSION IF NOT EXISTS pgcrypto");
			$db->dbExec("DROP TABLE table_with_primary_key");
			$db->dbExec("DROP TABLE table_without_primary_key");
			$db->dbExec("DROP TABLE test_meta");
		}
		// uid is for internal reference tests
		$base_table = <<<SQL
			uid VARCHAR,
			row_int INT,
			row_numeric NUMERIC,
			row_varchar VARCHAR,
			row_varchar_literal VARCHAR,
			row_json JSON,
			row_jsonb JSONB,
			row_bytea BYTEA,
			row_timestamp TIMESTAMP WITHOUT TIME ZONE,
			row_date DATE,
			row_interval INTERVAL,
			row_array_int INT ARRAY,
			row_array_varchar VARCHAR ARRAY
		)
		WITHOUT OIDS
		SQL;
		// create the tables
		$db->dbExec(
			// primary key name is table + '_id'
			<<<SQL
			CREATE TABLE table_with_primary_key (
				table_with_primary_key_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
				$base_table
			SQL
		);
		$db->dbExec(
			<<<SQL
			CREATE TABLE table_without_primary_key (
				$base_table
			SQL
			/* "CREATE TABLE table_without_primary_key ("
			. $base_table */
		);
		// create simple table for meta test
		$db->dbExec(
			<<<SQL
			CREATE TABLE test_meta (
				row_1 VARCHAR,
				row_2 INT
			) WITHOUT OIDS
			SQL
			/* "CREATE TABLE test_meta ("
			. "row_1 VARCHAR, "
			. "row_2 INT"
			. ") WITHOUT OIDS" */
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

	/**
	 * For all Warning and Error checks in all tests
	 * DB Warning/Error checks
	 *
	 * @param  \CoreLibs\DB\IO $db
	 * @param  string          $warning
	 * @param  string          $error
	 * @return array
	 */
	private function subAssertErrorTest(
		\CoreLibs\DB\IO $db,
		string $warning,
		string $error
	): array {
		// get last error/warnings
		$last_warning = $db->dbGetLastWarning();
		$last_error = $db->dbGetLastError();
		// if string for warning or error is not empty check
		$this->assertEquals(
			$warning,
			$last_warning,
			'Assert query warning'
		);
		$this->assertEquals(
			$error,
			$last_error,
			'Assert query error'
		);
		return [$last_warning, $last_error];
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
		/** @var \CoreLibs\DB\IO&MockObject $db_io_mock */
		$db_io_mock = $this->createPartialMock(\CoreLibs\DB\IO::class, ['dbVersion']);
		$db_io_mock->method('dbVersion')->willReturn('13.6.0');

		$this->assertEquals(
			$expected,
			// $db->dbCompareVersion($input)
			$db_io_mock->dbCompareVersion($input)
		);
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
		// 2: exception name
		// 2: info string
		// 3: ???
		return [
			'invalid connection' => [
				self::$db_config['invalid'],
				false,
				'RuntimeException',
				"-DB-info-> Connected to db '' with schema 'public' as user "
					. "'' at host '' on port '5432' with ssl mode 'allow' **** "
					. "-DB-info-> DB IO Class debug output: Yes **** ",
				null,
			],
			'valid connection' => [
				self::$db_config['valid'],
				true,
				'',
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
	 * @param  array  $connection
	 * @param  bool   $expected_status
	 * @param  string   $exception
	 * @param  string $expected_string
	 * @return void
	 */
	public function testConnection(
		array $connection,
		bool $expected_status,
		string $exception,
		string $expected_string
	): void {
		if ($expected_status === false) {
			$this->expectException($exception);
		}
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
		// 0: db connecdtion
		// 1: override log flag, null for default
		// 2: set flag
		return [
			'default debug set' => [
				// what base connection
				'valid',
				// actions (set)
				null,
				// set exepected
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
	 * Test dbSetDbug, dbGetDebug
	 *
	 * @covers ::dbGetDbug
	 * @covers ::dbSetDebug
	 * @testdox Set and Get Debug flag
	 *
	 * @return void
	 */
	public function testDbSetDebug(): void
	{
		$connection = 'valid';
		// default set, expect true
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);
		$this->assertTrue(
			$db->dbGetDebug()
		);
		// switch off
		$db->dbSetDebug(false);
		$this->assertFalse(
			$db->dbGetDebug()
		);
		$db->dbClose();
		// second conenction with log set NOT debug
		$log = new Logging\Logging([
			// 'log_folder' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'log_file_id' => 'CoreLibs-DB-IO-Test',
			'log_level' => Logging\Logger\Level::Notice,
		]);
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			$log
		);
		$this->assertFalse(
			$db->dbGetDebug()
		);
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
		// if settings are all empty -> assume exception
		if (empty($settings['db_name']) && empty($settings['db_user'])) {
			$this->expectException('RuntimeException');
		}
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
			$db->dbEscapeBytea($input),
			'Assert error to bytea'
		);
		$this->assertEquals(
			$input,
			$db->dbUnescapeBytea($expected),
			'Assert error from bytes'
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
			// escape string, but set all empty strings to null ('' is null)
			'string value d' => ['string d', 'd', '\'string d\'',],
			'empty string value d' => ['', 'd', 'NULL',],
			'null string value d' => [null, 'd', 'NULL',],
			// escape literal string, but set all empty strings to null ('' is null)
			'string value literal d' => ['string d', 'dl', '\'string d\'',],
			'empty string value literal d' => ['', 'dl', 'NULL',],
			'null string value literal d' => [null, 'dl', 'NULL',],
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
	 * @param int|float|string|bool|null $input
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
						'is base' => true,
						'is composite' => false,
						'description' => '',
						'is pseudo' => false
					],
					'row_2' => [
						'num' => 2,
						'type' => 'int4',
						'len' => 4,
						'not null' => false,
						'has default' => false,
						'array dims' => 0,
						'is enum' => false,
						'is base' => true,
						'is composite' => false,
						'description' => '',
						'is pseudo' => false
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
						'is base' => true,
						'is composite' => false,
						'description' => '',
						'is pseudo' => false
					],
					'row_2' => [
						'num' => 2,
						'type' => 'int4',
						'len' => 4,
						'not null' => false,
						'has default' => false,
						'array dims' => 0,
						'is enum' => false,
						'is base' => true,
						'is composite' => false,
						'description' => '',
						'is pseudo' => false
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
	//   dbExec, dbExecParams, dbResetQueryCalled, dbGetQueryCalled

	/**
	 * provide queries with return results
	 *
	 * @return array
	 */
	public function queryDbExecProvider(): array
	{
		// 0: query
		// 1: params, null for force dbEXec
		// 2: optional primary key name, null for empty test
		// 3: expectes result (bool, object)
		// 4: warning
		// 5: error
		// 6: run times, not set is once, true is max + 1
		return [
			// insert
			'table with pk insert' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				null,
				'',
				'object',
				'',
				'',
			],
			// insert but null primary key
			'table with pk insert null' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				null,
				null,
				'object',
				'',
				'',
			],
			// insert with params
			'table with pk insert params' => [
				'INSERT INTO table_with_primary_key (row_varchar) VALUES ($1)',
				['test'],
				'',
				'object',
				'',
				'',
			],
			// insert with params, null primary key
			'table with pk insert params' => [
				'INSERT INTO table_with_primary_key (row_varchar) VALUES ($1)',
				['test'],
				null,
				'object',
				'',
				'',
			],
			// insert to table with no pk (31?)
			'table with no pk insert' => [
				'INSERT INTO table_without_primary_key (row_date) VALUES (NOW())',
				null,
				'',
				'object',
				'',
				'',
			],
			// insert to table with no pk (31?) with params
			'table with no pk insert params' => [
				'INSERT INTO table_without_primary_key (row_varchar) VALUES ($1)',
				['test'],
				null,
				'object',
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
					null,
				'',
				'object',
				'32',
				'',
			],
			// Skip PK READING
			'table with pk insert and NULL pk name' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				null,
				'NULL',
				'object',
				'',
				'',
			],
			// insert with pk set
			'table with pk insert and pk name' => [
				'INSERT INTO table_with_primary_key (row_date) VALUES (NOW())',
				null,
				'table_with_primary_key_id',
				'object',
				'',
				'',
			],
			// update
			'table with pk update' => [
				'UPDATE table_with_primary_key SET row_date = NOW()',
				null,
				'',
				'object',
				'',
				'',
			],
			'table with pk select' => [
				'SELECT * FROM table_with_primary_key',
				null,
				'',
				'object',
				'',
				'',
			],
			// no query set, error 11
			'no query set' => [
				'',
				null,
				'',
				false,
				'',
				'11',
			],
			// wrong params coutn for insert
			'wrong params count' => [
				'INSERT INTO table_with_primary_key (row_varchar) VALUES ($1, $2)',
				['test'],
				null,
				false,
				'',
				'23'
			],
			'wrong params count, null params' => [
				'INSERT INTO table_with_primary_key (row_varchar) VALUES ($1, $2)',
				null,
				null,
				false,
				'',
				'23'
			],
			// no db connection setable (16) [needs Mocking]
			// TODO failed db connection
			// connection busy [async] (41)
			// TODO connection busy
			// same query run too many times (30)
			'same query run too many times' => [
				'SELECT row_date FROM table_with_primary_key',
				null,
				'',
				'object',
				'',
				'30',
				true,
			],
			// execution failed (13)
			'invalid query' => [
				'INVALID',
				null,
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
				null,
				'',
				false,
				'',
				'13'
			],
		];
	}

	/**
	 * pure dbExec/dbExecParams checker
	 * does not check __dbPostExec run, this will be done in the dbGet* functions
	 * tests (internal read data post exec group)
	 *
	 * @covers ::dbExec
	 * @covers ::dbExecParams
	 * @covers ::dbGetQueryCalled
	 * @covers ::dbResetQueryCalled
	 * @dataProvider queryDbExecProvider
	 * @testdox dbExec $query and pk $pk_name with $expected_return (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param array<mixed>|null $params
	 * @param string|null $pk_name
	 * @param object|bool $expected_return
	 * @param string $warning
	 * @param string $error
	 * @param bool $run_many_times
	 * @return void
	 */
	public function testDbExec(
		string $query,
		?array $params,
		?string $pk_name,
		$expected_return,
		string $warning,
		string $error,
		bool $run_many_times = false
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// clear any current query
		// $db->dbResetQuery();

		// assert never called query is 0
		$this->assertEquals(
			0,
			$db->dbGetQueryCalled($query),
			'Assert never called query is null'
		);

		// if expected result is not a bool
		// it has to be an object type PgSql\Result

		if (is_bool($expected_return)) {
			// supress ANY errors here
			if ($pk_name === null && $params === null) {
				$result = @$db->dbExec($query);
			} elseif ($params === null) {
				$result = @$db->dbExec($query, $pk_name);
			} elseif ($pk_name === null) {
				$result = @$db->dbExecParams($query, $params);
			} else {
				$result = @$db->dbExecParams($query, $params, $pk_name);
			}
			$this->assertEquals(
				$expected_return,
				$result
			);
		} else {
			if ($pk_name === null && $params === null) {
				$result = $db->dbExec($query);
			} elseif ($params === null) {
				$result = $db->dbExec($query, $pk_name);
			} elseif ($pk_name === null) {
				$result = $db->dbExecParams($query, $params);
			} else {
				$result = $db->dbExecParams($query, $params, $pk_name);
			}
			// if PHP or newer, must be Object PgSql\Result
			$this->assertIsObject(
				$result
			);
			// also check that this is correct instance type
			$this->assertInstanceOf(
				'PgSql\Result',
				$result
			);
		}
		// if we have more than one run time
		// re-run same query and then catch error
		if ($run_many_times) {
			for ($i = 1; $i <= $db->dbGetMaxQueryCall() + 1; $i++) {
				if ($pk_name === null && $params === null) {
					$db->dbExec($query);
				} elseif ($params === null) {
					$db->dbExec($query, $pk_name);
				} elseif ($pk_name === null) {
					$db->dbExecParams($query, $params);
				} else {
					$db->dbExecParams($query, $params, $pk_name);
				}
			}
			if ($pk_name === null && $params === null) {
				$result = $db->dbExec($query);
			} elseif ($params === null) {
				$result = $db->dbExec($query, $pk_name);
			} elseif ($pk_name === null) {
				$result = $db->dbExecParams($query, $params);
			} else {
				$result = $db->dbExecParams($query, $params, $pk_name);
			}
			// will fail now
			$this->assertFalse(
				$result
			);
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
		$this->subAssertErrorTest($db, $warning, $error);

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
		$read_query_params = "SELECT row_int, uid FROM table_with_primary_key WHERE uid = $1";
		// 0: query
		// 1: params (null) for other
		// 2: flag (assoc)
		// 3: result
		// 4: warning
		// 5: error
		// 6: insert query
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
				'',
				'',
				$insert_query,
			],
			'valid select, assoc only false' => [
				$read_query,
				null,
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
				null,
				true,
				[
					'row_int' => 1,
					'uid' => 'A',
				],
				'',
				'',
				$insert_query,
			],
			// params read
			'valid select, params' => [
				$read_query_params,
				[
					'A'
				],
				true,
				[
					'row_int' => 1,
					'uid' => 'A',
				],
				'',
				'',
				$insert_query
			],
			// errors
			'wrong params count' => [
				$read_query_params,
				[],
				true,
				false,
				'',
				'23',
				$insert_query
			],
			// params, wrong count
			'empty select' => [
				'',
				null,
				null,
				false,
				'',
				'11',
				$insert_query,
			],
			'insert query' => [
				$insert_query,
				null,
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
	 * @covers ::dbReturnRowParams
	 * @dataProvider returnRowProvider
	 * @testdox dbReturnRow $query and assoc $flag_assoc with $expected (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param array<mixed>|null $params
	 * @param bool|null $flag_assoc
	 * @param array<mixed>|bool $expected
	 * @param string $warning
	 * @param string $error
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbReturnRow(
		string $query,
		?array $params,
		?bool $flag_assoc,
		$expected,
		string $warning,
		string $error,
		string $insert_data
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		$db->dbExec($insert_data);
		// run
		if ($flag_assoc === null && $params === null) {
			$result = $db->dbReturnRow($query);
		} elseif ($params === null) {
			$result = $db->dbReturnRow($query, $flag_assoc);
		} elseif ($flag_assoc === null) {
			$result = $db->dbReturnRowParams($query, $params);
		} else {
			$result = $db->dbReturnRowParams($query, $params, $flag_assoc);
		}
		// compare
		$this->assertEquals(
			$expected,
			$result
		);
		// get last error/warnings
		// if string for warning or error is not empty check
		$this->subAssertErrorTest($db, $warning, $error);

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
		$read_query_params = "SELECT row_int, uid FROM table_with_primary_key WHERE uid = $1";
		// 0: query
		// 1: params (null) for other
		// 2: flag (assoc)
		// 3: result
		// 4: warning
		// 5: error
		// 6: insert query
		return [
			'valid select' => [
				$read_query,
				null,
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
				null,
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
			// params read
			'valid select, params' => [
				$read_query_params,
				[
					'A'
				],
				true,
				[
					[
						'row_int' => 1,
						'uid' => 'A',
					]
				],
				'',
				'',
				$insert_query
			],
			// errors
			'wrong params count' => [
				$read_query_params,
				[],
				true,
				false,
				'',
				'23',
				$insert_query
			],
			'empty select' => [
				'',
				null,
				null,
				false,
				'',
				'11',
				$insert_query,
			],
			'insert query' => [
				$insert_query,
				null,
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
	 * @param array<mixed>|null $params
	 * @param boolean|null $flag_assoc
	 * @param array<mixed>|bool $expected
	 * @param string $warning
	 * @param string $error
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbReturnArray(
		string $query,
		?array $params,
		?bool $flag_assoc,
		$expected,
		string $warning,
		string $error,
		string $insert_data
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		$db->dbExec($insert_data);
		// run
		if ($flag_assoc === null && $params === null) {
			$result = $db->dbReturnArray($query);
		} elseif ($params === null) {
			$result = $db->dbReturnArray($query, $flag_assoc);
		} elseif ($flag_assoc === null) {
			$result = $db->dbReturnArrayParams($query, $params);
		} else {
			$result = $db->dbReturnArrayParams($query, $params, $flag_assoc);
		}
		// compare
		$this->assertEquals(
			$expected,
			$result
		);
		// get last error/warnings
		// if string for warning or error is not empty check
		$this->subAssertErrorTest($db, $warning, $error);

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
		$read_query_params = "SELECT row_int, uid FROM table_with_primary_key WHERE uid = $1";
		$row_a = [
			'row_int' => 1,
			0 => 1,
			'uid' => 'A',
			1 => 'A'
		];
		$row_a_assoc = [
			'row_int' => 1,
			'uid' => 'A',
		];
		$row_b = [
			'row_int' => 2,
			0 => 2,
			'uid' => 'B',
			1 => 'B'
		];
		$row_b_assoc = [
			'row_int' => 2,
			'uid' => 'B',
		];
		// 0: read query
		// 1: params, null for not used
		// 2: reset flag, null for default
		// 3: assoc flag, null for default
		// 4: expected return (cursor_ext/data)
		// 5: step through, or normal loop read
		// 6: cursor ext compare array
		// 7*: first only, extended cursor (for each step) [not implemented yet]
		// 8: warning
		// 9: error
		// 10: insert data
		return [
			// *** READ STEP BY STEP
			// default cache: USE_CACHE
			'valid select, default cache settings (NO_CACHE)' => [
				// 0-3
				$read_query,
				null,
				null,
				null,
				// 4
				$row_a,
				// 5
				true,
				// 6
				// check cursor_ext
				[
					'cursor' => 'PgSql\Result',
					'data' => [],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 1,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 1,
					'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
					'assoc_flag' => false,
					'cached' => false,
					'finished' => false,
					'read_finished' => false,
					'db_read_finished' => false,
				],
				// 7
				// extended cursor per step (first only true)
				[
					// second row
					[
						'data' => [
							0 => $row_b,
						],
						'cursor' => [
							'cursor' => 'PgSql\Result',
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 2,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
							'assoc_flag' => false,
							'cached' => false,
							'finished' => false,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					],
					// end row, false
					[
						'data' => false,
						'cursor' => [
							'cursor' => 1,
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 0,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
							'assoc_flag' => false,
							'cached' => false,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					]
				],
				// warn/error
				'',
				'',
				// insert
				$insert_query
			],
			'valid select, use cache, assoc only' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::USE_CACHE,
				true,
				$row_a_assoc,
				true,
				// is same as default
				[
					'cursor' => 'PgSql\Result',
					'data' => [
						0 => $row_a_assoc,
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 1,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 1,
					'cache_flag' => \CoreLibs\DB\IO::USE_CACHE,
					'assoc_flag' => true,
					'cached' => true,
					'finished' => false,
					'read_finished' => false,
					'db_read_finished' => false,
				],
				[
					// second row
					[
						'data' => [
							0 => $row_b_assoc,
						],
						'cursor' => [
							'cursor' => 'PgSql\Result',
							'data' => [
								0 => $row_a_assoc,
								1 => $row_b_assoc,
							],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 2,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::USE_CACHE,
							'assoc_flag' => true,
							'cached' => true,
							'finished' => false,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					],
					// end row, false
					[
						'data' => false,
						'cursor' => [
							'cursor' => 1,
							'data' => [
								0 => $row_a_assoc,
								1 => $row_b_assoc,
							],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 0,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::USE_CACHE,
							'assoc_flag' => true,
							'cached' => true,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					]
				],
				'',
				'',
				$insert_query
			],
			'valid select, read new, assoc only' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::READ_NEW,
				true,
				$row_a_assoc,
				true,
				[
					'cursor' => 'PgSql\Result',
					'data' => [
						0 => $row_a_assoc,
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 1,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 1,
					'cache_flag' => \CoreLibs\DB\IO::READ_NEW,
					'assoc_flag' => true,
					'cached' => true,
					'finished' => false,
					'read_finished' => false,
					'db_read_finished' => false,
				],
				[
					// second row
					[
						'data' => [
							0 => $row_b_assoc,
						],
						'cursor' => [
							'cursor' => 'PgSql\Result',
							'data' => [
								0 => $row_a_assoc,
								1 => $row_b_assoc,
							],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 2,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::READ_NEW,
							'assoc_flag' => true,
							'cached' => true,
							'finished' => false,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					],
					// end row, false
					[
						'data' => false,
						'cursor' => [
							'cursor' => 1,
							'data' => [
								0 => $row_a_assoc,
								1 => $row_b_assoc,
							],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 0,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::READ_NEW,
							'assoc_flag' => true,
							'cached' => true,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					]
				],
				'',
				'',
				$insert_query
			],
			'valid select, clear cache, assoc only' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::CLEAR_CACHE,
				true,
				$row_a_assoc,
				true,
				[
					'cursor' => 'PgSql\Result',
					'data' => [
						0 => $row_a_assoc,
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 1,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 1,
					'cache_flag' => \CoreLibs\DB\IO::CLEAR_CACHE,
					'assoc_flag' => true,
					'cached' => true,
					'finished' => false,
					'read_finished' => false,
					'db_read_finished' => false,
				],
				[
					// second row
					[
						'data' => [
							0 => $row_b_assoc,
						],
						'cursor' => [
							'cursor' => 'PgSql\Result',
							'data' => [
								0 => $row_a_assoc,
								1 => $row_b_assoc,
							],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 2,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::CLEAR_CACHE,
							'assoc_flag' => true,
							'cached' => true,
							'finished' => false,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					],
					// end row, false
					[
						'data' => false,
						'cursor' => [
							'cursor' => 1,
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 0,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::CLEAR_CACHE,
							'assoc_flag' => true,
							'cached' => false,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					]
				],
				'',
				'',
				$insert_query
			],
			'valid select, no cache, assoc only' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::NO_CACHE,
				true,
				$row_a_assoc,
				true,
				[
					'cursor' => 'PgSql\Result',
					'data' => [],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 1,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 1,
					'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
					'assoc_flag' => true,
					'cached' => false,
					'finished' => false,
					'read_finished' => false,
					'db_read_finished' => false,
				],
				[
					// second row
					[
						'data' => [
							0 => $row_b_assoc,
						],
						'cursor' => [
							'cursor' => 'PgSql\Result',
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 2,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
							'assoc_flag' => true,
							'cached' => false,
							'finished' => false,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					],
					// end row, false
					[
						'data' => false,
						'cursor' => [
							'cursor' => 1,
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 2,
							'pos' => 0,
							'query' => $read_query,
							'params' => [],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
							'assoc_flag' => true,
							'cached' => false,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					]
				],
				'',
				'',
				$insert_query
			],
			'valid select params, no cache, assoc only' => [
				// 0-3
				$read_query_params,
				['A'],
				\CoreLibs\DB\IO::NO_CACHE,
				true,
				// 4
				$row_a_assoc,
				// 5
				true,
				// 6 cursor
				[
					'cursor' => 'PgSql\Result',
					'data' => [],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 1,
					'pos' => 1,
					'query' => $read_query_params,
					'params' => ['A'],
					'read_rows' => 1,
					'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
					'assoc_flag' => true,
					'cached' => false,
					'finished' => false,
					'read_finished' => true,
					'db_read_finished' => true,
				],
				// 7 extended cursor
				[
					// second row/last row
					[
						'data' => [
							0 => $row_b_assoc,
						],
						'cursor' => [
							'cursor' => 'PgSql\Result',
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 1,
							'pos' => 2,
							'query' => $read_query_params,
							'params' => ['A'],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
							'assoc_flag' => true,
							'cached' => false,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					],
					// end row, false
					[
						'data' => false,
						'cursor' => [
							'cursor' => 1,
							'data' => [],
							'field_names' => [
								'row_int',
								'uid'
							],
							'field_types' => [
								'int4',
								'varchar'
							],
							'num_fields' => 2,
							'num_rows' => 1,
							'pos' => 0,
							'query' => $read_query_params,
							'params' => ['A'],
							'read_rows' => 2,
							'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
							'assoc_flag' => true,
							'cached' => false,
							'finished' => true,
							'read_finished' => true,
							'db_read_finished' => true,
						]
					]
				],
				// 8-10
				'',
				'',
				$insert_query
			],
			// *** READ STEP BY STEP, ERROR TRIGGER
			'empty select error' => [
				// 0-3
				'',
				null,
				null,
				null,
				// 4
				false,
				// 5
				true,
				// 6
				[],
				// 7
				[],
				// 8-10
				'',
				'11',
				$insert_query,
			],
			'params count wrong' => [
				// 0-3
				$read_query_params,
				[],
				null,
				null,
				// 4
				false,
				// 5
				true,
				// 6
				[],
				// 7
				[],
				// 8-10
				'',
				'23',
				$insert_query,
			],
			'insert query error' => [
				$insert_query,
				null,
				null,
				null,
				false,
				true,
				[],
				[],
				'',
				'17',
				$insert_query
			],
			// *** READ AS LOOP
			// from here on a complex read all full tests
			'valid select, full read, default cache settings (NO CACHE)' => [
				$read_query,
				null,
				null,
				null,
				[$row_a, $row_b,],
				false,
				[
					'cursor' => 1,
					'data' => [],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 0,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 2,
					'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
					'assoc_flag' => false,
					'cached' => false,
					'finished' => true,
					'read_finished' => true,
					'db_read_finished' => true,
				],
				[],
				'',
				'',
				$insert_query
			],
			// USE CACHE
			'valid select, full read, USE CACHE' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::USE_CACHE,
				null,
				[$row_a, $row_b,],
				false,
				[
					'cursor' => 1,
					'data' => [
						0 => $row_a,
						1 => $row_b,
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 0,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 2,
					'cache_flag' => \CoreLibs\DB\IO::USE_CACHE,
					'assoc_flag' => false,
					'cached' => true,
					'finished' => true,
					'read_finished' => true,
					'db_read_finished' => true,
				],
				[],
				'',
				'',
				$insert_query
			],
			// READ_NEW
			'valid select, full read, READ NEW' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::READ_NEW,
				null,
				[$row_a, $row_b,],
				false,
				[
					'cursor' => 1,
					'data' => [
						0 => $row_a,
						1 => $row_b,
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 0,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 2,
					'cache_flag' => \CoreLibs\DB\IO::READ_NEW,
					'assoc_flag' => false,
					'cached' => true,
					'finished' => true,
					'read_finished' => true,
					'db_read_finished' => true,
				],
				[],
				'',
				'',
				$insert_query
			],
			// CLEAR_CACHE
			'valid select, full read, CLEAR CACHE' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::CLEAR_CACHE,
				null,
				[$row_a, $row_b,],
				false,
				[
					'cursor' => 1,
					'data' => [
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 0,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 2,
					'cache_flag' => \CoreLibs\DB\IO::CLEAR_CACHE,
					'assoc_flag' => false,
					'cached' => false,
					'finished' => true,
					'read_finished' => true,
					'db_read_finished' => true,
				],
				[],
				'',
				'',
				$insert_query
			],
			'valid select, full read, NO CACHE' => [
				$read_query,
				null,
				\CoreLibs\DB\IO::NO_CACHE,
				null,
				[$row_a, $row_b,],
				false,
				[
					'cursor' => 1,
					'data' => [
					],
					'field_names' => [
						'row_int',
						'uid'
					],
					'field_types' => [
						'int4',
						'varchar'
					],
					'num_fields' => 2,
					'num_rows' => 2,
					'pos' => 0,
					'query' => $read_query,
					'params' => [],
					'read_rows' => 2,
					'cache_flag' => \CoreLibs\DB\IO::NO_CACHE,
					'assoc_flag' => false,
					'cached' => false,
					'finished' => true,
					'read_finished' => true,
					'db_read_finished' => true,
				],
				[],
				'',
				'',
				$insert_query
			],
		];
	}

	/**
	 * dbReturn cursor extended checks
	 *
	 * @param  \CoreLibs\DB\IO   $db
	 * @param  string            $query
	 * @param  array<mixed>|null $params
	 * @param  array             $cursor_ext_checks
	 * @return void
	 */
	private function subAssertCursorExtTestDbReturnFunction(
		\CoreLibs\DB\IO $db,
		string $query,
		?array $params,
		array $cursor_ext_checks
	): void {
		// cursor check
		if (
			empty($db->dbGetLastWarning()) &&
			empty($db->dbGetLastError()) &&
			count($cursor_ext_checks)
		) {
			if ($params === null) {
				$cursor_ext = $db->dbGetCursorExt($query);
			} else {
				$cursor_ext = $db->dbGetCursorExt($query, $params);
			}
			foreach ($cursor_ext_checks as $key => $expected) {
				if ($key != 'cursor') {
					$this->assertEquals(
						$expected,
						$cursor_ext[$key],
						'assert equal cursor ext for ' . $key
					);
				} else {
					// for int, it has to be one
					// else depends on PHP version, either object or REsource
					if (is_int($expected)) {
						$this->assertEquals(
							1,
							$cursor_ext[$key],
							'assert equal cursor ext cursor int 1'
						);
					} else {
						$this->assertIsObject(
							$cursor_ext[$key],
							'assert is object cursor ext cursor'
						);
						// also check that this is correct instance type
						$this->assertInstanceOf(
							$expected,
							$cursor_ext[$key],
							'assert is instance of cursor ext cursor'
						);
					}
				}
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param  \CoreLibs\DB\IO    $db
	 * @param  string             $query
	 * @param  array<mixed>|null  $params
	 * @param  int|null           $flag_cache
	 * @param  bool|null          $flag_assoc
	 * @return array<mixed>|false
	 */
	private function subDbReturnCall(
		\CoreLibs\DB\IO $db,
		string $query,
		?array $params,
		?int $flag_cache,
		?bool $flag_assoc,
	): array|false {
		if ($flag_cache === null && $flag_assoc === null && $params === null) {
			$result = $db->dbReturn($query);
		} elseif ($flag_assoc === null && $params === null) {
			$result = $db->dbReturn($query, $flag_cache);
		} elseif ($params === null) {
			$result = $db->dbReturn($query, $flag_cache, $flag_assoc);
		} elseif ($flag_cache === null && $flag_assoc === null) {
			$result = $db->dbReturnParams($query, $params);
		} elseif ($flag_assoc === null) {
			$result = $db->dbReturnParams($query, $params, $flag_cache);
		} else {
			$result = $db->dbReturnParams($query, $params, $flag_cache, $flag_assoc);
		}
		return $result;
	}

	/**
	 * dbReturn Function Test
	 *
	 * @covers ::dbReturn
	 * @covers ::dbReturnParams
	 * @covers ::dbCacheReset
	 * @covers ::dbGetCursorExt
	 * @covers ::dbCursorPos
	 * @covers ::dbCursorNumRows
	 * @dataProvider dbReturnProvider
	 * @testdox dbReturn Read First $read_first_only only and cache $flag_cache and assoc $flag_assoc with (Warning: $warning/Error: $error) [$_dataName]
	 *
	 * @param string $query
	 * @param array<mixed>|null $params
	 * @param integer|null $flag_cache
	 * @param boolean|null $flag_assoc
	 * @param array<mixed>|bool $expected
	 * @param bool $read_first_only
	 * @param array<mixed> $cursor_ext_checks
	 * @param array<mixed> $cursor_ext_checks_step
	 * @param string $warning
	 * @param string $error
	 * @param string $insert_data
	 * @return void
	 */
	public function testDbReturnFunction(
		string $query,
		?array $params,
		?int $flag_cache,
		?bool $flag_assoc,
		$expected,
		bool $read_first_only,
		array $cursor_ext_checks,
		array $cursor_ext_checks_step,
		string $warning,
		string $error,
		string $insert_data
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		// insert data before we can test, from expected array
		$db->dbExec($insert_data);

		// all checks below
		if ($read_first_only === true) {
			// run query
			$result = $this->subDbReturnCall(
				$db,
				$query,
				$params,
				$flag_cache,
				$flag_assoc
			);
			// simple assert first read, then discard result
			// compare data
			$this->assertEquals(
				$expected,
				$result,
				'Assert dbReturn first only equal'
			);
			$this->subAssertErrorTest($db, $warning, $error);
			$this->subAssertCursorExtTestDbReturnFunction(
				$db,
				$query,
				$params,
				$cursor_ext_checks
			);
			// extended checks. read until end of data and check per steck cursor data
			foreach ($cursor_ext_checks_step as $_cursor_ext_checks) {
				// each step matches a read step
			}
		} else {
			// read all, and then do result compare
			// do cache reset test
			$data = [];
			$pos = 0;
			while (
				is_array(
					$res = $this->subDbReturnCall(
						$db,
						$query,
						$params,
						$flag_cache,
						$flag_assoc
					)
				)
			) {
				$data[] = $res;
				$pos++;
				// check cursor pos
				$this->assertEquals(
					$pos,
					($params === null ?
						$db->dbGetCursorPos($query) :
						$db->dbGetCursorPos($query, $params)
					),
					'Assert dbReturn pos'
				);
			}
			// does count match for returned data and the cursor num rows
			$this->assertEquals(
				count($data),
				($params === null ?
					$db->dbGetCursorNumRows($query) :
					$db->dbGetCursorNumRows($query, $params)
				),
				'Assert dbReturn num rows'
			);
			// run cursor ext checks after first run
			$this->subAssertCursorExtTestDbReturnFunction(
				$db,
				$query,
				$params,
				$cursor_ext_checks
			);
			// does data match
			// try get cursor data for non existing, must be null
			$this->assertNull(
				$db->dbGetCursorExt($query, [], 'nonexistingfield')
			);
			// does reset data work, query cursor must be null
			if ($params === null) {
				$db->dbCacheReset($query);
			} else {
				$db->dbCacheReset($query, $params);
			}
			$this->assertNull(
				($params === null ?
					$db->dbGetCursorExt($query) :
					$db->dbGetCursorExt($query, $params)
				),
				'Assert dbReturn reset cache'
			);
			// New run after reset
			// for read all, test that two reads result in same data
			$data = [];
			$pos = 0;
			while (
				is_array(
					$res = $this->subDbReturnCall(
						$db,
						$query,
						$params,
						$flag_cache,
						$flag_assoc
					)
				)
			) {
				$data[] = $res;
				$pos++;
				// check cursor pos
				$this->assertEquals(
					$pos,
					($params === null ?
						$db->dbGetCursorPos($query) :
						$db->dbGetCursorPos($query, $params)
					),
					'Assert dbReturn double read 1 pos: ' . $flag_cache
				);
			}
			$this->subAssertCursorExtTestDbReturnFunction(
				$db,
				$query,
				$params,
				$cursor_ext_checks
			);
			$data_second = [];
			$pos_second = 0;
			while (
				is_array(
					$res = $this->subDbReturnCall(
						$db,
						$query,
						$params,
						$flag_cache,
						$flag_assoc
					)
				)
			) {
				$data_second[] = $res;
				$pos_second++;
				// check cursor pos
				$this->assertEquals(
					$pos_second,
					($params === null ?
						$db->dbGetCursorPos($query) :
						$db->dbGetCursorPos($query, $params)
					),
					'Assert dbReturn double read 2 pos: ' . $flag_cache
				);
			}
			$this->assertEquals(
				$data,
				$data_second,
				'Assert first and second run are equal: return data'
			);
			$this->assertEquals(
				$pos,
				$pos_second,
				'Assert first and second run are equal: count'
			);
			// run cursor ext checks after second run
			$this->subAssertCursorExtTestDbReturnFunction(
				$db,
				$query,
				$params,
				$cursor_ext_checks
			);
		}

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// - prepared query execute
	//   dbPrepare, dbExecute, dbFetchArray, dbGetPrepareCursorValue

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
		// 13: prepated cursor array data match values
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
				// get prepared data
				[
					'pk_name' => 'table_with_primary_key_id',
					'count' => 2,
					'query' =>  'INSERT INTO table_with_primary_key (row_int, uid) '
						. 'VALUES ($1, $2) RETURNING table_with_primary_key_id',
					'returning_id' => true,
					'placeholder_converted' => [],
				],
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
				//
				[
					'pk_name' => '',
					'count' => 3,
					'query' => 'UPDATE table_with_primary_key SET row_int = $1, '
						. 'row_varchar = $2 WHERE uid = $3',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
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
				$insert_query,
				//
				[
					'pk_name' => '',
					'count' => 1,
					'query' => 'SELECT row_int, uid FROM table_with_primary_key WHERE uid = $1',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
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
				$insert_query,
				//
				[
					'pk_name' => '',
					'count' => 0,
					'query' => 'SELECT row_int, uid FROM table_with_primary_key',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
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
				//
				[
					'pk_name' => '',
					'count' => 0,
					'query' => '',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
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
				//
				[
					'pk_name' => '',
					'count' => 0,
					'query' => '',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
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
				// warning: 20
				true, '20', '',
				//
				'result', '', '',
				// no query but data for data only compare
				'',
				[],
				//
				$insert_query,
				//
				[
					'pk_name' => '',
					'count' => 0,
					'query' => 'SELECT row_int, uid FROM table_with_primary_key',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
			],
			// prepare with different statement name
			'prepare query with same statement name, different query' => [
				'double_error',
				$read_query,
				// primary key
				null,
				// arguments (none)
				null,
				// expected return false, warning: no, error: 26
				false, '', '26',
				// return expected, warning, error
				'', '', '',
				// dummy query for second prepare with wrong query
				$read_query . ' WHERE uid = $3',
				[],
				//
				$insert_query,
				//
				[
					'pk_name' => '',
					'count' => 0,
					'query' => 'SELECT row_int, uid FROM table_with_primary_key',
					'returning_id' => false,
					'placeholder_converted' => [],
				],
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
				'',
				//
				[
					'pk_name' => 'table_with_primary_key_id',
					'count' => 2,
					'query' => 'INSERT INTO table_with_primary_key (row_int, uid) VALUES '
						. '($1, $2) RETURNING table_with_primary_key_id',
					'returning_id' => true,
					'placeholder_converted' => [],
				],
			],
			// execute does not return a result (22)
			// TODO execute does not return a result
			// TODO prepared statement with placeholder params auto convert
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbPrepare
	 * @covers ::dbExecute
	 * @covers ::dbFetchArray
	 * @covers ::dbGetPrepareCursorValue
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
	 * @param array $prepare_cursor
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
		string $insert_data,
		array $prepare_cursor,
	): void {
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
		if ($error_prepare == '26') {
			$prepare_result = $db->dbPrepare($stm_name, $expected_data_query);
		}
		// if result type, or if forced bool
		if (is_string($expected_prepare) && $expected_prepare == 'result') {
			// if PHP or newer, must be Object PgSql\Result
			$this->assertIsObject(
				$prepare_result
			);
			// also check that this is correct instance type
			$this->assertInstanceOf(
				'PgSql\Result',
				$prepare_result
			);
		} else {
			$this->assertEquals(
				$expected_prepare,
				$prepare_result
			);
		}
		// error/warning check
		$this->subAssertErrorTest($db, $warning_prepare, $error_prepare);

		// for non fail prepare test exec
		// check test result
		if (!$error_prepare) {
			$execute_result = $query_data === null ?
				$db->dbExecute($stm_name) :
				$db->dbExecute($stm_name, $query_data);
			if ($expected_execute == 'result') {
				// if PHP or newer, must be Object PgSql\Result
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
				$this->assertEquals(
					$expected_execute,
					$execute_result
				);
			}
			// error/warning check
			$this->subAssertErrorTest($db, $warning_execute, $error_execute);
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

			// check dbGetPrepareCursorValue
			foreach (['pk_name', 'count', 'query', 'returning_id', 'placeholder_converted'] as $key) {
				$this->assertEquals(
					$prepare_cursor[$key],
					$db->dbGetPrepareCursorValue($stm_name, $key),
					'Prepared cursor: ' . $key . ': failed assertion'
				);
			}
		}

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// dedicated error checks for prepare cursor return

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerDbGetPrepareCursorValue(): array
	{
		// 1: query (can be empty for do not set)
		// 2: stm name
		// 3: key
		// 4: expected error return
		return [
			'no error' => [
				"SELECT row_int, uid FROM table_with_primary_key",
				'read',
				'pk_name',
				''
			],
			'statement name empty' => [
				'',
				'',
				'',
				'101',
			],
			'key empty' => [
				'',
				'read',
				'',
				'102',
			],
			'key invalid' => [
				'',
				'read',
				'invalid',
				'102',
			],
			'statement name not found' => [
				'',
				'invalid',
				'pk_name',
				'103',
			],
		];
	}

	/**
	 * test return prepare cursor errors
	 *
	 * @covers ::dbGetPrepareCursorValue
	 * @dataProvider providerDbGetPrepareCursorValue
	 * @testdox prepared query $stm_name with $key expect error id $error_id [$_dataName]
	 *
	 * @param string $query
	 * @param string $stm_name
	 * @param string $key
	 * @param string $error_id
	 * @return void
	 */
	public function testDbGetPrepareCursorValue(
		string $query,
		string $stm_name,
		string $key,
		$error_id
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		if (!empty($query)) {
			$db->dbPrepare($stm_name, $query);
			$db->dbExecute($stm_name);
		}
		$db->dbGetPrepareCursorValue($stm_name, $key);
		// match check error
		$last_error = $db->dbGetLastError();
		$this->assertEquals(
			$error_id,
			$last_error,
			'get prepare cursor value error check'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerDbPreparedCursorStatus(): array
	{
		return [
			'empty statement pararm' => [
				'query' => 'SELECT row_int, uid FROM table_with_primary_key',
				'stm_name' => 'test_stm_a',
				'check_stm_name' => '',
				'check_query' => '',
				'expected' => false
			],
			'different stm_name' => [
				'query' => 'SELECT row_int, uid FROM table_with_primary_key',
				'stm_name' => 'test_stm_b',
				'check_stm_name' => 'other_name',
				'check_query' => '',
				'expected' => 0
			],
			'same stm_name' => [
				'query' => 'SELECT row_int, uid FROM table_with_primary_key',
				'stm_name' => 'test_stm_c',
				'check_stm_name' => 'test_stm_c',
				'check_query' => '',
				'expected' => 1
			],
			'same stm_name and query' => [
				'query' => 'SELECT row_int, uid FROM table_with_primary_key',
				'stm_name' => 'test_stm_d',
				'check_stm_name' => 'test_stm_d',
				'check_query' => 'SELECT row_int, uid FROM table_with_primary_key',
				'expected' => 2
			],
			'same stm_name and different query' => [
				'query' => 'SELECT row_int, uid FROM table_with_primary_key',
				'stm_name' => 'test_stm_e',
				'check_stm_name' => 'test_stm_e',
				'check_query' => 'SELECT row_int, uid, row_int FROM table_with_primary_key',
				'expected' => 1
			],
			'insert query test' => [
				'query' => 'INSERT INTO table_with_primary_key (row_int, uid) VALUES ($1, $2)',
				'stm_name' => 'test_stm_f',
				'check_stm_name' => 'test_stm_f',
				'check_query' => 'INSERT INTO table_with_primary_key (row_int, uid) VALUES ($1, $2)',
				'expected' => 2
			]
		];
	}

	/**
	 * test cursor status for prepared statement
	 *
	 * @covers ::dbPreparedCursorStatus
	 * @dataProvider providerDbPreparedCursorStatus
	 * @testdox Check prepared $stm_name ($check_stm_name) status is $expected [$_dataName]
	 *
	 * @param  string   $query
	 * @param  string   $stm_name
	 * @param  string   $check_stm_name
	 * @param  string   $check_query
	 * @param  bool|int $expected
	 * @return void
	 */
	public function testDbPreparedCursorStatus(
		string $query,
		string $stm_name,
		string $check_stm_name,
		string $check_query,
		bool|int $expected
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$db->dbPrepare($stm_name, $query);
		// $db->dbExecute($stm_name);
		$this->assertEquals(
			$expected,
			$db->dbPreparedCursorStatus($check_stm_name, $check_query),
			'check prepared stement cursor status'
		);
		unset($db);
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
					// 'main::run::run::run::run::run::run::run::runBare::runTest::testDbErrorHandling::dbSetMaxQueryCall
					'source' => "/^(include::)?main::(run::)+runBare::runTest::testDbErrorHandling::dbSetMaxQueryCall$/",
					'pg_error' => '',
					'message' => '',
					'context' => [
						'max_calls' => 0
					]
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
			if (!is_array($value) && strpos($value, '/') === 0) {
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
	public function returningPrvoider(): array
	{
		// NOTE that query can have multiple inserts
		// NOTE if there are different INSERTS before the primary keys
		// will not match anymore. Must be updated by hand
		// IMPORTANT: if this is stand alone the primary key will not match and fail
		$table_with_primary_key_id = 70;
		// 0: query + returning
		// 1: params
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
			// same but as heredoc
			'single insert (PK), heredoc string' => [
				<<<SQL
				INSERT INTO table_with_primary_key (
					row_varchar, row_varchar_literal, row_int, row_date
				) VALUES (
					'Text', 'Other', 123, '2022-03-01'
				)
				RETURNING row_varchar, row_varchar_literal, row_int, row_date
				SQL,
				null,
				null,
				null,
				null,
				[
					'row_varchar' => 'Text',
					'row_varchar_literal' => 'Other',
					'row_int' => 123,
					'row_date' => '2022-03-01',
					// 'table_with_primary_key_id' => "/^\d+$/",
					'table_with_primary_key_id' => $table_with_primary_key_id + 2,
				],
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
						// 'table_with_primary_key_id' => "/^\d+$/",
						'table_with_primary_key_id' => $table_with_primary_key_id + 2,
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
				null,
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
						'table_with_primary_key_id' => $table_with_primary_key_id + 3,
					],
					1 => [
						'row_varchar' => 'Foxtrott',
						'row_varchar_literal' => 'Tango',
						'row_int' => 789,
						'row_date' => '1982-10-15',
						'table_with_primary_key_id' => $table_with_primary_key_id + 4,
					],
				],
				[
					0 => [
						'row_varchar' => 'Text',
						'row_varchar_literal' => 'Other',
						'row_int' => 123,
						'row_date' => '2022-03-01',
						'table_with_primary_key_id' => $table_with_primary_key_id + 3,
					],
					1 => [
						'row_varchar' => 'Foxtrott',
						'row_varchar_literal' => 'Tango',
						'row_int' => 789,
						'row_date' => '1982-10-15',
						'table_with_primary_key_id' => $table_with_primary_key_id + 4,
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
			],
			// same as above but as heredoc string
			'single insert (No PK), heredoc string' => [
				<<<SQL
				INSERT INTO table_without_primary_key (
					row_varchar, row_varchar_literal, row_int, row_date
				) VALUES (
					'Text', 'Other', 123, '2022-03-01'
				)
				RETURNING row_varchar, row_varchar_literal, row_int, row_date
				SQL,
				null,
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
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbGetReturningExt
	 * @covers ::dbGetReturningArray
	 * @dataProvider returningPrvoider
	 * @testdox Check returning cursor using $pk_name with $key and $pos [$_dataName]
	 *
	 * @param string $query
	 * @param array<mixed<|null $params
	 * @param string|null $pk_name
	 * @param string|null $key
	 * @param integer|null $pos
	 * @param array<mixed>|string|int|null $expected_ret_ext
	 * @param array $expected_ret_arr
	 * @return void
	 */
	public function testDbReturning(
		string $query,
		?array $params,
		?string $pk_name,
		?string $key,
		?int $pos,
		array|string|int|null $expected_ret_ext,
		array $expected_ret_arr
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// insert data
		if ($pk_name === null && $params === null) {
			$db->dbExec($query);
		} elseif ($params === null) {
			$db->dbExec($query, $pk_name);
		} elseif ($pk_name === null) {
			$db->dbExecParams($query, $params);
		} else {
			$db->dbExecParams($query, $params, $pk_name);
		}

		// get the last value for PK and match that somehow

		$returning_ext = $db->dbGetReturningExt($key, $pos);
		$returning_arr = $db->dbGetReturningArray();

		$this->assertEquals(
			$expected_ret_ext,
			$returning_ext,
			'Returning extended failed'
		);
		$this->assertEquals(
			$expected_ret_arr,
			$returning_arr,
			'Returning Array failed'
		);

		// print "EXT: " . print_r($returning_ext, true) . "\n";
		// print "ARR: " . print_r($returning_arr, true) . "\n";

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}

	// testing auto convert

	/**
	 * Undocumented function
	 *
	 * @covers ::dbSetConvertFlag
	 * @testdox Check convert type works
	 *
	 * @return void
	 */
	public function testConvertType(): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$bytea_data = $db->dbEscapeBytea(
			file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'CoreLibsDBIOTest.php') ?: ''
		);
		$query_insert = <<<SQL
		INSERT INTO table_with_primary_key (
			uid,
			row_int, row_numeric, row_varchar, row_varchar_literal,
			row_json, row_jsonb, row_bytea, row_timestamp,
			row_date, row_interval, row_array_int, row_array_varchar
		) VALUES (
			$1,
			$2, $3, $4, $5,
			$6, $7, $8, $9,
			$10, $11, $12, $13
		)
		SQL;
		$db->dbExecParams(
			$query_insert,
			[
				'CONVERT_TYPE_TEST',
				1, 1.5, 'varchar', 'varchar literla',
				json_encode(['json', 'a', 1, true, 'sub' => ['b', 'c']]),
				json_encode(['jsonb', 'a', 1, true, 'sub' => ['b', 'c']]),
				$bytea_data, date('Y-m-d H:i:s'), date('Y-m-d'), date('H:m:s'),
				'{1,2,3}', '{"a","b","c"}'
			]
		);
		$type_layout = [
			'uid' => 'string',
			'row_int' => 'int',
			'row_numeric' => 'float',
			'row_varchar' => 'string',
			'row_varchar_literal' => 'string',
			'row_json' => 'json',
			'row_jsonb' => 'json',
			'row_bytea' => 'bytea',
			'row_timestamp' => 'string',
			'row_date' => 'string',
			'row_interval' => 'string',
			'row_array_int' => 'string',
			'row_array_varchar' => 'string'
		];
		$query_select = <<<SQL
		SELECT
			uid,
			row_int, row_numeric, row_varchar, row_varchar_literal,
			row_json, row_jsonb, row_bytea, row_timestamp,
			row_date, row_interval, row_array_int, row_array_varchar
		FROM
			table_with_primary_key
		WHERE
			uid = $1
		SQL;
		$res = $db->dbReturnRowParams($query_select, ['CONVERT_TYPE_TEST']);
		// all hast to be string
		foreach ($res as $key => $value) {
			$this->assertIsString($value, 'Assert string for column: ' . $key);
		}
		// convert base only
		$db->dbSetConvertFlag(Convert::on);
		$res = $db->dbReturnRowParams($query_select, ['CONVERT_TYPE_TEST']);
		foreach ($res as $key => $value) {
			if (is_numeric($key)) {
				$name = $db->dbGetFieldName($key);
			} else {
				$name = $key;
			}
			switch ($type_layout[$name]) {
				case 'int':
					$this->assertIsInt($value, 'Assert int for column: ' . $key . '/' . $name);
					break;
				default:
					$this->assertIsString($value, 'Assert string for column: ' . $key  . '/' . $name);
					break;
			}
		}
		$db->dbSetConvertFlag(Convert::numeric);
		$res = $db->dbReturnRowParams($query_select, ['CONVERT_TYPE_TEST']);
		foreach ($res as $key => $value) {
			if (is_numeric($key)) {
				$name = $db->dbGetFieldName($key);
			} else {
				$name = $key;
			}
			switch ($type_layout[$name]) {
				case 'int':
					$this->assertIsInt($value, 'Assert int for column: ' . $key . '/' . $name);
					break;
				case 'float':
					$this->assertIsFloat($value, 'Assert float for column: ' . $key . '/' . $name);
					break;
				default:
					$this->assertIsString($value, 'Assert string for column: ' . $key  . '/' . $name);
					break;
			}
		}
		$db->dbSetConvertFlag(Convert::json);
		$res = $db->dbReturnRowParams($query_select, ['CONVERT_TYPE_TEST']);
		foreach ($res as $key => $value) {
			if (is_numeric($key)) {
				$name = $db->dbGetFieldName($key);
			} else {
				$name = $key;
			}
			switch ($type_layout[$name]) {
				case 'int':
					$this->assertIsInt($value, 'Assert int for column: ' . $key . '/' . $name);
					break;
				case 'float':
					$this->assertIsFloat($value, 'Assert float for column: ' . $key . '/' . $name);
					break;
				case 'json':
				case 'jsonb':
					$this->assertIsArray($value, 'Assert array for column: ' . $key . '/' . $name);
					break;
				default:
					$this->assertIsString($value, 'Assert string for column: ' . $key  . '/' . $name);
					break;
			}
		}
		$db->dbSetConvertFlag(Convert::bytea);
		$res = $db->dbReturnRowParams($query_select, ['CONVERT_TYPE_TEST']);
		foreach ($res as $key => $value) {
			if (is_numeric($key)) {
				$name = $db->dbGetFieldName($key);
			} else {
				$name = $key;
			}
			switch ($type_layout[$name]) {
				case 'int':
					$this->assertIsInt($value, 'Assert int for column: ' . $key . '/' . $name);
					break;
				case 'float':
					$this->assertIsFloat($value, 'Assert float for column: ' . $key . '/' . $name);
					break;
				case 'json':
				case 'jsonb':
					$this->assertIsArray($value, 'Assert array for column: ' . $key . '/' . $name);
					break;
				case 'bytea':
					// for hex types it must not start with \x
					$this->assertStringStartsNotWith(
						'\x',
						$value,
						'Assert bytes not starts with \x for column: ' . $key . '/' . $name
					);
					break;
				default:
					$this->assertIsString($value, 'Assert string for column: ' . $key  . '/' . $name);
					break;
			}
		}
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
		// 1: query params
		// 2: optional insert query (if select or needed)
		// 3: optional compare query, if not set 0 is used
		// 4: num rows
		// 5: column count
		// 6: column names
		// 7: column types
		return [
			'select data' => [
				"SELECT row_varchar, row_varchar_literal, row_int, row_date "
					. "FROM table_with_primary_key",
				null,
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, '2022-03-01'), "
					. "('Foxtrott', 'Tango', 789, '1982-10-15') ",
				null,
				//
				3,
				4,
				['row_varchar', 'row_varchar_literal', 'row_int', 'row_date'],
				['varchar', 'varchar', 'int4', 'date'],
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
				[],
			],
			// update
			'update data' => [
				"UPDATE table_with_primary_key SET "
					. "row_varchar = 'CHANGE A', row_int = 999 "
					. "WHERE uid = 'A'",
					null,
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
				[],
			],
			// select with params and proper query hashing
			'select data, params' => [
				"SELECT row_varchar, row_varchar_literal, row_int, row_date "
					. "FROM table_with_primary_key "
					. "WHERE row_varchar = $1",
				['Text'],
				"INSERT INTO table_with_primary_key "
					. "(row_varchar, row_varchar_literal, row_int, row_date) "
					. "VALUES "
					. "('Text', 'Other', 123, '2022-03-01'), "
					. "('Foxtrott', 'Tango', 789, '1982-10-15') ",
				null,
				//
				1,
				4,
				['row_varchar', 'row_varchar_literal', 'row_int', 'row_date'],
				['varchar', 'varchar', 'int4', 'date'],
			],
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
	 * @param array<mixed>|null $params,
	 * @param string|null $insert_query
	 * @param string|null $compare_query
	 * @param integer $expected_rows
	 * @param integer $expected_cols
	 * @param array<mixed> $expected_col_names
	 * @param array<mixed> $expected_col_types
	 * @return void
	 */
	public function testDbGetMethods(
		string $query,
		?array $params,
		?string $insert_query,
		?string $compare_query,
		int $expected_rows,
		int $expected_cols,
		array $expected_col_names,
		array $expected_col_types
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		if (!empty($insert_query)) {
			$db->dbExec($insert_query);
		}

		if ($params === null) {
			$db->dbExec($query);
		} else {
			$db->dbExecParams($query, $params);
		}

		$this->assertInstanceOf(
			'PgSql\Result',
			$db->dbGetCursor(),
			'Failed assert dbGetCursor'
		);

		$this->assertEquals(
			$compare_query ?? $query,
			$db->dbGetQuery(),
			'Failed assert dbGetQuery'
		);
		$this->assertEquals(
			// perhaps move that somewhere else?
			\CoreLibs\Create\Hash::__hashLong(
				$query . (
					$params !== null && $params !== [] ?
						'#' . json_encode($params) : ''
				)
			),
			($params === null ?
				$db->dbBuildQueryHash($query) :
				$db->dbBuildQueryHash($query, $params)
			),
			'Failed assertdbGetQueryHash '
		);
		$this->assertEquals(
			$expected_rows,
			$db->dbGetNumRows(),
			'Failed assert dbGetNumRows'
		);
		$this->assertEquals(
			$expected_cols,
			$db->dbGetNumFields(),
			'Failed assert dbGetNumFields'
		);
		$this->assertEquals(
			$expected_col_names,
			$db->dbGetFieldNames(),
			'Failed assert dbGetFieldNames'
		);
		$this->assertEquals(
			$expected_col_types,
			$db->dbGetFieldTypes(),
			'Failed assert dbGetFieldTypes'
		);
		// check FieldNameTypes matches
		$this->assertEquals(
			array_combine(
				$expected_col_names,
				$expected_col_types
			),
			$db->dbGetFieldNameTypes(),
			'Failed assert dbGetFieldNameTypes'
		);
		// check pos matches name
		// name matches type
		// pos matches type
		foreach ($expected_col_names as $pos => $name) {
			$this->assertEquals(
				$name,
				$db->dbGetFieldName($pos),
				'Failed assert dbGetFieldName: ' . $pos . ' => ' . $name
			);
			$this->assertEquals(
				$expected_col_types[$pos],
				$db->dbGetFieldType($name),
				'Failed assert dbGetFieldType: ' . $name . ' => ' . $expected_col_types[$pos]
			);
			$this->assertEquals(
				$expected_col_types[$pos],
				$db->dbGetFieldType($pos),
				'Failed assert dbGetFieldType: ' . $pos . ' => ' . $expected_col_types[$pos]
			);
		}
		$dbh = $db->dbGetDbh();
		$this->assertIsObject(
			$dbh
		);
		// also check that this is correct instance type
		$this->assertInstanceOf(
			'PgSql\Connection',
			$dbh
		);

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

	// MARK: QUERY PLACEHOLDERS

	// test query placeholder detection for all possible sets
	// ::dbPrepare

	/**
	 * placeholder sql
	 *
	 * @return array
	 */
	public function providerDbCountQueryParams(): array
	{
		return [
			'one place holder' => [
				'query' => 'SELECT row_varchar FROM table_with_primary_key WHERE row_varchar = $1',
				'count' => 1,
				'convert' => false,
			],
			'one place holder, json call' => [
				'query' => "SELECT row_varchar FROM table_with_primary_key WHERE row_jsonb->>'data' = $1",
				'count' => 1,
				'convert' => false,
			],
			'one place holder, <> compare' => [
				'query' => "SELECT row_varchar FROM table_with_primary_key WHERE row_varchar <> $1",
				'count' => 1,
				'convert' => false,
			],
			'one place holder, named' => [
				'query' => "SELECT row_varchar FROM table_with_primary_key WHERE row_varchar <> :row_varchar",
				'count' => 1,
				'convert' => true,
			],
			'no replacement' => [
				'query' => "SELECT row_varchar FROM table_with_primary_key WHERE row_varchar = '$1'",
				'count' => 0,
				'convert' => false,
			],
			'insert' => [
				'query' => "INSERT INTO table_with_primary_key (row_varchar, row_jsonb, row_int) VALUES ($1, $2, $3)",
				'count' => 3,
				'convert' => false,
			],
			'update' => [
				'query' => "UPDATE table_with_primary_key SET row_varchar = $1, row_jsonb = $2, row_int = $3 WHERE row_numeric = $4",
				'count' => 4,
				'convert' => false,
			],
			'multiple, multline' => [
				'query' => <<<SQL
				SELECT
					row_varchar
				FROM
					table_with_primary_key
				WHERE
					row_varchar = $1 AND row_int = $2
					AND row_numeric = ANY($3)
				SQL,
				'count' => 3,
				'convert' => false,
			],
			'two digit numbers' => [
				'query' => <<<SQL
				INSERT INTO table_with_primary_key (
					row_int, row_numeric, row_varchar, row_varchar_literal, row_json,
					row_jsonb, row_bytea, row_timestamp, row_date, row_interval
				) VALUES (
					$1, $2, $3, $4, $5,
					$6, $7, $8, $9, $10
				)
				SQL,
				'count' => 10,
				'convert' => false,
			],
			'things in brackets' => [
				'query' => <<<SQL
				SELECT row_varchar
				FROM table_with_primary_key
				WHERE
					row_varchar = $1 AND
					(row_int = ANY($2) OR row_int = $3)
					AND row_varchar_literal = $4
				SQL,
				'count' => 4,
				'convert' => false,
			],
			'number compare' => [
				'query' => <<<SQL
				SELECT row_varchar
				FROM table_with_primary_key
				WHERE
					row_int >= $1 OR row_int <= $2 OR
					row_int > $3 OR row_int < $4
					OR row_int = $5 OR row_int <> $6
				SQL,
				'count' => 6,
				'convert' => false,
			],
			'comments in insert' => [
				'query' => <<<SQL
				INSERT INTO table_with_primary_key (
					row_int, row_numeric, row_varchar, row_varchar_literal
				) VALUES (
					-- comment 1 かな
					$1, $2,
					-- comment 2 -
					$3
					-- comment 3
					, $4
					-- ignore $5, $6
					-- $7, $8
					-- digest($9, 10)
				)
				SQL,
				'count' => 4,
				'convert' => false
			],
			'comment in update' => [
				'query' => <<<SQL
				UPDATE table_with_primary_key SET
					row_int =
					-- COMMENT 1
					$1,
					row_numeric =
					$2 -- COMMENT 2
					,
					row_varchar -- COMMENT 3
					= $3
				WHERE
					row_varchar = $4
				SQL,
				'count' => 4,
				'convert' => false,
			],
			// Note some are not set
			'a complete set of possible' => [
				'query' => <<<SQL
				UPDATE table_with_primary_key SET
				-- ROW
				row_varchar = $1
				WHERE
				row_varchar = ANY($2) AND row_varchar <> $3
				AND row_varchar > $4 AND row_varchar < $5
				AND row_varchar >= $6 AND row_varchar <=$7
				AND row_jsonb->'a' = $8 AND row_jsonb->>$9 = 'a'
				AND row_jsonb<@$10 AND row_jsonb@>$11
				AND row_varchar ^@ $12
				SQL,
				'count' => 12,
				'convert' => false,
			],
			// all the same
			'all the same numbered' => [
				'query' => <<<SQL
				UPDATE table_with_primary_key SET
					row_int = $1::INT, row_numeric = $1::NUMERIC, row_varchar = $1
				WHERE
					row_varchar = $1
				SQL,
				'count' => 1,
				'convert' => false,
			],
			'update with case' => [
				'query' => <<<SQL
				UPDATE table_with_primary_key SET
					row_int = $1::INT,
					row_varchar = CASE WHEN row_int = 1 THEN $2 ELSE 'bar'::VARCHAR END
				WHERE
					row_varchar = $3
				SQL,
				'count' => 3,
				'convert' => false,
			],
			'select with case' => [
				'query' => <<<SQL
				SELECT row_int
				FROM table_with_primary_key
				WHERE
					row_varchar = CASE WHEN row_int = 1 THEN $1 ELSE $2 END
				SQL,
				'count' => 2,
				'convert' => false,
			],
			// special $$ string case
			'text string, with $ placehoders that could be seen as $$ string' => [
				'query' => <<<SQL
				SELECT row_int
				FROM table_with_primary_key
				WHERE
					row_bytea = digest($3::VARCHAR, $4) OR
					row_varchar = encode(digest($3, $4), 'hex') OR
					row_bytea = hmac($3, $5, $4) OR
					row_varchar = encode(hmac($3, $5, $4), 'hex') OR
					row_bytea = pgp_sym_encrypt($3, $6) OR
					row_varchar = encode(pgp_sym_encrypt($1, $6), 'hex') OR
					row_varchar = CASE WHEN row_int = 1 THEN $1 ELSE $2 END
				SQL,
				'count' => 6,
				'convert' => false,
			],
			// NOTE, in SQL heredoc we cannot write $$ strings parts
			'text string, with $ placehoders are in $$ strings' => [
				'query' => '
				SELECT row_int
				FROM table_with_primary_key
				WHERE
					row_varchar = $$some string$$ OR
					row_varchar = $tag$some string$tag$ OR
					row_varchar = $btag$some $1 string$btag$ OR
					row_varchar = $btag$some $1 $subtag$ something $subtag$string$btag$ OR
					row_varchar = $1
				',
				'count' => 1,
				'convert' => false,
			],
			// a text string with escaped quite
			'text string, with escaped quote' => [
				'query' => <<<SQL
				SELECT row_int
				FROM table_with_primary_key
				WHERE
					row_varchar = 'foo bar bar baz $5' OR
					row_varchar = 'foo bar '' barbar $6' OR
					row_varchar = E'foo bar \' barbar $7' OR
					row_varchar = CASE WHEN row_int = 1 THEN $1 ELSE $2 END
				SQL,
				'count' => 2,
				'convert' => false,
			]
		];
		$string = <<<SQL
		'''
		SQL;
	}

	/**
	 * Placeholder check and convert tests
	 *
	 * @covers ::dbPrepare
	 * @covers ::__dbCountQueryParams
	 * @onvers ::convertPlaceholderInQuery
	 * @dataProvider providerDbCountQueryParams
	 * @testdox Query replacement count test [$_dataName]
	 *
	 * @param  string $query
	 * @param  int    $count
	 * @return void
	 */
	public function testDbCountQueryParams(string $query, int $count, bool $convert): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$id = sha1($query);
		$db->dbSetConvertPlaceholder($convert);
		$db->dbPrepare($id, $query);
		// print "\n**\n";
		// print "PCount: " . $db->dbGetPrepareCursorValue($id, 'count') . "\n";
		// print "\n**\n";
		$this->assertEquals(
			$count,
			$db->dbGetPrepareCursorValue($id, 'count'),
			'DB count params'
		);
		$placeholder = ConvertPlaceholder::convertPlaceholderInQuery($query, null, 'pg');
		// print "RES: " . print_r($placeholder, true) . "\n";
		$this->assertEquals(
			$count,
			$placeholder['needed'],
			'convert params'
		);
	}

	/**
	 * query placeholder convert
	 *
	 * @return array
	 */
	public function queryPlaceholderReplaceProvider(): array
	{
		// 				WHERE row_varchar = $1
		return [
			'select, no change' => [
				'query' => <<<SQL
				SELECT row_varchar, row_varchar_literal, row_int, row_date
				FROM table_with_primary_key
				SQL,
				'params' => [],
				'found' => 0,
				'expected_query' => '',
				'expected_params' => [],
			],
			'select, params ?' => [
				'query' => <<<SQL
				SELECT row_varchar, row_varchar_literal, row_int, row_date
				FROM table_with_primary_key
				WHERE row_varchar = ?
				SQL,
				'params' => ['string a'],
				'found' => 1,
				'expected_query' => <<<SQL
				SELECT row_varchar, row_varchar_literal, row_int, row_date
				FROM table_with_primary_key
				WHERE row_varchar = $1
				SQL,
				'expected_params' => ['string a'],
			],
			'select, params :' => [
				'query' => <<<SQL
				SELECT row_varchar, row_varchar_literal, row_int, row_date
				FROM table_with_primary_key
				WHERE row_varchar = :row_varchar
				SQL,
				'params' => [':row_varchar' => 'string a'],
				'found' => 1,
				'expected_query' => <<<SQL
				SELECT row_varchar, row_varchar_literal, row_int, row_date
				FROM table_with_primary_key
				WHERE row_varchar = $1
				SQL,
				'expected_params' => ['string a'],
			],
			// TODO: test with multiple entries
			// TODO: test with same entry ($1, $1, :var, :var)
		];
	}

	/**
	 * test query string with placeholders convert
	 *
	 * @dataProvider queryPlaceholderReplaceProvider
	 * @testdox Query replacement test [$_dataName]
	 *
	 * @param  string $query
	 * @param  array  $params
	 * @param  string $expected_query
	 * @param  array  $expected_params
	 * @return void
	 */
	public function testQueryPlaceholderReplace(
		string $query,
		array $params,
		int $expected_found,
		string $expected_query,
		array $expected_params
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$db->dbSetConvertPlaceholder(true);
		//
		if ($db->dbCheckQueryForSelect($query)) {
			$res = $db->dbReturnRowParams($query, $params);
			$converted = $db->dbGetPlaceholderConverted();
		} else {
			$db->dbExecParams($query, $params);
			$converted = $db->dbGetPlaceholderConverted();
		}
		$this->assertEquals(
			$expected_found,
			$converted['found'],
			'Found not equal'
		);
		$this->assertEquals(
			$expected_query,
			$converted['query'],
			'Query not equal'
		);
		$this->assertEquals(
			$expected_params,
			$converted['params'],
			'Params not equal'
		);
	}

	/**
	 * test exception for placeholder convert
	 * -> internally converted to error
	 *
	 * @testdox Query Replace error tests
	 *
	 * @return void
	 */
	public function testQueryPlaceholderReplaceException(): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$db->dbSetConvertPlaceholder(true);
		$db->dbExecParams(
			<<<SQL
			SELECT foo FROM bar
			WHERE a = ? and b = :bname
			SQL,
			['a', 'b']
		);
		$this->assertEquals(
			200,
			$db->dbGetLastError()
		);

		// catch unset, for :names
		$db->dbExecParams(
			<<<SQL
			SELECT foo FROM bar
			WHERE a = :aname and b = :bname
			SQL,
			[':foo' => 'a', ':bname' => 'b']
		);
		$this->assertEquals(
			210,
			$db->dbGetLastError()
		);

		// TODO: other way around for to pdo
	}

	// TODO implement below checks
	// - complex write sets
	//   dbWriteData, dbWriteDataExt
	// - data debug
	//   dbDumpData

	// MARK: ASYNC

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
		// 1: params
		// 2: primary key
		// 3: exepected exec return
		// 4: warning
		// 5: error
		// 6: exepcted check return 1st
		// 7: final result
		// 8: warning
		// 9: error
		return [
			'run simple async query' => [
				"SELECT pg_sleep(1)",
				null,
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
			'run simple async query, params' => [
				"SELECT pg_sleep($1)",
				[1],
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
			// error on wrong params
			'wrong params count' => [
				"SELECT pg_sleep($1)",
				[],
				null,
				// exec result
				false,
				'',
				'23',
				// check first
				false,
				// check final
				false,
				'',
				'42'
			],
			// send query failed (E40)
			// result failed (E43)
			// no query running (E42)
			'no async query running' => [
				'',
				null,
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
	 * @covers ::dbExecParamsAsync
	 * @covers ::dbCheckAsync
	 * @dataProvider asyncProvider
	 * @testdox async query $query with $expected_exec (warning $warning_exec/error $error_exec) and $expected_check/$expected_final (warning $warning_final/error $error_final) [$_dataName]
	 *
	 * @param string $query
	 * @param array<mixed>|null $params
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
		?array $params,
		?string $pk_name,
		bool $expected_exec,
		string $warning_exec,
		string $error_exec,
		bool $expected_check,
		$expected_final,
		string $warning_final,
		string $error_final
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);

		// exec the query
		if ($pk_name === null && $params === null) {
			$result_exec = $db->dbExecAsync($query);
		} elseif ($params === null) {
			$result_exec = $db->dbExecAsync($query, $pk_name);
		} elseif ($pk_name === null) {
			$result_exec = $db->dbExecParamsAsync($query, $params);
		} else {
			$result_exec = $db->dbExecParamsAsync($query, $params, $pk_name);
		}
		$this->assertEquals(
			$expected_exec,
			$result_exec
		);
		// error/warning check
		$this->subAssertErrorTest($db, $warning_exec, $error_exec);

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
		// check after final
		if ($expected_final == 'result') {
			// post end check
			$this->assertIsObject(
				$result_check
			);
			// also check that this is correct instance type
			$this->assertInstanceOf(
				'PgSql\Result',
				$result_check
			);
		} else {
			// else compar check
			$this->assertEquals(
				$expected_final,
				$result_check
			);
		}
		// error/warning check
		$this->subAssertErrorTest($db, $warning_final, $error_final);

		// reset all data
		$db->dbExec("TRUNCATE table_with_primary_key");
		$db->dbExec("TRUNCATE table_without_primary_key");
		// close connection
		$db->dbClose();
	}
}

// __END__
