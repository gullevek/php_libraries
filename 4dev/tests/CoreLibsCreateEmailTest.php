<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Create\Email
 * @coversDefaultClass \CoreLibs\Create\Email
 * @testdox \CoreLibs\Create\Email method tests
 */
final class CoreLibsCreateEmailTest extends TestCase
{
	private static $log;

	/**
	 * start DB conneciton, setup DB, etc
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		self::$log = new \CoreLibs\Debug\Logging([
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'file_id' => 'CoreLibs-Create-Email-Test',
			'debug_all' => true,
			'echo_all' => false,
			'print_all' => true,
		]);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function encodeEmailNameProvider(): array
	{
		// 0: email
		// 1: name
		// 2: encoding
		// 3: expected
		return [
			'all empty' => [
				'',
				null,
				null,
				''
			],
			'email only' => [
				'test@test.com',
				null,
				null,
				'test@test.com'
			],
			'email and name' => [
				'test@test.com',
				'Test Name',
				null,
				'"Test Name" <test@test.com>'
			],
			'name in mime encoded, default UTF-8' => [
				'test@test.com',
				'日本語',
				null,
				'"=?UTF-8?B?5pel5pys6Kqe?=" <test@test.com>'
			],
			'name in mime encoded, UTF-8 parameter' => [
				'test@test.com',
				'日本語',
				'UTF-8',
				'"=?UTF-8?B?5pel5pys6Kqe?=" <test@test.com>'
			],
			// does internal UTF-8 to ISO-2022-JP convert
			'encoding in ISO-2022-JP' => [
				'test@test.com',
				'日本語',
				'ISO-2022-JP',
				'"=?ISO-2022-JP?B?GyRCRnxLXA==?=" <test@test.com>'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider encodeEmailNameProvider
	 * @testdox encode email $email, name $name, encoding $encoding will be $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testEncodeEmailName(
		string $email,
		?string $name,
		?string $encoding,
		string $expected
	): void {
		if ($name === null && $encoding === null) {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email);
		} elseif ($encoding === null) {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email, $name);
		} else {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email, $name, $encoding);
		}
		$this->assertEquals(
			$expected,
			$encoded_email
		);
	}

	public function sendEmailProvider(): array
	{
		// 0: subject
		// 1: body
		// 2: from email
		// 3: from name ('')
		// 4: array for to email
		// 5: replace content ([]/null)
		// 6: encoding (UTF-8/null)
		// 8: return status
		// 9: expected content
		return [
			'all empty, fail -1' => [
				'subject' => '',
				'body' => '',
				'from_email' => '',
				'from_name' => '',
				'to_email' => [],
				'replace' => null,
				'encoding' => null,
				'expected_status' => -1,
				'expected_content' => [],
			],
			'missing to entry, fail -2' => [
				'subject' => 'SUBJECT',
				'body' => 'BODY',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [],
				'replace' => null,
				'encoding' => null,
				'expected_status' => -2,
				'expected_content' => [],
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider sendEmailProvider
	 * @testdox email sending with expected status $expected_status [$_dataName]
	 *
	 * @param  string      $subject
	 * @param  string      $body
	 * @param  string      $from_email
	 * @param  string      $from_name
	 * @param  array       $to_email
	 * @param  array|null  $replace
	 * @param  string|null $encoding
	 * @param  int         $expected_status
	 * @param  array       $expected_content
	 * @return void
	 */
	public function testSendEmail(
		string $subject,
		string $body,
		string $from_email,
		string $from_name,
		array $to_email,
		?array $replace,
		?string $encoding,
		int $expected_status,
		array $expected_content
	): void {
		if ($replace === null) {
			$replace = [];
		}
		if ($encoding === null) {
			$encoding = 'UTF-8';
		}
		$status = \CoreLibs\Create\Email::sendEmail(
			$subject,
			$body,
			$from_email,
			$from_name,
			$to_email,
			$replace,
			$encoding,
			true,
			self::$log
		);
		$this->assertEquals(
			$expected_status,
			$status,
			'Assert sending status'
		);
		// assert content: must load JSON from log file?
	}
}

// __END__
