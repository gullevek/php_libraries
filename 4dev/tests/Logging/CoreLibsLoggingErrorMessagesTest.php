<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Logging\Logger\Level;

/**
 * Test class for Logging
 * @coversDefaultClass \CoreLibs\Logging\ErrorMessages
 * @testdox \CoreLibs\Logging\ErrorMEssages method tests
 */
final class CoreLibsLoggingErrorMessagesTest extends TestCase
{
	private const LOG_FOLDER = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;

	/**
	 * tear down and remove log data
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		array_map('unlink', glob(self::LOG_FOLDER . '*.log'));
	}

	/**
	 * for checking level only
	 *
	 * @return array
	 */
	public function providerErrorMessageLevel(): array
	{
		return [
			'ok' => [
				'level' => 'ok',
				'str' => 'OK',
				'expected' => 'ok',
			],
			'info' => [
				'level' => 'info',
				'str' => 'INFO',
				'expected' => 'info',
			],
			'notice' => [
				'level' => 'notice',
				'str' => 'NOTICE',
				'expected' => 'notice',
			],
			'warn' => [
				'level' => 'warn',
				'str' => 'WARN',
				'expected' => 'warn'
			],
			'warning' => [
				'level' => 'warning',
				'str' => 'WARN',
				'expected' => 'warn'
			],
			'error' => [
				'level' => 'error',
				'str' => 'ERROR',
				'expected' => 'error'
			],
			'abort' => [
				'level' => 'abort',
				'str' => 'ABORT',
				'expected' => 'abort'
			],
			'crash' => [
				'level' => 'crash',
				'str' => 'CRASH',
				'expected' => 'crash'
			],
			'wrong level' => [
				'level' => 'wrong',
				'str' => 'WRONG',
				'expected' => 'unknown'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerErrorMessageLevel
	 * @testdox error message level: $level will be $expected [$_dataName]
	 *
	 * @param  string $level
	 * @param  string $str
	 * @param  string  $expected
	 * @return void
	 */
	public function testErrorMessageLevelOk(string $level, string $str, string $expected): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testErrorMessagesLevelOk',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Error,
		]);
		$em = new \CoreLibs\Logging\ErrorMessage($log);
		$em->setMessage(
			$level,
			$str
		);
		$this->assertEquals(
			[
				'level' => $expected,
				'str' => $str,
				'id' => '',
				'target' => '',
				'target_style' => '',
				'highlight' => [],
			],
			$em->getLastErrorMsg()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Test of all methods for n messages [$_dataName]
	 *
	 * @return void
	 */
	public function testErrorMessageOk(): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testErrorMessagesOk',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Error
		]);
		$em = new \CoreLibs\Logging\ErrorMessage($log);
		$em->setErrorMsg(
			'100',
			'info',
			'INFO MESSAGE'
		);

		$this->assertEquals(
			[
				'id' => '100',
				'level' => 'info',
				'str' => 'INFO MESSAGE',
				'target' => '',
				'target_style' => '',
				'highlight' => [],
			],
			$em->getLastErrorMsg()
		);
		$this->assertEquals(
			['100'],
			$em->getErrorIds()
		);
		$this->assertEquals(
			[
				[
					'id' => '100',
					'level' => 'info',
					'str' => 'INFO MESSAGE',
					'target' => '',
					'target_style' => '',
					'highlight' => [],
				]
			],
			$em->getErrorMsg()
		);

