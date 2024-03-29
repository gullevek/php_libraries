<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

// TODO: setLogPer test log file written matches pattern

/**
 * Test class for Debug\Logging
 * @coversDefaultClass \CoreLibs\Debug\LoggingLegacy
 * @testdox \CoreLibs\Debug\LoggingLegacy method tests
 */
final class CoreLibsDebugLoggingLegacyTest extends TestCase
{
	private const LOG_FOLDER = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;

	/**
	 * test set for options BASIC
	 *
	 * 0: options
	 * - null for NOT set
	 * 1: expected
	 * 2: override
	 * override:
	 * - constant for CONSTANTS
	 * - global for _GLOBALS
	 *
	 * @return array
	 */
	public function optionsProvider(): array
	{
		return [
			'log folder set' => [
				[
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
					'file_id' => 'testClassInit'
				],
				[
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'debug_all' => false,
					'print_all' => false,
				],
				[]
			],
			'nothing set' => [
				[
					'file_id' => 'testClassInit'
				],
				[
					'log_folder' => getcwd() . DIRECTORY_SEPARATOR,
					'debug_all' => false,
					'print_all' => false,
				],
				[]
			],
			'no options set, constant set [DEPRECATED]' => [
				[
					'file_id' => 'testClassInit'
				],
				[
					'log_folder' => str_replace(DIRECTORY_SEPARATOR . 'configs', '', __DIR__)
							. DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR,
					'debug_all' => false,
					'print_all' => false,
				],
				[
					'constant' => [
						'BASE' => str_replace(DIRECTORY_SEPARATOR . 'configs', '', __DIR__)
							. DIRECTORY_SEPARATOR,
						'LOG' => 'log' . DIRECTORY_SEPARATOR
					]
				]
			],
			'standard test set' => [
				[
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
					'file_id' => 'testClassInit',
					'debug_all' => true,
					'print_all' => true,
				],
				[
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'debug_all' => true,
					'print_all' => true,
				],
				[]
			]
		];
	}

	/**
	 * init logging class
	 *
	 * @dataProvider optionsProvider
	 * @testdox init test [$_dataName]
	 *
	 * @param array $options
	 * @param array $expected
	 * @param array $override
	 * @return void
	 */
	public function testClassInit(array $options, array $expected, array $override): void
	{
		if (!empty($override['constant'])) {
			foreach ($override['constant'] as $var => $value) {
				if (!defined($var)) {
					define($var, $value);
				}
			}
			// for deprecated no log_folder set
			// if base is defined and it does have AAASetupData set
			// change the log_folder "Debug" to "AAASetupData"
			if (
				defined('BASE') &&
				strpos(BASE, DIRECTORY_SEPARATOR . 'AAASetupData') !== false
			) {
				$expected['log_folder'] = str_replace(
					DIRECTORY_SEPARATOR . 'Debug',
					DIRECTORY_SEPARATOR . 'AAASetupData',
					$expected['log_folder']
				);
			}
		}
		// if not log folder and constant set -> expect E_USER_DEPRECATION
		if (!empty($override['constant']) && empty($options['log_folder'])) {
			// the deprecation message
			$deprecation_message = 'options: log_folder must be set. '
				. 'Setting via BASE and LOG constants is deprecated';
			// convert E_USER_DEPRECATED to a exception
			set_error_handler(
				static function (int $errno, string $errstr): never {
					throw new \Exception($errstr, $errno);
				},
				E_USER_DEPRECATED
			);
			// catch this with the message
			$this->expectExceptionMessage($deprecation_message);
		}
		$log = new \CoreLibs\Debug\LoggingLegacy($options);
		// reset error handler
		restore_error_handler();
		// check that settings match
		$this->assertEquals(
			$expected['log_folder'],
			$log->getSetting('log_folder'),
			'log folder not matching'
		);
		$this->assertEquals(
			$expected['debug_all'],
			$log->getSetting('debug_output_all'),
			'debug all flag not matching'
		);
		$this->assertEquals(
			$expected['print_all'],
			$log->getSetting('print_output_all'),
			'print all flag not matching'
		);
		// print "LOG: " . $log->getSetting('log_folder') . "\n";
		// print "DEBUG: " . $log->getSetting('debug_output_all') . "\n";
		// print "PRINT: " . $log->getSetting('print_output_all') . "\n";
	}

