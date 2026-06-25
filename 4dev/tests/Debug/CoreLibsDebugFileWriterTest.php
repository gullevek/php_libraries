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
	 * @testdox fsetFolder test correct return code
	 *
	 * @return void
	 */
	public function testFsetFolder(): void
	{
		// check the following
		// - valid folder, writeable
		// - valid folder, not writeable
		// - invalid folder (eg file)
		// - invalid folder name (eg contains spaces), must match ^[\w\-\/]+

		// if we have no /tmp/ folder, we cannot test this, so skip the test
		if (!is_dir('/tmp')) {
			$this->markTestSkipped('No /tmp folder found, cannot test fsetFolder');
		}
		// TEST 3:
		// create a file in /tmp/ to test invalid folder (eg file)
		$test_file = '/tmp/somefile.txt';
		if (!is_file($test_file)) {
			touch($test_file);
		}
		$this->assertEquals(
			false,
			\CoreLibs\Debug\FileWriter::fsetFolder($test_file)
		);
		// TEST 4:
		// test invalid folder name (eg contains spaces), must match ^[\w\-\/]+
		$this->assertEquals(
			false,
			\CoreLibs\Debug\FileWriter::fsetFolder('some name')
		);
		// TEST 1:
		// create a folder in /tmp/ to test valid folder, writeable
		$test_folder = '/tmp/somefolder';
		if (!is_dir($test_folder)) {
			mkdir($test_folder);
		}
		$this->assertEquals(
			true,
			\CoreLibs\Debug\FileWriter::fsetFolder($test_folder)
		);
		// TEST 2:
		// create a folder in /tmp/ to test valid folder, not writeable
		$test_folder = '/tmp/somefolder2';
		if (!is_dir($test_folder)) {
			mkdir($test_folder);
		}
		// remove all write permissions for the folder
		chmod($test_folder, 0555);
		$this->assertEquals(
			false,
			\CoreLibs\Debug\FileWriter::fsetFolder($test_folder)
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
