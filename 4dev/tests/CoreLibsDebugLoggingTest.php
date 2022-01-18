<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Debug\Logging
 * @coversDefaultClass \CoreLibs\Debug\Logging
 * @testdox \CoreLibs\Debug\Logging method tests
 */
final class CoreLibsDebugLoggingTest extends TestCase
{
	public $log;

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
					'log_folder' => '/tmp/',
					'debug_all' => false,
					'print_all' => false,
				],
				[
					'constant' => [
						'BASE' => '/tmp',
						'LOG' => '/'
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

	// init tests
	// - __construct call with options

	/**
	 * Undocumented function
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
			$this->log->getSetting('debug_all')
		);
		$this->assertEquals(
			$expected['print_all'],
			$this->log->getSetting('print_all')
		);
		print "LOG: " . $this->log->getSetting('log_folder') . "\n";
		print "DEBUG: " . $this->log->getSetting('debug_all') . "\n";
		print "PRINT: " . $this->log->getSetting('print_all') . "\n";
	}

	// setting tests
	// - basicSetLogId
	// - getLogId
	// - setLogLevelAll
	// - getLogLevelAll
	// - debugFor
	// - setLogLevel
	// - getLogLevel
	// - setLogPer
	// - getLogPer
	// debug tets
	// - pr
	// - debug
	// - mergeErrors
	// - printErrorMsg
	// - resetErrorMsg
	// - getErrorMsg
}

// __END__