		$em->setErrorMsg(
			'200',
			'error',
			'ERROR MESSAGE'
		);
		$this->assertEquals(
			[
				'id' => '200',
				'level' => 'error',
				'str' => 'ERROR MESSAGE',
				'target' => '',
				'target_style' => '',
				'highlight' => [],
			],
			$em->getLastErrorMsg()
		);
		$this->assertEquals(
			['100', '200'],
			$em->getErrorIds()
		);
		$this->assertEquals(
			[
				[
					'id' => '100',
					'level' => 'info',
					'str' => 'INFO MESSAGE',
					'target' => '',
					'target_style' => '',
					'highlight' => [],
				],
				[
					'id' => '200',
					'level' => 'error',
					'str' => 'ERROR MESSAGE',
					'target' => '',
					'target_style' => '',
					'highlight' => [],
				]
			],
			$em->getErrorMsg()
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerErrorMessageLog(): array
	{
		return [
			'error, not logged' => [
				'id' => '200',
				'level' => 'error',
				'str' => 'ERROR MESSAGE',
				'message' => null,
				'log_error' => null,
				'expected' => '<ERROR> ERROR MESSAGE',
			],
			'error, logged' => [
				'id' => '200',
				'level' => 'error',
				'str' => 'ERROR MESSAGE',
				'message' => null,
				'log_error' => true,
				'expected' => '<ERROR> ERROR MESSAGE',
			],
			'error, logged, message' => [
				'id' => '200',
				'level' => 'error',
				'str' => 'ERROR MESSAGE',
				'message' => 'OTHER ERROR MESSAGE',
				'log_error' => true,
				'expected' => '<ERROR> OTHER ERROR MESSAGE',
			],
			'notice' => [
				'id' => '100',
				'level' => 'notice',
				'str' => 'NOTICE MESSAGE',
				'message' => null,
				'log_error' => null,
				'expected' => '<NOTICE> NOTICE MESSAGE',
			],
			'notice, message' => [
				'id' => '100',
				'level' => 'notice',
				'str' => 'NOTICE MESSAGE',
				'message' => 'OTHER NOTICE MESSAGE',
				'log_error' => null,
				'expected' => '<NOTICE> OTHER NOTICE MESSAGE',
			],
			'crash' => [
				'id' => '300',
				'level' => 'crash',
				'str' => 'CRASH MESSAGE',
				'message' => null,
				'log_error' => null,
				'expected' => '<ALERT> CRASH MESSAGE',
			],
			'crash, message' => [
				'id' => '300',
				'level' => 'crash',
				'str' => 'CRASH MESSAGE',
				'message' => 'OTHER CRASH MESSAGE',
				'log_error' => null,
				'expected' => '<ALERT> OTHER CRASH MESSAGE',
			],
			'abort' => [
				'id' => '200',
				'level' => 'abort',
				'str' => 'ABORT MESSAGE',
				'message' => null,
				'log_error' => null,
				'expected' => '<CRITICAL> ABORT MESSAGE',
			],
			'abort, message' => [
				'id' => '200',
				'level' => 'abort',
				'str' => 'ABORT MESSAGE',
				'message' => 'OTHER ABORT MESSAGE',
				'log_error' => null,
				'expected' => '<CRITICAL> OTHER ABORT MESSAGE',
			],
			'unknown' => [
				'id' => '400',
				'level' => 'wrong level',
				'str' => 'WRONG LEVEL MESSAGE',
				'message' => null,
				'log_error' => null,
				'expected' => '<EMERGENCY> WRONG LEVEL MESSAGE',
			],
			'unknown, message' => [
				'id' => '400',
				'level' => 'wrong level',
				'str' => 'WRONG LEVEL MESSAGE',
				'message' => 'OTHER WRONG LEVEL MESSAGE',
				'log_error' => null,
				'expected' => '<EMERGENCY> OTHER WRONG LEVEL MESSAGE',
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerErrorMessageLog
	 * @testdox Test Log writing with log level Error [$_dataName]
	 *
	 * @param  string      $id
	 * @param  string      $level
	 * @param  string      $str
	 * @param  string|null $message
	 * @param  bool|null   $log_error
	 * @param  string      $expected
	 * @return void
	 */
	public function testErrorMessageLogErrorLevel(
		string $id,
		string $level,
		string $str,
		?string $message,
		?bool $log_error,
		string $expected
	): void {
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testErrorMessagesLogError',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Notice,
			'log_per_run' => true
		]);
		$em = new \CoreLibs\Logging\ErrorMessage($log);
		$em->setErrorMsg(
			$id,
			$level,
			$str,
			message: $message,
			log_error: $log_error
		);
		$file_content = '';
		if (is_file($log->getLogFolder() . $log->getLogFile())) {
			$file_content = file_get_contents(
				$log->getLogFolder() . $log->getLogFile()
			) ?: '';
		}
		// if error, if null or false, it will not be logged
		if ($level == 'error' && ($log_error === null || $log_error === false)) {
			$this->assertStringNotContainsString(
				$expected,
				$file_content
			);
		} else {
			$this->assertStringContainsString(
				$expected,
				$file_content
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider providerErrorMessageLog
	 * @testdox Test Log writing with log Level Debug [$_dataName]
	 *
	 * @param  string      $id
	 * @param  string      $level
	 * @param  string      $str
	 * @param  string|null $message
	 * @param  bool|null   $log_error
	 * @param  string      $expected
	 * @return void
	 */
	public function testErrorMessageLogErrorDebug(
		string $id,
		string $level,
		string $str,
		?string $message,
		?bool $log_error,
		string $expected
	): void {
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testErrorMessagesLogDebug',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Debug,
			'log_per_run' => true
		]);
		$em = new \CoreLibs\Logging\ErrorMessage($log);
		$em->setErrorMsg(
			$id,
			$level,
			$str,
			message: $message,
			log_error: $log_error
		);
		$file_content = '';
		if (is_file($log->getLogFolder() . $log->getLogFile())) {
			$file_content = file_get_contents(
				$log->getLogFolder() . $log->getLogFile()
			) ?: '';
		}
		// if error, and log is debug level, only explicit false are not logged
		if ($level == 'error' && $log_error === false) {
			$this->assertStringNotContainsString(
				$expected,
				$file_content
			);
		} else {
			$this->assertStringContainsString(
				$expected,
				$file_content
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Test jump target set and reporting
	 *
	 * @return void
	 */
	public function testJumpTarget(): void
	{
		$log = new \CoreLibs\Logging\Logging([
			'log_file_id' => 'testErrorMessagesLogDebug',
			'log_folder' => self::LOG_FOLDER,
			'log_level' => Level::Debug,
			'log_per_run' => true
		]);
		$em = new \CoreLibs\Logging\ErrorMessage($log);
		$em->setJumpTarget(
			'target-f',
			'Target text'
		);
		$this->assertEquals(
			[
				'target-f' => 'Target text'
			],
			$em->getJumpTarget()
		);
		// set same target, keep as before
		$em->setJumpTarget(
			'target-f',
			'Other text'
		);
		$this->assertEquals(
			[
				'target-f' => 'Target text'
			],
			$em->getJumpTarget()
		);
		// add new now two messages
		$em->setJumpTarget(
			'target-s',
			'More text'
		);
		$this->assertEquals(
			[
				'target-f' => 'Target text',
				'target-s' => 'More text'
			],
			$em->getJumpTarget()
		);
		// add empty info
		$em->setJumpTarget(
			'target-e',
			''
		);
		$this->assertEquals(
			[
				'target-f' => 'Target text',
				'target-s' => 'More text',
				'target-e' => 'Jump to: target-e'
			],
			$em->getJumpTarget()
		);
	}
}

// __END__
