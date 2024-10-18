<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Debug\FileWriter
 * @coversDefaultClass \CoreLibs\Debug\FileWriter
 * @testdox \CoreLibs\Debug\FileWriter method tests
 */
final class CoreLibsDebugFileWriterTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function fsetFolderProvider(): array
	{
		return [
			'valid log folder name' => [
				0 => '/tmp/',
				1 => true,
			],
			'invalid log folder name' => [
				0 => 'some name',
				1 => false,
			],
			'not writeable log folder name' => [
				0 => '/opt',
				1 => false,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function fsetFilenameProvider(): array
	{
		return [
			'valid log file name' => [
				0 => 'some_valid_name.log',
				1 => true,
			],
			'file name contains path' => [
				0 => 'log/debug.log',
				1 => false,
			],
			'invalid log file name' => [
				0 => 'invalid name',
				1 => false,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function fdebugProvider(): array
	{
		return [
			'debug with default enter' => [
				0 => 'test string',
				1 => null,
				2 => true,
				3 => 'test string' . "\n"
			],
			'debug with no enter' => [
				0 => 'test string',
				1 => false,
				2 => true,
				3 => 'test string'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider fsetFolderProvider
	 * @testdox fsetFolder $input will match $expected [$_dataName]
	 *
	 * @param string $input
	 * @param boolean $expected
	 * @return void
	 */
	public function testFsetFolder(string $input, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Debug\FileWriter::fsetFolder($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider fsetFilenameProvider
	 * @testdox fsetFilename $input will match $expected [$_dataName]
	 *
	 * @param string $input
	 * @param boolean $expected
	 * @return void
	 */
	public function testFsetFilename(string $input, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Debug\FileWriter::fsetFilename($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider fdebugProvider
	 * @testdox fdebug write $input with enter $enter and will be $expected and written $expected_log [$_dataName]
	 *
	 * @param string $input
	 * @param boolean|null $enter
	 * @param boolean $expected
	 * @param string $expected_log
	 * @return void
	 */
	public function testFdebug(string $input, ?bool $enter, bool $expected, string $expected_log): void
	{
		// set debug log folder
		$file = 'FileWriterTest.log';
		$folder = '/tmp';
		$debug_file = $folder . DIRECTORY_SEPARATOR . $file;
		$valid_folder = \CoreLibs\Debug\FileWriter::fsetFolder($folder);
		$this->assertTrue(
			$valid_folder
		);
		$valid_file = \CoreLibs\Debug\FileWriter::fsetFilename($file);
		$this->assertTrue(
			$valid_file
		);
		// write the log line
		if ($enter === null) {
			$this->assertEquals(
				$expected,
				\CoreLibs\Debug\FileWriter::fdebug($input)
			);
		} else {
			$this->assertEquals(
				$expected,
				\CoreLibs\Debug\FileWriter::fdebug($input, $enter)
			);
		}
		if (is_file($debug_file)) {
			// open file, load data, compre to expected_log
			$log_data = file_get_contents($debug_file);
			if ($log_data === false) {
				$this->fail('fdebug file not readable or not data: ' . $debug_file);
			}
			$this->assertStringEndsWith(
				$expected_log,
				$log_data
			);
			unlink($debug_file);
		} else {
			$this->fail('fdebug file not found: ' . $debug_file);
		}
	}
}

// __END__
