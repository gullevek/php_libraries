<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Get\System
 * @coversDefaultClass \CoreLibs\Get\System
 * @testdox \CoreLibs\Get\System method tests
 */
final class CoreLibsGetSystemTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function fileUploadErrorMessageProvider(): array
	{
		return [
			'upload err init size' => [
				0 => UPLOAD_ERR_INI_SIZE,
				1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
			],
			'upload err from size' => [
				0 => UPLOAD_ERR_FORM_SIZE,
				1 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'
			],
			'upload err partial' => [
				0 => UPLOAD_ERR_PARTIAL,
				1 => 'The uploaded file was only partially uploaded'
			],
			'upload err no file' => [
				0 => UPLOAD_ERR_NO_FILE,
				1 => 'No file was uploaded'
			],
			'upload err no tmp dir' => [
				0 => UPLOAD_ERR_NO_TMP_DIR,
				1 => 'Missing a temporary folder'
			],
			'upload err cant write' => [
				0 => UPLOAD_ERR_CANT_WRITE,
				1 => 'Failed to write file to disk'
			],
			'upload err extension' => [
				0 => UPLOAD_ERR_EXTENSION,
				1 => 'File upload stopped by extension'
			],
			'unkown error' => [
				0 => 99999,
				1 => 'Unknown upload error'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function getHostNameProvider(): array
	{
		return [
			'original set' => [
				0 => null,
				1 => 'NOHOST',
				2 => 'NOPORT',
			],
			'override set no port' => [
				0 => 'foo.org',
				1 => 'foo.org',
				2 => '80'
			],
			'override set with port' => [
				0 => 'foo.org:443',
				1 => 'foo.org',
				2 => '443'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function getPageNameProvider(): array
	{
		return [
			'original set' => [
				0 => null, // input
				1 => 'phpunit',
				2 => 'phpunit',
				3 => 'www/vendor/bin/phpunit', // NOTE: this can change
			],
			'some path with extension' => [
				0 => '/some/path/to/file.txt',
				1 => 'file.txt',
				2 => 'file',
				3 => '/some/path/to/file.txt',
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::fileUploadErrorMessage
	 * @dataProvider fileUploadErrorMessageProvider
	 * @testdox fileUploadErrorMessage $input error matches $expected [$_dataName]
	 *
	 * @param integer $input
	 * @param string $expected
	 * @return void
	 */
	public function testFileUploadErrorMessage(int $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Get\System::fileUploadErrorMessage($input)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getHostName
	 * @dataProvider getHostNameProvider
	 * @testdox getHostName $input must match $expected_host:$expected_port [$_dataName]
	 *
	 * @param string|null $input
	 * @param string $expected_host
	 * @param string $expected_port
	 * @return void
	 */
	public function testGetHostNanme(?string $input, string $expected_host, string $expected_port): void
	{
		// print "HOSTNAME: " . $_SERVER['HTTP_HOST'] . "<br>";
		// print "SERVER: " . print_r($_SERVER, true) . "\n";
		// print "SELF: " . $_SERVER['PHP_SELF'] . "\n";
		if ($input !== null) {
			$_SERVER['HTTP_HOST'] = $input;
		}
		list ($host, $port) = \CoreLibs\Get\System::getHostName();
		$this->assertEquals(
			$expected_host,
			$host
		);
		$this->assertEquals(
			$expected_port,
			$port
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getPageName
	 * @dataProvider getPageNameProvider
	 * @testdox getPageName $input will match 0: $expected_0, 1: $expected_1, 2: $expected_2 [$_dataName]
	 *
	 * @param string|null $input
	 * @param string $expected_0 default with extension
	 * @param string $expected_1 no extension
	 * @param string $expected_2 full path
	 * @return void
	 */
	public function testGetPageName(?string $input, string $expected_0, string $expected_1, string $expected_2)
	{
		if ($input !== null) {
			$_SERVER['PHP_SELF'] = $input;
		}
		// default 0,
		$this->assertEquals(
			$expected_0,
			\CoreLibs\Get\System::getPageName()
		);
		$this->assertEquals(
			$expected_0,
			\CoreLibs\Get\System::getPageName(\CoreLibs\Get\System::WITH_EXTENSION)
		);
		$this->assertEquals(
			$expected_1,
			\CoreLibs\Get\System::getPageName(\CoreLibs\Get\System::NO_EXTENSION)
		);
		$this->assertEquals(
			$expected_2,
			\CoreLibs\Get\System::getPageName(\CoreLibs\Get\System::FULL_PATH)
		);
	}
}

// __END__
