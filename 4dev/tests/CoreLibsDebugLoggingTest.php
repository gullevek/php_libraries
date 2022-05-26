<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

// TODO: setLogPer test log file written matches pattern

/**
 * Test class for Debug\Logging
 * @coversDefaultClass \CoreLibs\Debug\Logging
 * @testdox \CoreLibs\Debug\Logging method tests
 */
final class CoreLibsDebugLoggingTest extends TestCase
{
	public $log;

	/**
	 * test set for options BASIC
	 *
	 * 0: options
	 * - null for NOT set
	 * 1: expected
	 * 2: override
	 * override:
	 * - constant for COSNTANTS
	 * - global for _GLOBALS
	 *
	 * @return array
	 */
	public function optionsProvider(): array
	{
		return [
			'log folder set' => [
				[
					'log_folder' => '/tmp'
				],
				[
					'log_folder' => '/tmp/',
					'debug_all' => false,
					'print_all' => false,
				],
				[]
			],
			'nothing set' => [
				null,
				[
					'log_folder' => getcwd() . DIRECTORY_SEPARATOR,
					'debug_all' => false,
					'print_all' => false,
				],
				[]
			],
			'no options set, constant set' => [
				null,
				[
					'log_folder' => str_replace('/configs', '', __DIR__)
					. DIRECTORY_SEPARATOR . 'log/',
					'debug_all' => false,
					'print_all' => false,
				],
				[
					'constant' => [
						'BASE' => str_replace('/configs', '', __DIR__)
							. DIRECTORY_SEPARATOR,
						'LOG' => 'log/'
					]
				]
			],
			'standard test set' => [
				[
					'log_folder' => '/tmp',
					'debug_all' => true,
					'print_all' => true,
				],
				[
					'log_folder' => '/tmp/',
					'debug_all' => true,
					'print_all' => true,
				],
				[]
			]
		];
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
		return [
			'no log id set' => [
				null,
				[
					'log_file_id' => ''
				],
				[]
			],
			// set log id manually afterwards
			'set log id manually' => [
				null,
				[
					'log_file_id' => '',
					'set_log_file_id' => 'abc123',
				],
				[
					// set post launch
					'values' => [
						'log_file_id' => 'abc123'
					]
				]
			],
			// set log id from options
			'set log id via options' => [
				[
					'file_id' => 'abc456',
				],
				[
					'log_file_id' => 'abc456'
				],
				[]
			],
			// set log id from GLOBALS [DEPRECATED]
			'set log id via globals' => [
				null,
				[
					'log_file_id' => 'def123'
				],
				[
					'globals' => [
						'LOG_FILE_ID' => 'def123'
					]
				]
			],
			// set log id from CONSTANT [DEPRECATED]
			'set log id via constant' => [
				null,
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
				]
			],
			// invalid, keep previous set
			'invalid log id' => [
				[
					'file_id' => 'jkl456'
				],
				[
					'log_file_id' => 'jkl456',
					'set_log_file_id' => 'jkl456',
				],
				[
					'values' => [
						'log_file_id' => './#'
					]
				]
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logLevelAllProvider(): array
	{
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
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logLevelProvider(): array
	{
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
	 * Undocumented function
	 *
	 * @return array
	 */
	public function logPerProvider(): array
	{
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
	 * init logging class
	 *
	 * @dataProvider optionsProvider
	 * @testdox init test [$_dataName]
	 *
	 * @param array|null $options
	 * @param array $expected
	 * @param array $override
	 * @return void
	 */
	public function testClassInit(?array $options, array $expected, array $override): void
	{
		if (!empty($override['constant'])) {
			foreach ($override['constant'] as $var => $value) {
				define($var, $value);
			}
		}
		if ($options === null) {
			$this->log = new \CoreLibs\Debug\Logging();
		} else {
			$this->log = new \CoreLibs\Debug\Logging($options);
		}
		// check that settings match
		$this->assertEquals(
			$expected['log_folder'],
			$this->log->getSetting('log_folder')
		);
		$this->assertEquals(
			$expected['debug_all'],
			$this->log->getSetting('debug_output_all')
		);
		$this->assertEquals(
			$expected['print_all'],
			$this->log->getSetting('print_output_all')
		);
		// print "LOG: " . $this->log->getSetting('log_folder') . "\n";
		// print "DEBUG: " . $this->log->getSetting('debug_output_all') . "\n";
		// print "PRINT: " . $this->log->getSetting('print_output_all') . "\n";
	}

	/**
	 * test the setting and getting of LogId
	 *
	 * @covers ::setLogId
	 * @dataProvider logIdOptionsProvider
	 * @testdox log id set/get tests [$_dataName]
	 *
	 * @param array|null $options
	 * @param array $expected
	 * @param array $override
	 * @return void
	 */
	public function testLogId(?array $options, array $expected, array $override): void
	{
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
		if ($options === null) {
			$this->log = new \CoreLibs\Debug\Logging();
		} else {
			$this->log = new \CoreLibs\Debug\Logging($options);
		}
		// check current
		$this->assertEquals(
			$this->log->getLogId(),
			$expected['log_file_id']
		);
		// we need to override now too
		if (!empty($override['values'])) {
			// check if we have values, set them post and assert
			$this->log->basicSetLogId($override['values']['log_file_id']);
			$this->assertEquals(
				$this->log->getLogId(),
				$expected['set_log_file_id']
			);
		}
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
		$this->log = new \CoreLibs\Debug\Logging();
		// set and check
		$this->assertEquals(
			$this->log->setLogLevelAll($type, $flag),
			$expected_set
		);
		// get and check
		$this->assertEquals(
			$this->log->getLogLevelAll($type),
			$expected_get
		);
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
		$this->log = new \CoreLibs\Debug\Logging();
		// set
		$this->assertEquals(
			$this->log->setLogLevel($type, $flag, $debug_on),
			$expected_set
		);
		// get, if level is null compare to?
		$this->assertEquals(
			$this->log->getLogLevel($type, $flag, $level),
			$expected_get
		);
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
		$this->log = new \CoreLibs\Debug\Logging();
		// set and check
		$this->assertEquals(
			$this->log->setLogPer($type, $set),
			$expected_set
		);
		// get and check
		$this->assertEquals(
			$this->log->getLogPer($type),
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
		$this->log = new \CoreLibs\Debug\Logging();
		// set and check
		$this->assertEquals(
			$this->log->setGetLogPrintFileDate($input),
			$expected_set
		);
		$this->assertEquals(
			$this->log->setGetLogPrintFileDate(),
			$expected_get
		);
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
		$this->log = new \CoreLibs\Debug\Logging();
		$this->assertEquals(
			$this->log->prAr($input),
			$expected
		);
	}

	public function prBlProvider(): array
	{
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
	 * @textdox check prBl $input ($true/$false) is expected $false [$_dataName]
	 *
	 * @param  bool        $input
	 * @param  string|null $true
	 * @param  string|null $false
	 * @param  string      $expected
	 * @return void
	 */
	public function testPrBl(bool $input, ?string $true, ?string $false, string $expected): void
	{
		$this->log = new \CoreLibs\Debug\Logging();
		$return = '';
		if ($true === null && $false === null) {
			$return = $this->log->prBl($input);
		} elseif ($true !== null || $false !== null) {
			$return = $this->log->prBl($input, $true ?? '', $false ?? '');
		}
		$this->assertEquals(
			$expected,
			$return
		);
	}

	// from here are complex debug tests

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
		$this->log = new \CoreLibs\Debug\Logging($options);
		// * debug (A/B)
		// NULL check for strip/prefix
		$this->assertEquals(
			$this->log->debug(
				$debug_msg['level'],
				$debug_msg['string'],
				$debug_msg['strip'],
				$debug_msg['prefix'],
			),
			$expected_debug
		);
		// * if print check data in log file
		$log_file = $this->log->getLogFileName();
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
		$log_string =  $this->log->printErrorMsg();
		// * print
		if (!empty($options['debug_all']) && !empty($options['echo_all'])) {
			// print $this->log->printErrorMsg() . "\n";
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
}

// __END__