	/**
	 * adds log ID settings based on basic options
	 *
	 * @return array
	 */
	public function logIdOptionsProvider(): array
	{
		// 0: options
		// 1: expected
		// 2: override
		// 3: exception message
		return [
			'no log id set' => [
				[
					'log_folder' => self::LOG_FOLDER,
				],
				[
					'log_file_id' => ''
				],
				[],
				null
			],
			// set log id manually afterwards
			'set log id manually' => [
				[
					'log_folder' => self::LOG_FOLDER,
				],
				[
					'log_file_id' => '',
					'set_log_file_id' => 'abc123',
				],
				[
					// set post launch
					'values' => [
						'log_file_id' => 'abc123'
					]
				],
				null
			],
			// set log id from options
			'set log id via options' => [
				[
					'file_id' => 'abc456',
					'log_folder' => self::LOG_FOLDER,
				],
				[
					'log_file_id' => 'abc456'
				],
				[],
				null
			],
			// set log id from GLOBALS [DEPRECATED]
			'set log id via globals [DEPRECATED]' => [
				[
					'log_folder' => self::LOG_FOLDER,
				],
				[
					'log_file_id' => 'def123'
				],
				[
					'globals' => [
						'LOG_FILE_ID' => 'def123'
					]
				],
				'options: file_id must be set. Setting via LOG_FILE_ID global variable is deprecated'
			],
			// set log id from CONSTANT [DEPRECATED]
			'set log id via constant [DEPRECATED]' => [
				[
					'log_folder' => self::LOG_FOLDER,
				],
				[
					'log_file_id' => 'ghi123'
				],
				[
					// reset global
					'globals' => [
						'LOG_FILE_ID' => null
					],
					'constant' => [
						'LOG_FILE_ID' => 'ghi123'
					]
				],
				'options: file_id must be set. Setting via LOG_FILE_ID constant is deprecated'
			],
			// invalid, keep previous set
			'invalid log id' => [
				[
					'file_id' => 'jkl456',
					'log_folder' => self::LOG_FOLDER,
				],
				[
					'log_file_id' => 'jkl456',
					'set_log_file_id' => 'jkl456',
				],
				[
					'values' => [
						'log_file_id' => './#'
					]
				],
				null
			]
		];
	}

