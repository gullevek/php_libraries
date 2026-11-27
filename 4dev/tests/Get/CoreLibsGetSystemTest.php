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
	/** @var array<string,mixed> backup of $_SERVER, restored after each test */
	private array $server_backup = [];

	/**
	 * backup $_SERVER, tests in here change it
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		$this->server_backup = $_SERVER;
	}

	/**
	 * restore $_SERVER
	 *
	 * @return void
	 */
	protected function tearDown(): void
	{
		$_SERVER = $this->server_backup;
	}

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
				2 => 0,
			],
			'override set no port' => [
				0 => 'foo.org',
				1 => 'foo.org',
				2 => 80
			],
			'override set with port' => [
				0 => 'foo.org:443',
				1 => 'foo.org',
				2 => 443
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
			// 0: input
			// 1: expected default/WITH_EXTENSION
			// 2: expected NO_EXTENSION
			// 3: expected FULL_PATH, if first and last character are / use regex
			'original set' => [
				0 => null,
				1 => 'phpunit',
				2 => 'phpunit',
				// NOTE: this can change, so it is a regex check
				3 => "/^(\/?.*\/?)?(vendor\/bin|tools)\/phpunit$/",
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
	 * @param int $expected_port
	 * @return void
	 */
	public function testGetHostNanme(?string $input, string $expected_host, int $expected_port): void
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
			$host,
			'failed expected host assert'
		);
		$this->assertEquals(
			$expected_port,
			$port,
			'faile expected port assert'
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
			\CoreLibs\Get\System::getPageName(),
			'failed default assert'
		);
		$this->assertEquals(
			$expected_0,
			\CoreLibs\Get\System::getPageName(\CoreLibs\Get\System::WITH_EXTENSION),
			'failed WITH_EXTESION assert'
		);
		$this->assertEquals(
			$expected_1,
			\CoreLibs\Get\System::getPageName(\CoreLibs\Get\System::NO_EXTENSION),
			'failed NO_EXTENSION assert'
		);
		// FULL PATH check can be equals or regex
		$page_name_full_path = \CoreLibs\Get\System::getPageName(\CoreLibs\Get\System::FULL_PATH);
		if (
			substr($expected_2, 0, 1) == '/' &&
			substr($expected_2, -1, 1) == '/'
		) {
			// this is regex
			$this->assertMatchesRegularExpression(
				$expected_2,
				$page_name_full_path,
				'failed FULL_PATH assert regex'
			);
		} else {
			$this->assertEquals(
				$expected_2,
				$page_name_full_path,
				'failed FULL_PATH assert equals'
			);
		}
	}

	/**
	 * Provider for getIpAddresses
	 * CLI has no REMOTE_ADDR, so the $_SERVER values are faked
	 *
	 * @return array<string,array{0:array<string,string>,1:array<string,string|array<string>>}>
	 */
	public function getIpAddressesProvider(): array
	{
		return [
			// 0: $_SERVER entries to set
			// 1: expected return
			'nothing set' => [
				[],
				[],
			],
			'only REMOTE_ADDR' => [
				['REMOTE_ADDR' => '192.168.0.10'],
				['REMOTE_ADDR' => '192.168.0.10'],
			],
			'only HTTP_X_FORWARDED_FOR' => [
				['HTTP_X_FORWARDED_FOR' => '10.0.0.1'],
				['HTTP_X_FORWARDED_FOR' => '10.0.0.1'],
			],
			'only CLIENT_IP' => [
				['CLIENT_IP' => '172.16.0.5'],
				['CLIENT_IP' => '172.16.0.5'],
			],
			'IPv6 REMOTE_ADDR' => [
				['REMOTE_ADDR' => '2001:db8::1'],
				['REMOTE_ADDR' => '2001:db8::1'],
			],
			'all set, return order is fixed' => [
				[
					'CLIENT_IP' => '172.16.0.5',
					'HTTP_X_FORWARDED_FOR' => '10.0.0.1',
					'REMOTE_ADDR' => '192.168.0.10',
				],
				[
					'REMOTE_ADDR' => '192.168.0.10',
					'HTTP_X_FORWARDED_FOR' => '10.0.0.1',
					'CLIENT_IP' => '172.16.0.5',
				],
			],
			'forwarded for list adds -array entry' => [
				[
					'REMOTE_ADDR' => '192.168.0.10',
					'HTTP_X_FORWARDED_FOR' => '10.0.0.1,10.0.0.2,10.0.0.3',
				],
				[
					'REMOTE_ADDR' => '192.168.0.10',
					'HTTP_X_FORWARDED_FOR' => '10.0.0.1,10.0.0.2,10.0.0.3',
					'HTTP_X_FORWARDED_FOR-array' => ['10.0.0.1', '10.0.0.2', '10.0.0.3'],
				],
			],
			'list with spaces is not trimmed' => [
				['HTTP_X_FORWARDED_FOR' => '10.0.0.1, 10.0.0.2'],
				[
					'HTTP_X_FORWARDED_FOR' => '10.0.0.1, 10.0.0.2',
					'HTTP_X_FORWARDED_FOR-array' => ['10.0.0.1', ' 10.0.0.2'],
				],
			],
			'empty values are skipped' => [
				[
					'REMOTE_ADDR' => '',
					'HTTP_X_FORWARDED_FOR' => '',
					'CLIENT_IP' => '172.16.0.5',
				],
				['CLIENT_IP' => '172.16.0.5'],
			],
			'other $_SERVER keys are ignored' => [
				[
					'REMOTE_ADDR' => '192.168.0.10',
					'HTTP_CLIENT_IP' => '1.2.3.4',
					'HTTP_X_REAL_IP' => '5.6.7.8',
				],
				['REMOTE_ADDR' => '192.168.0.10'],
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getIpAddresses
	 * @dataProvider getIpAddressesProvider
	 * @testdox getIpAddresses check [$_dataName]
	 *
	 * @param  array<string,string>                $server   $_SERVER entries to set
	 * @param  array<string,string|array<string>> $expected expected return
	 * @return void
	 */
	public function testGetIpAddresses(array $server, array $expected): void
	{
		// CLI has no REMOTE_ADDR, start clean and fake the entries
		unset(
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_X_FORWARDED_FOR'],
			$_SERVER['CLIENT_IP']
		);
		foreach ($server as $key => $value) {
			$_SERVER[$key] = $value;
		}
		// assertSame so key order and types are checked too
		$this->assertSame(
			$expected,
			\CoreLibs\Get\System::getIpAddresses(),
			'failed getIpAddresses assert'
		);
	}
}

// __END__
