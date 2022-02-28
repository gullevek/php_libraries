<?php // phpcs:disable Generic.Files.LineLength

/*
This test needs a working database with certain tables setup
DB Name: corelibs_db_io_test
DB User: corelibs_db_io_test
DB Password: corelibs_db_io_test
DB Encoding: UTF8
User must be able to drop/create tables

Below tables will be auto created
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
	 * Check if tables exist
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
				'Cannot connect to valid Test DB.'
			);
		}
		// check if they already exist, drop them
		if ($db->dbShowTableMetaData('table_with_primary_key') !== false) {
			$db->dbExec("DROP TABLE table_with_primary_key");
			$db->dbExec("DROP TABLE table_without_primary_key");
		}
		$base_table = "row_int INT, "
			. "row_numeric NUMERIC, "
			. "row_varchar VARCHAR, "
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
			. "row_primary_key SERIAL PRIMARY KEY, "
			. $base_table
		);
		$db->dbExec(
			"CREATE TABLE table_without_primary_key ("
			. $base_table
		);
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

		// TODO: Mock \CoreLibs\DB\SQL\PgSQL somehow
		// Create a stub for the SomeClass class.
		// $stub = $this->createMock(\CoreLibs\DB\IO::class);
		// $stub->method('dbVersion')
		// 	->willReturn('13.1.0');
		// print "DB: " . $stub->dbVersion() . "\n";
		// print "TEST: " . ($stub->dbCompareVersion('=13.1.0') ? 'YES' : 'NO') . "\n";
		// print "TEST: " . ($stub->dbCompareVersion('=13.5.0') ? 'YES' : 'NO') . "\n";
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
			// print "ERRORS: " . print_r($db->dbGetErrorHistory(true), true) . "\n";
		}
	}

	// - debug flag sets
	//   dbGetDebug,  dbSetDebug, dbToggleDebug

	/**
	 * Undocumented function
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
	 * Undocumented function
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
		bool $expected,
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);
		if ($set === null) {
			// equals to do nothing
			$this->assertEquals(
				$expected,
				$db->dbSetDebug()
			);
		} else {
			$this->assertEquals(
				$expected,
				$db->dbSetDebug($set)
			);
		}
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
		bool $expected,
	): void {
		$db = new \CoreLibs\DB\IO(
			self::$db_config[$connection],
			self::$log
		);
		if ($toggle === null) {
			// equals to do nothing
			$this->assertEquals(
				$expected,
				$db->dbToggleDebug()
			);
		} else {
			$this->assertEquals(
				$expected,
				$db->dbToggleDebug($toggle)
			);
		}
		// must always match
		$this->assertEquals(
			$expected,
			$db->dbGetDebug()
		);
		$db->dbClose();
	}

	// - set max query call sets
	//   dbSetMaxQueryCall, dbGetMaxQueryCall

	public function maxQueryCallProvider(): array
	{
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
	 * Undocumented function
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

	// - set and get schema
	//   dbGetSchema, dbSetSchema,

	// - encoding settings (exclude encoding test, just set)
	//   dbGetEncoding, dbSetEncoding

	public function encodingProvider(): array
	{
		// 0: connection
		// 1: set encoding
		// 2: expected return from set
		// 2: expected to get
		return [
			'default set no encoding' => [
				'valid',
				'',
				false,
				// I expect that the default DB is set to UTF8
				'UTF8'
			],
			'set to Shift JIS' => [
				'valid',
				'ShiftJIS',
				true,
				'SJIS'
			],
			// 'set to Invalid' => [
			// 	'valid',
			// 	'Invalid',
			// 	false,
			// 	'UTF8'
			// ],
			// other tests includ perhaps mocking for error?
		];
	}

	/**
	 * Undocumented function
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
			$db->dbSetEncoding($set_encoding)
		);
		$this->assertEquals(
			$expected_get_encoding,
			$db->dbGetEncoding()
		);
		$db->dbClose();
	}

	// - all general data from connection array
	//   dbGetSetting (name, user, ecnoding, schema, host, port, ssl, debug, password)

		/**
	 * returns ALL connections sets
	 *
	 * @return array
	 */
	public function connectionCompleteProvider(): array
	{
		$connections = [];
		// return self::$db_config;
		foreach (self::$db_config as $connection => $settings) {
			$connections['DB Connection: ' . $connection] = [
				$connection,
				$settings,
			];
		}

		return $connections;
	}

	/**
	 * Undocumented function
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
	 * Undocumented function
	 *
	 * @return array
	 */
	public function booleanProvider(): array
	{
		return [
			'source "t" to true' => [
				't',
				true,
				false
			],
			'source "true" to true' => [
				'true',
				true,
				false
			],
			'source "f" to false' => [
				'f',
				false,
				false
			],
			'source "false" to false' => [
				'false',
				false,
				false
			],
			'source anything to true' => [
				'something',
				true,
				false,
			],
			'source empty to false' => [
				'',
				false,
				false,
			],
			'source bool true to "t"' => [
				true,
				't',
				true,
			],
			'source bool false to "f"' => [
				false,
				'f',
				true,
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
	 * @param string|bool $expected
	 * @param bool $reverse
	 * @return void
	 */
	public function testDbBoolean($source, $expected, bool $reverse): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$db->dbBoolean($source, $reverse)
		);
		$db->dbClose();
	}

	// - test interval/age string conversion to
	//   \CoreLibs\Combined\DateTime::stringToTime/timeStringFormat compatbile
	//   dbTimeFormat

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function timeFormatProvider(): array
	{
		return [
			'interval a' => [
				'41 years 9 mons 18 days',
				false,
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
	 * Undocumented function
	 *
	 * @covers ::dbTimeFormat
	 * @dataProvider timeFormatProvider
	 * @testdox Have $source and convert ($show_micro) to $expected [$_dataName]
	 *
	 * @param string $source
	 * @param bool $show_micro
	 * @param string $expected
	 * @return void
	 */
	public function testDbTimeFormat(string $source, bool $show_micro, string $expected): void
	{
		$db = new \CoreLibs\DB\IO(
			self::$db_config['valid'],
			self::$log
		);
		$this->assertEquals(
			$expected,
			$db->dbTimeFormat($source, $show_micro)
		);
		$db->dbClose();
	}

	// - convert PostreSQL arrays into PHP
	//   dbArrayParse

	public function arrayProvider(): array
	{
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
	 * Undocumented function
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

	public function stringProvider(): array
	{
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
	 * The function should current escape settings to do proper checks
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
	 * Undocumented function
	 *
	 * @return array
	 */
	public function byteaProvider(): array
	{
		return [
			'standard empty string' => [
				'',
				'\x'
			],
			'random values' => [
				'""9f8a!1012938123712378a../%(\'%)"!"#0"#$%\'"#$00"#$0"#0$0"#$',
				'\x2222396638612131303132393338313233373132333738612e2e2f2528272529222122233022232425272223243030222324302223302430222324'
			]
		];
	}

	/**
	 * Test bytea escape
	 * NOTE:
	 * This depends on bytea encoding settings on the server,
	 * Currently skip as true
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
	 * Undocumented function
	 *
	 * @return array
	 */
	public function sqlEscapeProvider(): array
	{
		return [
			// int
			'integer value' => [1, 'i', 1,],
			'bad integer value' => ['za', 'i', 0,],
			'empty integer value' => ['', 'i', 'NULL',],
			// float
			'float value' => [1.1, 'f', 1.1,],
			'bad float value' => ['za', 'f', 0,],
			'empty float value' => ['', 'f', 'NULL',],
			// text
			'string value' => ['string value', 't', '\'string value\'',],
			'empty string value' => ['', 't', 'NULL',],
			// text literal
			'string value literal' => ['string literal', 'tl', '\'string literal\'',],
			'empty string value literal' => ['', 'tl', 'NULL',],
			// ?d
			'string value d' => ['string d', 'd', '\'string d\'',],
			'empty string value d' => ['', 'd', 'NULL',],
			// b (bool)
			'bool true value' => [true, 'b', '\'t\'',],
			'bool false value' => [false, 'b', '\'f\'',],
			'empty bool value' => ['', 'b', 'NULL',],
			// i2
			'integer2 value' => [1, 'i2', 1,],
			'bad integer2 value' => ['za', 'i2', 0,],
			'empty integer2 value' => ['', 'i2', 0,],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::dbSqlEscape
	 * @dataProvider sqlEscapeProvider
	 * @testdox Input value $input as $flag to $expected [$_dataName]
	 *
	 * @param int|float|string $input
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

	// - db execution tests
	//   dbReturn, dbDumpData, dbCacheReset, dbExec, dbExecAsync, dbCheckAsync
	//   dbFetchArray, dbReturnRow, dbReturnArray, dbCursorPos, dbCursorNumRows,
	//   dbShowTableMetaData, dbPrepare, dbExecute
	// - internal read data (post exec)
	//   dbGetReturning, dbGetInsertPKName, dbGetInsertPK, dbGetReturningExt,
	//   dbGetReturningArray, dbGetCursorExt, dbGetNumRows,
	//   getHadError, getHadWarning,
	//   dbResetQueryCalled, dbGetQueryCalled
	// - complex write sets
	//   dbWriteData, dbWriteDataExt
	// - deprecated tests [no need to test perhaps]
	//   getInsertReturn, getReturning, getInsertPK, getReturningExt,
	//   getCursorExt, getNumRows

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