	/**
	 * test the setting and getting of LogId
	 *
	 * @covers ::setLogId
	 * @dataProvider logIdOptionsProvider
	 * @testdox log id set/get tests [$_dataName]
	 *
	 * @param array $options
	 * @param array $expected
	 * @param array $override
	 * @param string|null $deprecation_message until we remove the old code
	 * @return void
	 */
	public function testLogId(
		array $options,
		array $expected,
		array $override,
		?string $deprecation_message
	): void {
		// we need to set with file_id option, globals LOG_FILE_ID, constant LOG_FILE_ID
		if (!empty($override['constant'])) {
			foreach ($override['constant'] as $var => $value) {
				define($var, $value);
			}
		}
		if (!empty($override['globals'])) {
			foreach ($override['globals'] as $var => $value) {
				$GLOBALS[$var] = $value;
			}
		}
		if (!empty($override['constant']) || !empty($override['globals'])) {
			// convert E_USER_DEPRECATED to a exception
			set_error_handler(
				static function (int $errno, string $errstr): never {
					throw new \Exception($errstr, $errno);
				},
				E_USER_DEPRECATED
			);
			// catch this with the message
			$this->expectExceptionMessage($deprecation_message);
		}
		$log = new \CoreLibs\Debug\LoggingLegacy($options);
		// reset error handler
		restore_error_handler();
		// check current
		$this->assertEquals(
			$log->getLogId(),
			$expected['log_file_id']
		);
		// we need to override now too
		if (!empty($override['values'])) {
			// check if we have values, set them post and assert
			$log->setLogId($override['values']['log_file_id']);
			$this->assertEquals(
				$log->getLogId(),
				$expected['set_log_file_id']
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logLevelAllProvider(): array
	{
		// 0: type
		// 1: flag
		// 2: expected set
		// 3: expected get
		return [
			'debug all true' => [
				'debug',
				true,
				true,
				true,
			],
			'echo all true' => [
				'echo',
				true,
				true,
				true,
			],
			'print all true' => [
				'print',
				true,
				true,
				true,
			],
			'set invalid level' => [
				'invalud',
				true,
				false,
				false,
			],
		];
	}

	/**
	 * check set/get for log level all flag
	 *
	 * @dataProvider logLevelAllProvider
	 * @testdox set/get all log level $type with flag $flag [$_dataName]
	 *
	 * @param string $type
	 * @param bool $flag
	 * @param bool $expected_set
	 * @param bool $expected_get
	 * @return void
	 */
	public function testSetGetLogLevelAll(
		string $type,
		bool $flag,
		bool $expected_set,
		bool $expected_get
	): void {
		// neutral start with default
		$log = new \CoreLibs\Debug\LoggingLegacy([
			'file_id' => 'testSetGetLogLevelAll',
			'log_folder' => self::LOG_FOLDER
		]);
		// set and check
		$this->assertEquals(
			$log->setLogLevelAll($type, $flag),
			$expected_set
		);
		// get and check
		$this->assertEquals(
			$log->getLogLevelAll($type),
			$expected_get
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logLevelProvider(): array
	{
		// 0: type
		// 1: flag
		// 2: debug on (array)
		// 3: expected set
		// 4: level
		// 5: expected get
		return [
			'set debug on for level A,B,C and check full set' => [
				'debug',
				'on',
				['A', 'B', 'C'],
				true,
				null,
				[
					'A' => true,
					'B' => true,
					'C' => true,
				]
			],
			'set debug off for level A,B,C and check A' => [
				'debug',
				'off',
				['A', 'B', 'C'],
				true,
				'A',
				true,
			],
			// set one to false
			'set debug off for level A, B to false and check all' => [
				'debug',
				'off',
				['A', 'B' => false],
				true,
				null,
				[
					'A' => true,
					'B' => false,
				],
			],
			// set invalid type
			'set invalid level' => [
				'invalid',
				'',
				[],
				false,
				null,
				false
			],
			// set invalid flag
			'set invalid on flag' => [
				'print',
				'invalid',
				[],
				false,
				null,
				false
			],
			// missing debug array set
			'missing debug level array' => [
				'print',
				'off',
				[],
				false,
				null,
				[]
			],
			// set but check no existing
			'set level but check no exisitng' => [
				'print',
				'on',
				['A'],
				true,
				'C',
				false
			]
		];
	}

	/**
	 * checks setting for per log info level
	 *
	 * @covers ::setLogLevel
	 * @dataProvider logLevelProvider
	 * @testdox set/get log level $type to $flag check with $level [$_dataName]
	 *
	 * @param string $type
	 * @param string $flag
	 * @param array $debug_on
	 * @param bool $expected_set
	 * @param string|null $level
	 * @param bool|array<mixed> $expected_get
	 * @return void
	 */
	public function testSetGetLogLevel(
		string $type,
		string $flag,
		array $debug_on,
		bool $expected_set,
		?string $level,
		$expected_get
	): void {
		// neutral start with default
		$log = new \CoreLibs\Debug\LoggingLegacy([
			'file_id' => 'testSetGetLogLevel',
			'log_folder' => self::LOG_FOLDER
		]);
		// set
		$this->assertEquals(
			$log->setLogLevel($type, $flag, $debug_on),
			$expected_set
		);
		// get, if level is null compare to?
		$this->assertEquals(
			$log->getLogLevel($type, $flag, $level),
			$expected_get
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logPerProvider(): array
	{
		// 0: type
		// 1: set
		// 2: expected set
		// 3: expected get
		return [
			'level set true' => [
				'level',
				true,
				true,
				true,
			],
			'class set true' => [
				'class',
				true,
				true,
				true,
			],
			'page set true' => [
				'page',
				true,
				true,
				true,
			],
			'run set true' => [
				'run',
				true,
				true,
				true,
			],
			'set invalid type' => [
				'invalid',
				true,
				false,
				false,
			]
		];
	}

	/**
	 * set and get per log
	 * for level/class/page/run flags
	 *
	 * @covers ::setLogPer
	 * @dataProvider logPerProvider
	 * @testdox set/get log per $type with $set [$_dataName]
	 *
	 * @param string $type
	 * @param boolean $set
	 * @param boolean $expected_set
	 * @param boolean $expected_get
	 * @return void
	 */
	public function testSetGetLogPer(
		string $type,
		bool $set,
		bool $expected_set,
		bool $expected_get
	): void {
		// neutral start with default
		$log = new \CoreLibs\Debug\LoggingLegacy([
			'file_id' => 'testSetGetLogPer',
			'log_folder' => self::LOG_FOLDER
		]);
		// set and check
		$this->assertEquals(
			$log->setLogPer($type, $set),
			$expected_set
		);
		// get and check
		$this->assertEquals(
			$log->getLogPer($type),
			$expected_get
		);
	}

	/**
	 * set the print log file date part
	 *
	 * @covers ::setGetLogPrintFileDate
	 * @testWith [true, true, true]
	 *           [false, false, false]
	 * @testdox set/get log file date to $input [$_dataName]
	 *
	 * @param boolean $input
	 * @param boolean $expected_set
	 * @param boolean $expected_get
	 * @return void
	 */
	public function testSetGetLogPrintFileDate(bool $input, bool $expected_set, bool $expected_get): void
	{
		// neutral start with default
		$log = new \CoreLibs\Debug\LoggingLegacy([
			'file_id' => 'testSetGetLogPrintFileDate',
			'log_folder' => self::LOG_FOLDER
		]);
		// set and check
		$this->assertEquals(
			$log->setGetLogPrintFileDate($input),
			$expected_set
		);
		$this->assertEquals(
			$log->setGetLogPrintFileDate(),
			$expected_get
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function prArProvider(): array
	{
		return [
			'simple array' => [
				[
					'A' => 'foobar'
				],
				"##HTMLPRE##Array\n(\n"
				. "    [A] => foobar\n"
				. ")\n"
				. "##/HTMLPRE##"
			],
			'empty array' => [
				[],
				"##HTMLPRE##Array\n(\n"
				. ")\n"
				. "##/HTMLPRE##"
			],
			'nested array' => [
				[
					'A' => [
						'B' => 'bar'
					]
				],
				"##HTMLPRE##Array\n(\n"
				. "    [A] => Array\n"
				. "        (\n"
				. "            [B] => bar\n"
				. "        )\n"
				. "\n"
				. ")\n"
				. "##/HTMLPRE##"
			],
		];
	}

	/**
	 * convert array to string with ## pre replace space holders
	 *
	 * @covers ::prAr
	 * @dataProvider prArProvider
	 * @testdox check prAr array to string conversion [$_dataName]
	 *
	 * @param array $input
	 * @param string $expected
	 * @return void
	 */
	public function testPrAr(array $input, string $expected): void
	{
		$log = new \CoreLibs\Debug\LoggingLegacy([
			'file_id' => 'testPrAr',
			'log_folder' => self::LOG_FOLDER
		]);
		$this->assertEquals(
			$log->prAr($input),
			$expected
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function prBlProvider(): array
	{
		// 0: input flag (bool)
		// 1: is true
		// 2: is flase
		// 3: epxected
		return [
			'true bool default' => [
				true,
				null,
				null,
				'true'
			],
			'false bool default' => [
				false,
				null,
				null,
				'false'
			],
			'true bool override' => [
				true,
				'ok',
				'not ok',
				'ok'
			],
			'false bool override' => [
				false,
				'ok',
				'not ok',
				'not ok'
			],
		];
	}

	/**
	 * check bool to string converter
	 *
	 * @covers ::prBl
	 * @dataProvider prBlProvider
	 * @testdox check prBl $input ($true/$false) is expected $false [$_dataName]
	 *
	 * @param  bool        $input
	 * @param  string|null $true
	 * @param  string|null $false
	 * @param  string      $expected
	 * @return void
	 */
	public function testPrBl(bool $input, ?string $true, ?string $false, string $expected): void
	{
		$log = new \CoreLibs\Debug\LoggingLegacy([
			'file_id' => 'testPrBl',
			'log_folder' => self::LOG_FOLDER
		]);
		$return = '';
		if ($true === null && $false === null) {
			$return = $log->prBl($input);
		} elseif ($true !== null || $false !== null) {
			$return = $log->prBl($input, $true ?? '', $false ?? '');
		}
		$this->assertEquals(
			$expected,
			$return
		);
	}

	// from here are complex debug tests

	/**
	 * Undocumented function
	 *
	 * 0: array $options
	 * 1: array $debug_msg
	 * 2: boolean $expected_debug
	 * 3: string $expected_file
	 * 4: string $expected_string_start
	 * 5: string $expected_string_contains
	 *
	 * @return array
	 */
	public function debugProvider(): array
	{
		// error message to pass in
		$error_msg['A'] = [
			'level' => 'A',
			'string' => 'error msg',
			'strip' => false,
			'prefix' => '',
		];
		// file content to check
		$file_msg['A'] = "{PHPUnit\TextUI\Command} <A> - error msg\n";
		// string messages to check
		$string_msg['A'] = [
			's' => '<div style="text-align: left; padding: 5px; font-size: 10px; '
				. 'font-family: sans-serif; border-top: 1px solid black; '
				. 'border-bottom: 1px solid black; margin: 10px 0 10px 0; '
				. 'background-color: white; color: black;">'
				. '<div style="font-size: 12px;">{<span style="font-style: '
				. 'italic; color: #928100;">PHPUnit\TextUI\Command</span>}'
				. '</div><div style="font-size: 12px;">[<span style="font-style: '
				. 'italic; color: #c56c00;">A</span>] </div><div>[<span '
				. 'style="font-weight: bold; color: #5e8600;">',
			'c' => 'PHPUnit\TextUI\Command</span>} - error msg</div><!--#BR#-->',
		];
		// array provider
		return [
			'A debug: on, print: on, echo: on' => [
				[
					'debug_all' => true,
					'print_all' => true,
					'echo_all' => true,
				],
				$error_msg['A'],
				true,
				$file_msg['A'],
				$string_msg['A']['s'],
				$string_msg['A']['c'],
			],
			'B debug: on, print: off, echo: on' => [
				[
					'debug_all' => true,
					'print_all' => false,
					'echo_all' => true,
				],
				$error_msg['A'],
				true,
				'',
				$string_msg['A']['s'],
				$string_msg['A']['c'],
			],
			'C debug: on, print: on, echo: off' => [
				[
					'debug_all' => true,
					'print_all' => true,
					'echo_all' => false,
				],
				$error_msg['A'],
				true,
				$file_msg['A'],
				'',
				'',
			],
			'D debug: on, print: off, echo: off' => [
				[
					'debug_all' => true,
					'print_all' => false,
					'echo_all' => false,
				],
				$error_msg['A'],
				false,
				'',
				'',
				''
			],
			'E debug: off, print: off, echo: off' => [
				[
					'debug_all' => false,
					'print_all' => false,
					'echo_all' => false,
				],
				$error_msg['A'],
				false,
				'',
				'',
				''
			]
			// TODO more tests with different error messages
		];
	}

	/**
	 * Test debug flow
	 *
	 * @covers ::debug
	 * @dataProvider debugProvider
	 * @testdox check debug flow: $expected_debug [$_dataName]
	 *
	 * @param array $options
	 * @param array $debug_msg
	 * @param boolean $expected_debug
	 * @param string $expected_file
	 * @param string $expected_string_start
	 * @param string $expected_string_contains
	 * @return void
	 */
	public function testDebug(
		array $options,
		array $debug_msg,
		bool $expected_debug,
		string $expected_file,
		string $expected_string_start,
		string $expected_string_contains
	): void {
		// must run with below matrix
		// level    | debug | print | echo | debug() | printErrorMsg() | file
		// A 1/1/1  | on    | on    | on   | true    | 'string'        | on
		// B 1/0/1  | on    | off   | on   | true    | 'string'        | off
		// C 1/1/0  | on    | on    | off  | true    | ''              | on
		// D 1/0/0  | on    | off   | off  | false   | ''              | off
		// E 0/1/1  | off   | on    | on   | false   | ''              | off
		// F 0/0/1  | off   | off   | on   | false   | ''              | off
		// G 0/1/0  | off   | on    | off  | false   | ''              | off
		// H 0/0/0  | off   | off   | off  | false   | ''              | off


		// * debug off
		// return false on debug(),
		// return false on writeErrorMsg()
		// empty string on printErrorMsg
		// * print off
		// return true on debug(),
		// return false on writeErrorMsg()
		// empty string on printErrorMsg
		// * echo off
		// return true on debug(),
		// empty string on printErrorMsg
		// fillxed error_msg array

		// overwrite any previous set from test
		$options['file_id'] = 'TestDebug';
		// set log folder to temp
		$options['log_folder'] = '/tmp/';
		// remove any files named /tmp/error_log_TestDebug*.log
		array_map('unlink', glob($options['log_folder'] . 'error_msg_' . $options['file_id'] . '*.log'));
		// init logger
		$log = new \CoreLibs\Debug\LoggingLegacy($options);
		// * debug (A/B)
		// NULL check for strip/prefix
		$this->assertEquals(
			$log->debug(
				$debug_msg['level'],
				$debug_msg['string'],
				$debug_msg['strip'],
				$debug_msg['prefix'],
			),
			$expected_debug
		);
		// * if print check data in log file
		$log_file = $log->getLogFileName();
		if (!empty($options['debug_all']) && !empty($options['print_all'])) {
			// file name matching
			$this->assertStringStartsWith(
				$options['log_folder'] . 'error_msg_' . $options['file_id'],
				$log_file,
			);
			// cotents check
			if (!is_file($log_file)) {
				$this->fail('error msg file not found: ' . $log_file);
			} else {
				$log_data = file_get_contents($log_file);
				if ($log_data === null) {
					$this->fail('error msg file not readable or not data: ' . $log_file);
				}
				// file content matching
				$this->assertStringEndsWith(
					$expected_file,
					$log_data,
				);
			}
		} else {
			// there should be no file there
			$this->assertEquals(
				$log_file,
				''
			);
		}
		// ** ECHO ON
		$log_string =  $log->printErrorMsg();
		// * print
		if (!empty($options['debug_all']) && !empty($options['echo_all'])) {
			// print $log->printErrorMsg() . "\n";
			// echo string must start with
			$this->assertStringStartsWith(
				$expected_string_start,
				$log_string
			);
			// echo string must containt
			$this->assertStringContainsString(
				$expected_string_contains,
				$log_string
			);
			// TODO: as printing directly is not really done anymore tests below are todo
			// * get error msg (getErrorMsg)
			// * merge error msg (mergeErrors)
			// * print merged (printErrorMsg)
			// * reset A (resetErrorMsg)
			// * reset ALL (resetErrorMsg)
		} else {
			$this->assertEquals(
				$log_string,
				''
			);
		}
	}

	// TODO: setLogUniqueId/getLogUniqueId

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logUniqueIdProvider(): array
	{
		return [
			'option set' => [
				'option' => true,
				'override' => false,
			],
			'direct set' => [
				'option' => false,
				'override' => false,
			],
			'override set' => [
				'option' => false,
				'override' => true,
			],
			'option and override set' => [
				'option' => false,
				'override' => true,
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setLogUniqueId
	 * @covers ::getLogUniqueId
	 * @dataProvider logUniqueIdProvider
	 * @testdox per run log id set test: option: $option, override: $override [$_dataName]
	 *
	 * @param  bool $option
	 * @param  bool $override
	 * @return void
	 */
	public function testLogUniqueId(bool $option, bool $override): void
	{
		if ($option === true) {
			$log = new \CoreLibs\Debug\LoggingLegacy([
				'file_id' => 'testLogUniqueId',
				'log_folder' => self::LOG_FOLDER,
				'per_run' => $option
			]);
		} else {
			$log = new \CoreLibs\Debug\LoggingLegacy([
				'file_id' => 'testLogUniqueId',
				'log_folder' => self::LOG_FOLDER
			]);
			$log->setLogUniqueId();
		}
		$per_run_id = $log->getLogUniqueId();
		$this->assertMatchesRegularExpression(
			"/^\d{4}-\d{2}-\d{2}_\d{6}_U_[a-z0-9]{8}$/",
			$per_run_id,
			'assert per log run id 1st'
		);
		if ($override === true) {
			$log->setLogUniqueId(true);
			$per_run_id_2nd = $log->getLogUniqueId();
			$this->assertMatchesRegularExpression(
				"/^\d{4}-\d{2}-\d{2}_\d{6}_U_[a-z0-9]{8}$/",
				$per_run_id_2nd,
				'assert per log run id 2nd'
			);
			$this->assertNotEquals(
				$per_run_id,
				$per_run_id_2nd,
				'1st and 2nd don\'t match'
			);
		}
	}
}

// __END__
