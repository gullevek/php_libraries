<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Logging\Logger\Level;
use CoreLibs\Logging\Logger\Flag;

// TODO: setLogPer test log file written matches pattern

/**
 * Test class for Logging
 * @coversDefaultClass \CoreLibs\Logging\Logging
 * @testdox \CoreLibs\Logging\Logging method tests
 */
final class CoreLibsLoggingLoggingTest extends TestCase
{
	private const LOG_FOLDER = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
	private const REGEX_BASE = "\[[\d\-\s\.:]+\]\s{1}" // date
		. "\[[\w\.]+(:\d+)?\]\s{1}" // host:port
		. "\[[\w\-\.\/]+:\d+\]\s{1}" // folder/file
		. "\[\w+\]\s{1}" // run id
		. "{[\w\\\\]+(::\w+)?}\s{1}"; // class

	public static function tearDownAfterClass(): void
	{
		array_map('unlink', glob(self::LOG_FOLDER . '*.log'));
	}

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
				'options' => [
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
					'log_file_id' => 'testClassInit',
				],
				'expected' => [
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'log_level' => Level::Debug,
					'log_file_id' => 'testClassInit',
				],
				'override' => [],
			],
			// -> deprecation warning, log_folder must be set
			'no log folder set' => [
				'options' => [
					'log_file_id' => 'testClassInit'
				],
				'expected' => [
					'log_folder' => getcwd() . DIRECTORY_SEPARATOR,
					'log_level' => Level::Debug,
					'log_file_id' => 'testClassInit',
				],
				'override' => []
			],
			// -> upcoming deprecated
			'file_id set but not log_file_id' => [
				'options' => [
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'file_id' => 'testClassInit',
				],
				'expected' => [
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'log_level' => Level::Debug,
					'log_file_id' => 'testClassInit',
				],
				'override' => [],
			],
			// both file_id and log_file_id set -> WARNING
			'file_id and log_file_id set' => [
				'options' => [
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'file_id' => 'testClassInit',
					'log_file_id' => 'testClassInit',
				],
				'expected' => [
					'log_folder' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
					'log_level' => Level::Debug,
					'log_file_id' => 'testClassInit',
				],
				'override' => [],
			],
			// no log file id set -> error,
			'nothing set' => [
				'options' => [],
				'expected' => [
					'log_folder' => getcwd() . DIRECTORY_SEPARATOR,
					'log_level' => Level::Debug,
					'log_file_id' => 'NOHOST-0_PHPUnit-TextUI-Command',
				],
				'override' => []
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
		// alert for log file id with globals
		if (!empty($override['constant']) && empty($options['log_file_id'])) {
			//
		}
		// alert for log file id and file id set
		if (
			!empty($options['log_file_id']) &&
			!empty($options['file_id'])
		) {
			set_error_handler(
				static function (int $errno, string $errstr): never {
					throw new \InvalidArgumentException($errstr, $errno);
				},
				E_USER_WARNING
			);
			$error_message = 'options: "file_id" is deprecated use: "log_file_id".';
			$this->expectExceptionMessage($error_message);
			$this->expectException(\InvalidArgumentException::class);
			set_error_handler(
				static function (int $errno, string $errstr): never {
					throw new \Exception($errstr, $errno);
				},
				E_USER_DEPRECATED
			);
			$this->expectException(\Exception::class);
			// $error_message = 'options: both log_file_id and log_id are set at the same time, will use log_file_id';
			// $this->expectExceptionMessage($error_message);
		}
		// empty log folder
		if (empty($override['constant']) && empty($options['log_folder'])) {
			$this->expectException(\InvalidArgumentException::class);
			$this->expectExceptionMessageMatches("/^Missing mandatory option: \"/");
		} elseif (empty($options['log_file_id']) && !empty($options['file_id'])) {
			// the deprecation message
			$deprecation_message = 'options: "file_id" is deprecated use: "log_file_id".';
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
		$log = new \CoreLibs\Logging\Logging($options);
		// reset error handler
		restore_error_handler();
		// check that settings match
		$this->assertEquals(
			$expected['log_folder'],
			$log->getLogFolder(),
			'log folder not matching'
		);
		$this->assertEquals(
			$expected['log_file_id'],
			$log->getLogFileId(),
			'log file id not matching'
		);
	}

	// test all setters/getters

	// setLoggingLevel
	// getLoggingLevel
	// loggingLevelIsDebug

	/**
	 * Undocumented function
	 *
	 * @covers ::setLoggingLevel
	 * @covers ::getLoggingLevel
	 * @covers ::loggingLevelIsDebug
	 * @testdox setLoggingLevel set/get checks
	 *
	 * @return void
	 */
	public function testSetLoggingLevel(): void
	{
		// valid that is not Debug
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLoggingLevel',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Info
		]);
		$this->assertEquals(
			Level::Info,
			$log->getLoggingLevel()
		);
		$this->assertFalse(
			$log->loggingLevelIsDebug()
		);
		// not set, should be debug]
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLoggingLevel',
			'log_folder' => self::LOG_FOLDER,
		]);
		$this->assertEquals(
			Level::Debug,
			$log->getLoggingLevel()
		);
		$this->assertTrue(
			$log->loggingLevelIsDebug()
		);
		// invalid, should be debug, will throw excpetion too
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Option: "log_level" is not of instance \CoreLibs\Logging\Logger\Level');
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLoggingLevel',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => 'I'
		]);
		$this->assertEquals(
			Level::Debug,
			$log->getLoggingLevel()
		);
		$this->assertTrue(
			$log->loggingLevelIsDebug()
		);
		// set valid, then change
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLoggingLevel',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Info
		]);
		$this->assertEquals(
			Level::Info,
			$log->getLoggingLevel()
		);
		$log->setLoggingLevel(Level::Notice);
		$this->assertEquals(
			Level::Notice,
			$log->getLoggingLevel()
		);
		// illegal logging level
		$this->expectException(\Psr\Log\InvalidArgumentException::class);
		$this->expectExceptionMessageMatches("/^Level \"NotGood\" is not defined, use one of: /");
		$log->setLoggingLevel('NotGood');
	}

	// setLogFileId
	// getLogFileId

	/**
	 * Undocumented function
	 *
	 * @covers ::setLogFileId
	 * @covers ::getLogFileId
	 * @testdox setLogFileId set/get checks
	 *
	 * @return void
	 */
	public function testLogFileId(): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testLogFileId',
			'log_folder' => self::LOG_FOLDER
		]);
		// bad, keep same
		$log->setLogFileId('$$##$%#$%&');
		$this->assertEquals(
			'testLogFileId',
			$log->getLogFileId()
		);
		// good, change
		$log->setLogFileId('validID');
		$this->assertEquals(
			'validID',
			$log->getLogFileId()
		);
		// invalid on init
		$this->expectException(\Psr\Log\InvalidArgumentException::class);
		$this->expectExceptionMessage('LogFileId: no log file id set');
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => '$$$"#"#$"#$',
			'log_folder' => self::LOG_FOLDER
		]);
	}

	// setLogUniqueId
	// getLogUniqueId

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
			$log = new \CoreLibs\Logging\Logging([
				'log_file_id' => 'testLogUniqueId',
				'log_folder' => self::LOG_FOLDER,
				'log_per_run' => $option
			]);
		} else {
			$log = new \CoreLibs\Logging\Logging([
				'log_file_id' => 'testLogUniqueId',
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

	// setLogDate
	// getLogDate

	/**
	 * Undocumented function
	 *
	 * @covers ::setLogDate
	 * @covers ::getLogDate
	 * @testdox setLogDate set/get checks
	 *
	 * @return void
	 */
	public function testSetLogDate(): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testLogFileId',
			'log_folder' => self::LOG_FOLDER,
			'log_per_date' => true,
		]);
		$this->assertEquals(
			date('Y-m-d'),
			$log->getLogDate()
		);
	}

	// setLogFlag
	// getLogFlag
	// unsetLogFlag
	// getLogFlags
	// Logger\Flag

	/**
	 * Undocumented function
	 *
	 * @covers Logger\Flag
	 * @testdox Logger\Flag enum test
	 *
	 * @return void
	 */
	public function testLoggerFlag(): void
	{
		// logger flags to check that they exist
		$flags = [
			'all_off' => 0,
			'per_run' => 1,
			'per_date' => 2,
			'per_group' => 4,
			'per_page' => 8,
			'per_class' => 16,
			'per_level' => 32,
		];
		// from int -> return value
		foreach ($flags as $name => $value) {
			$this->assertEquals(
				Flag::fromName($name),
				Flag::fromValue($value)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setLogFlag
	 * @covers ::getLogFlag
	 * @covers ::unsetLogFlag
	 * @covers ::getLogFlags
	 * @testdox setLogDate set/get checks
	 *
	 * @return void
	 */
	public function testSetLogFlag(): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLogFlag',
			'log_folder' => self::LOG_FOLDER,
		]);
		// set valid log flag
		$log->setLogFlag(Flag::per_run);
		$this->assertTrue(
			$log->getLogFlag(Flag::per_run)
		);
		// flags seum
		$this->assertEquals(
			Flag::per_run->value,
			$log->getLogFlags(),
		);
		// unset valid log flag
		$log->unsetLogFlag(Flag::per_run);
		$this->assertFalse(
			$log->getLogFlag(Flag::per_run)
		);
		// illegal Flags cannot be set, they will throw eerros onrun

		// test multi set and sum is equals set
		$log->setLogFlag(Flag::per_date);
		$log->setLogFlag(Flag::per_group);
		$this->assertEquals(
			Flag::per_date->value + Flag::per_group->value,
			$log->getLogFlags()
		);
	}

	// setLogFolder
	// getLogFolder

	/**
	 * Undocumented function
	 *
	 * @covers ::setLogFolder
	 * @covers ::getLogFolder
	 * @testdox setLogFolder set/get checks, init check
	 *
	 * @return void
	 */
	public function testSetLogFolder(): void
	{
		// set to good folder
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLogFolder',
			'log_folder' => self::LOG_FOLDER,
		]);
		$this->assertEquals(
			self::LOG_FOLDER,
			$log->getLogFolder()
		);
		// set to a good other folder
		$log->setLogFolder(DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR);
		$this->assertEquals(
			DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
			$log->getLogFolder()
		);
		// good other folder with missing trailing slash
		$log->setLogFolder(DIRECTORY_SEPARATOR . 'tmp');
		$this->assertEquals(
			DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
			$log->getLogFolder()
		);
		// a bad folder -> last good folder
		$log->setLogFolder('I-am-not existing');
		$this->assertEquals(
			DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
			$log->getLogFolder()
		);
		// init with a bad folder
		$this->expectException(\Psr\Log\InvalidArgumentException::class);
		$this->expectExceptionMessage('Folder: "I-am-bad" is not writeable for logging');
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLogFolderInvalid',
			'log_folder' => 'I-am-bad',
		]);
	}

	// getLogFile (no set, only correct after log run)

	// setLogMaxFileSize
	// getLogMaxFileSize

	/**
	 * Undocumented function
	 *
	 * @covers ::setLogMaxFileSize
	 * @covers ::getLogMaxFileSize
	 * @testdox setLogMaxFileSize set/get checks, init check
	 *
	 * @return void
	 */
	public function testSetLogMaxFileSize(): void
	{
		// init set to 0
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testSetLogMaxFileSize',
			'log_folder' => self::LOG_FOLDER,
		]);
		$this->assertEquals(
			0,
			$log->getLogMaxFileSize()
		);
		// set to new, valid size
		$file_size = 200 * 1024;
		$valid = $log->setLogMaxFileSize($file_size);
		$this->assertTrue($valid);
		$this->assertEquals(
			$file_size,
			$log->getLogMaxFileSize()
		);
		// invalid size, < 0, will be last and return false
		$valid = $log->setLogMaxFileSize(-1);
		$this->assertFalse($valid);
		$this->assertEquals(
			$file_size,
			$log->getLogMaxFileSize()
		);
		// too small (< MIN_LOG_MAX_FILESIZE)
		$valid = $log->setLogMaxFileSize($log::MIN_LOG_MAX_FILESIZE - 1);
		$this->assertFalse($valid);
		$this->assertEquals(
			$file_size,
			$log->getLogMaxFileSize()
		);
	}

	// getOption (option params)

	/**
	 * Undocumented function
	 *
	 * @covers ::getOption
	 * @testdox getOption checks
	 *
	 * @return void
	 */
	public function testGetOption(): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testGetOption',
			'log_folder' => self::LOG_FOLDER,
		]);
		$this->assertEquals(
			self::LOG_FOLDER,
			$log->getOption('log_folder')
		);
		// not found
		$this->assertNull(
			$log->getOption('I_do not exist')
		);
	}

	// test all logger functions
	// debug (special)
	// info
	// notice
	// warning
	// error
	// critical
	// alert
	// emergency

	/**
	 * Undocumented function
	 *
	 * @covers ::debug
	 * @testdox debug checks
	 *
	 * @return void
	 */
	public function testDebug(): void
	{
		// init logger
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testDebug',
			'log_folder' => self::LOG_FOLDER,
		]);
		// clean all data in folder first
		array_map('unlink', glob($log->getLogFolder() . $log->getLogFileId() . '*.log'));

		$group_id = 'G';
		$message = 'D';
		$log_status = $log->debug($group_id, $message);
		$this->assertTrue($log_status, 'debug write successful');
		$file_content = file_get_contents(
			$log->getLogFolder() . $log->getLogFile()
		) ?: '';
		$log_level = $log->getLoggingLevel()->getName();
		// [2023-05-30 15:51:39.36128800] [NOHOST:0]
		// [www/vendor/bin/phpunit] [7b9d0747] {PHPUnit\TextUI\Command}
		// <DEBUG:G> D
		$this->assertMatchesRegularExpression(
			"/" . self::REGEX_BASE
				. "<$log_level:$group_id>\s{1}" // log level / group id
				. "$message" // message
				. "/",
			$file_content
		);
	}

	public function providerLoggingLevelWrite(): array
	{
		return [
			'info' => [
				'message' => 'I',
				'file_id' => Level::Info->name,
				'level' => Level::Info
			],
			'notice' => [
				'message' => 'N',
				'file_id' => Level::Notice->name,
				'level' => Level::Notice
			],
			'warning' => [
				'message' => 'W',
				'file_id' => Level::Warning->name,
				'level' => Level::Warning
			],
			'error' => [
				'message' => 'E',
				'file_id' => Level::Error->name,
				'level' => Level::Error
			],
			'crticial' => [
				'message' => 'C',
				'file_id' => Level::Critical->name,
				'level' => Level::Critical
			],
			'alert' => [
				'message' => 'A',
				'file_id' => Level::Alert->name,
				'level' => Level::Alert
			],
			'emergency' => [
				'message' => 'Em',
				'file_id' => Level::Emergency->name,
				'level' => Level::Emergency
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::info
	 * @covers ::notice
	 * @covers ::warning
	 * @covers ::error
	 * @covers ::critical
	 * @covers ::alert
	 * @covers ::emergency
	 * @dataProvider providerLoggingLevelWrite
	 * @testdox logging level write checks for $level [$_dataName]
	 *
	 * @return void
	 */
	public function testLoggingLevelWrite(string $message, string $file_id, Level $level): void
	{
		// init logger
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'test' . $file_id,
			'log_folder' => self::LOG_FOLDER,
			'log_level' => $level,
		]);
		// clean all data in folder first
		array_map('unlink', glob($log->getLogFolder() . $log->getLogFileId() . '*.log'));

		switch ($level->value) {
			case 200:
				$log_status = $log->info($message);
				break;
			case 250:
				$log_status = $log->notice($message);
				break;
			case 300:
				$log_status = $log->warning($message);
				break;
			case 400:
				$log_status = $log->error($message);
				break;
			case 500:
				$log_status = $log->critical($message);
				break;
			case 550:
				$log_status = $log->alert($message);
				break;
			case 600:
				$log_status = $log->emergency($message);
				break;
		}
		$this->assertTrue($log_status, 'log write successful');
		$file_content = file_get_contents(
			$log->getLogFolder() . $log->getLogFile()
		) ?: '';
		$log_level = $log->getLoggingLevel()->getName();
		$this->assertMatchesRegularExpression(
			"/" . self::REGEX_BASE
				. "<$log_level>\s{1}" // log level / group id
				. "$message" // message
				. "/",
			$file_content,
			'log message regex'
		);
	}

	// deprecated calls check?
}

// __END__
