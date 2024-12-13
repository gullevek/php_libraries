<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Logging\Logger\Level;

/**
 * Test class for Logging
 * @coversDefaultClass \CoreLibs\Logging\ErrorMessages
 * @testdox \CoreLibs\Logging\ErrorMessages method tests
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
			'success' => [
				'level' => 'success',
				'str' => 'SUCCESS',
				'expected' => 'success',
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
				'log_warning' => null,
				'expected' => '<ERROR> ERROR MESSAGE',
			],
			'error, logged' => [
				'id' => '200',
				'level' => 'error',
				'str' => 'ERROR MESSAGE',
				'message' => null,
				'log_error' => true,
				'log_warning' => null,
				'expected' => '<ERROR> ERROR MESSAGE',
			],
			'error, logged, message' => [
				'id' => '200',
				'level' => 'error',
				'str' => 'ERROR MESSAGE',
				'message' => 'OTHER ERROR MESSAGE',
				'log_error' => true,
				'log_warning' => null,
				'expected' => '<ERROR> OTHER ERROR MESSAGE',
			],
			'warn, not logged' => [
				'id' => '300',
				'level' => 'warn',
				'str' => 'WARNING MESSAGE',
				'message' => null,
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<WARNING> WARNING MESSAGE',
			],
			'warn, logged' => [
				'id' => '300',
				'level' => 'warn',
				'str' => 'WARNING MESSAGE',
				'message' => null,
				'log_error' => null,
				'log_warning' => true,
				'expected' => '<WARNING> WARNING MESSAGE',
			],
			'warn, logged, message' => [
				'id' => '300',
				'level' => 'warn',
				'str' => 'WARNING MESSAGE',
				'message' => 'OTHER WARNING MESSAGE',
				'log_error' => null,
				'log_warning' => true,
				'expected' => '<WARNING> OTHER WARNING MESSAGE',
			],
			'notice' => [
				'id' => '100',
				'level' => 'notice',
				'str' => 'NOTICE MESSAGE',
				'message' => null,
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<NOTICE> NOTICE MESSAGE',
			],
			'notice, message' => [
				'id' => '100',
				'level' => 'notice',
				'str' => 'NOTICE MESSAGE',
				'message' => 'OTHER NOTICE MESSAGE',
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<NOTICE> OTHER NOTICE MESSAGE',
			],
			'crash' => [
				'id' => '300',
				'level' => 'crash',
				'str' => 'CRASH MESSAGE',
				'message' => null,
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<ALERT> CRASH MESSAGE',
			],
			'crash, message' => [
				'id' => '300',
				'level' => 'crash',
				'str' => 'CRASH MESSAGE',
				'message' => 'OTHER CRASH MESSAGE',
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<ALERT> OTHER CRASH MESSAGE',
			],
			'abort' => [
				'id' => '200',
				'level' => 'abort',
				'str' => 'ABORT MESSAGE',
				'message' => null,
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<CRITICAL> ABORT MESSAGE',
			],
			'abort, message' => [
				'id' => '200',
				'level' => 'abort',
				'str' => 'ABORT MESSAGE',
				'message' => 'OTHER ABORT MESSAGE',
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<CRITICAL> OTHER ABORT MESSAGE',
			],
			'unknown' => [
				'id' => '400',
				'level' => 'wrong level',
				'str' => 'WRONG LEVEL MESSAGE',
				'message' => null,
				'log_error' => null,
				'log_warning' => null,
				'expected' => '<EMERGENCY> WRONG LEVEL MESSAGE',
			],
			'unknown, message' => [
				'id' => '400',
				'level' => 'wrong level',
				'str' => 'WRONG LEVEL MESSAGE',
				'message' => 'OTHER WRONG LEVEL MESSAGE',
				'log_error' => null,
				'log_warning' => null,
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
	 * @param  bool|null   $log_warning
	 * @param  string      $expected
	 * @return void
	 */
	public function testErrorMessageLogErrorLevel(
		string $id,
		string $level,
		string $str,
		?string $message,
		?bool $log_error,
		?bool $log_warning,
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
			log_error: $log_error,
			log_warning: $log_warning
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
		} elseif ($level == 'warn' && ($log_warning === null || $log_warning === false)) {
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
	 * @param  bool|null   $log_warning
	 * @param  string      $expected
	 * @return void
	 */
	public function testErrorMessageLogErrorDebug(
		string $id,
		string $level,
		string $str,
		?string $message,
		?bool $log_error,
		?bool $log_warning,
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
			log_error: $log_error,
			log_warning: $log_warning
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
		} elseif ($level == 'warn' && $log_warning === false) {
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
				['target' => 'target-f', 'info' => 'Target text', 'level' => 'error']
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
				['target' => 'target-f', 'info' => 'Target text', 'level' => 'error']
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
				['target' => 'target-f', 'info' => 'Target text', 'level' => 'error'],
				['target' => 'target-s', 'info' => 'More text', 'level' => 'error'],
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
				['target' => 'target-f', 'info' => 'Target text', 'level' => 'error'],
				['target' => 'target-s', 'info' => 'More text', 'level' => 'error'],
				['target' => 'target-e', 'info' => 'Jump to: target-e', 'level' => 'error'],
			],
			$em->getJumpTarget()
		);
		// add through message
		$em->setErrorMsg('E-101', 'abort', 'Abort message', jump_target:[
			'target' => 'abort-target',
			'info' => 'Abort error'
		]);
		$this->assertEquals(
			[
				['target' => 'target-f', 'info' => 'Target text', 'level' => 'error'],
				['target' => 'target-s', 'info' => 'More text', 'level' => 'error'],
				['target' => 'target-e', 'info' => 'Jump to: target-e', 'level' => 'error'],
				['target' => 'abort-target', 'info' => 'Abort error', 'level' => 'abort'],
			],
			$em->getJumpTarget()
		);
	}
}

// __END__
