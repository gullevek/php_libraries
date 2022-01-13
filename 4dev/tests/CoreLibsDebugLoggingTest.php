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
					'log_folder' => '/tmp/'
				],
				[]
			],
			'nothing set' => [
				null,
				[
					'log_folder' => getcwd() . DIRECTORY_SEPARATOR
				],
				[]
			],
			'no options set, constant set' => [
				null,
				[
					'log_folder' => '/tmp/'
				],
				[
					'constant' => [
						'BASE' => '/tmp',
						'LOG' => '/'
					]
				]
			],
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
		// print "LOG: " . $this->log->getSetting('log_folder') . "\n";
		$this->assertEquals(
			$expected['log_folder'],
			$this->log->getSetting('log_folder')
		);
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
