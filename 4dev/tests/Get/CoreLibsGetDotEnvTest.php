<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Get\DotEnv;

/**
 * Test class for ACL\Login
 * @coversDefaultClass \CoreLibs\Get\DotEnv
 * @testdox \CoreLibs\Get\DotEnv method tests
 */
final class CoreLibsGetDotEnvTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function envFileProvider(): array
	{
		$dot_env_content = [
			'SOMETHING' => 'A',
			'OTHER' => 'B IS B',
			'Complex' => 'A B \"D is F',
			'HAS_SPACE' => 'ABC',
			'HAS_COMMENT_QUOTES_SPACE' => 'Comment at end with quotes and space',
			'HAS_COMMENT_QUOTES_NO_SPACE' => 'Comment at end with quotes no space',
			'HAS_COMMENT_NO_QUOTES_SPACE' => 'Comment at end no quotes and space',
			'HAS_COMMENT_NO_QUOTES_NO_SPACE' => 'Comment at end no quotes no space',
			'COMMENT_IN_TEXT_QUOTES' => 'Foo bar # comment in here',
			'FAILURE' => 'ABC',
			'SIMPLEBOX' => 'A B  C',
			'TITLE' => '1',
			'FOO' => '1.2',
			'SOME.TEST' => 'Test Var',
			'SOME.LIVE' => 'Live Var',
			'A_TEST1' => 'foo',
			'A_TEST2' => '${TEST1:-bar}',
			'A_TEST3' => '${TEST4:-bar}',
			'A_TEST5' => 'null',
			'A_TEST6' => '${TEST5-bar}',
			'A_TEST7' => '${TEST6:-bar}',
			'B_TEST1' => 'foo',
			'B_TEST2' => '${TEST1:=bar}',
			'B_TEST3' => '${TEST4:=bar}',
			'B_TEST5' => 'null',
			'B_TEST6' => '${TEST5=bar}',
			'B_TEST7' => '${TEST6=bar}',
			'Test' => 'A',
			'TEST' => 'B',
			'LINE' => "ABC\nDEF",
			'OTHERLINE' => "ABC\nAF\"ASFASDF\nMORESHIT",
			'SUPERLINE' => '',
			'__FOO_BAR_1' => 'b',
			'__FOOFOO' => 'f     ',
			123123 => 'number',
			'EMPTY' => '',
		];
		// 0: folder relative to test folder, if unset __DIR__
		// 1: file, if unset .env
		// 2: status to be returned
		// 3: _ENV file content to be set
		// 4: override chmod as octect in string
		return [
			'default' => [
				'folder' => null,
				'file' => null,
				'status' => 3,
				'content' => [],
				'chmod' => null,
			],
			'cannot open file' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'cannot_read.env',
				'status' => 2,
				'content' => [],
				'chmod' => '000',
			],
			'empty file' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'empty.env',
				'status' => 1,
				'content' => [],
				'chmod' => null,
			],
			'override all' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'test.env',
				'status' => 0,
				'content' => $dot_env_content,
				'chmod' => null,
			],
			'override directory' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => null,
				'status' => 0,
				'content' => $dot_env_content,
				'chmod' => null,
			],
		];
	}

	/**
	 * test read .env file
	 *
	 * @covers ::readEnvFile
	 * @dataProvider envFileProvider
	 * @testdox Read _ENV file from $folder / $file with expected status: $expected_status and chmod $chmod [$_dataName]
	 *
	 * @param  string|null $folder
	 * @param  string|null $file
	 * @param  int         $expected_status
	 * @param  array       $expected_env
	 * @param  string|null $chmod
	 * @return void
	 */
	public function testReadEnvFile(
		?string $folder,
		?string $file,
		int $expected_status,
		array $expected_env,
		?string $chmod
	): void {
		// if we have file + chmod set
		$old_chmod = null;
		if (
			is_file($folder . DIRECTORY_SEPARATOR . $file) &&
			!empty($chmod)
		) {
			// get the old permissions
			$old_chmod = fileperms($folder . DIRECTORY_SEPARATOR . $file);
			chmod($folder . DIRECTORY_SEPARATOR . $file, octdec($chmod));
		}
		$message = '\CoreLibs\Get\DotEnv is deprecated in favor for '
			. 'composer package gullevek\dotenv which is a copy of this';
		// convert E_USER_DEPRECATED to a exception
		set_error_handler(
			static function (int $errno, string $errstr): never {
				throw new \Exception($errstr, $errno);
			},
			E_USER_DEPRECATED
		);
		$this->expectExceptionMessage($message);
		if ($folder !== null && $file !== null) {
			$status = DotEnv::readEnvFile($folder, $file);
		} elseif ($folder !== null) {
			$status = DotEnv::readEnvFile($folder);
		} else {
			$status = DotEnv::readEnvFile();
		}
		restore_error_handler();
		$this->assertEquals(
			$status,
			$expected_status,
			'Assert returned status equal'
		);
		// now assert read data
		$this->assertEquals(
			$_ENV,
			$expected_env,
			'Assert _ENV correct'
		);
		// if we have file and chmod unset
		if ($old_chmod !== null) {
			chmod($folder . DIRECTORY_SEPARATOR . $file, $old_chmod);
		}
	}
}

// __END__
