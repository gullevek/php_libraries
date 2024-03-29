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
		self::$log = new \CoreLibs\Logging\Logging([
			'log_folder' => DIRECTORY_SEPARATOR . 'tmp',
			'log_file_id' => 'CoreLibs-Create-Email-Test',
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
		// 3: kv_folding
		// 4: expected
		return [
			'all empty' => [
				'',
				null,
				null,
				null,
				''
			],
			'email only' => [
				'test@test.com',
				null,
				null,
				null,
				'test@test.com'
			],
			'email and name' => [
				'test@test.com',
				'Test Name',
				null,
				null,
				'"Test Name" <test@test.com>'
			],
			'name in mime encoded, default UTF-8' => [
				'test@test.com',
				'日本語',
				null,
				null,
				'"=?UTF-8?B?5pel5pys6Kqe?=" <test@test.com>'
			],
			'name in mime encoded with half width Katakana, default UTF-8' => [
				'test@test.com',
				'日本語ｶﾀｶﾅﾊﾟ',
				null,
				null,
				'"=?UTF-8?B?5pel5pys6Kqe7722776A7722776F776K776f?=" <test@test.com>'
			],
			'name in mime encoded with half width Katakana, folding on, default UTF-8' => [
				'test@test.com',
				'日本語ｶﾀｶﾅﾊﾟ',
				'UTF-8',
				true,
				'"=?UTF-8?B?5pel5pys6Kqe44Kr44K/44Kr44OK44OR?=" <test@test.com>'
			],
			'name in mime encoded, UTF-8 parameter' => [
				'test@test.com',
				'日本語',
				'UTF-8',
				null,
				'"=?UTF-8?B?5pel5pys6Kqe?=" <test@test.com>'
			],
			// does internal UTF-8 to ISO-2022-JP convert
			'encoding in ISO-2022-JP' => [
				'test@test.com',
				'日本語',
				'ISO-2022-JP',
				null,
				'"=?ISO-2022-JP?B?GyRCRnxLXDhsGyhC?=" <test@test.com>'
			],
			'encoding with half width Katakana in ISO-2022-JP' => [
				'test@test.com',
				'日本語ｶﾀｶﾅﾊﾟ',
				'ISO-2022-JP',
				null,
				'"=?ISO-2022-JP?B?GyRCRnxLXDhsGyhCPz8/Pz8/?=" <test@test.com>'
			],
			'encoding with half width Katakana, folding on in ISO-2022-JP' => [
				'test@test.com',
				'日本語ｶﾀｶﾅﾊﾟ',
				'ISO-2022-JP',
				true,
				// was ok php 8.1
				// '"=?ISO-2022-JP?B?GyRCRnxLXDhsGyhCPz8/Pz8=?=" <test@test.com>'
				// below ok php 8.1.12, 2022/12/9
				'"=?ISO-2022-JP?B?GyRCRnxLXDhsGyhCPz8/Pz8/?=" <test@test.com>'
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider encodeEmailNameProvider
	 * @testdox encode email $email, name $name, encoding $encoding, folding $kv_folding will be $expected [$_dataName]
	 *
	 * @param  string      $email
	 * @param  string|null $name
	 * @param  string|null $encoding
	 * @param  bool|null   $kv_folding
	 * @param  string      $expected
	 * @return void
	 */
	public function testEncodeEmailName(
		string $email,
		?string $name,
		?string $encoding,
		?bool $kv_folding,
		string $expected
	): void {
		if ($name === null && $encoding === null && $kv_folding === null) {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email);
		} elseif ($encoding === null && $kv_folding === null) {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email, $name);
		} elseif ($kv_folding === null) {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email, $name, $encoding);
		} else {
			$encoded_email = \CoreLibs\Create\Email::encodeEmailName($email, $name, $encoding, $kv_folding);
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
		// 7: kv_folding
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
				'kv_folding' => null,
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
				'kv_folding' => null,
				'expected_status' => -2,
				'expected_content' => [],
			],
			'bad encoding, fail -3' => [
				'subject' => 'SUBJECT',
				'body' => 'BODY',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => ['to@test.com'],
				'replace' => null,
				'encoding' => 'IDONTEXISTENCODING',
				'kv_folding' => null,
				'expected_status' => -3,
				'expected_content' => [],
			],
			'sending email 1' => [
				'subject' => 'SUBJECT',
				'body' => 'BODY',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => null,
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT',
						'body' => 'BODY',
					]
				],
			],
			'sending email 1, encoded' => [
				'subject' => 'SUBJECT 日本語',
				'body' => 'BODY 日本語',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => null,
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6Kqe?=',
						'body' => 'BODY 日本語',
					]
				],
			],
			'sending email 1, encoded, with half width katakanata' => [
				'subject' => 'SUBJECT 日本語ｶﾀｶﾅﾊﾟ',
				'body' => 'BODY 日本語',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => null,
				'encoding' => 'UTF-8',
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6Kqe7722776A7722776F776K776f?=',
						'body' => 'BODY 日本語',
					]
				],
			],
			'sending email 1, encoded, with half width katakanata, folding on' => [
				'subject' => 'SUBJECT 日本語ｶﾀｶﾅﾊﾟ',
				'body' => 'BODY 日本語',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => null,
				'encoding' => 'UTF-8',
				'kv_folding' => true,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6Kqe44Kr44K/44Kr44OK44OR?=',
						'body' => 'BODY 日本語',
					]
				],
			],
			'sending email 1, encoded subject ISO-2022-JP' => [
				'subject' => 'SUBJECT 日本語',
				'body' => 'BODY 日本語',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => null,
				'encoding' => 'ISO-2022-JP',
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT =?ISO-2022-JP?B?GyRCRnxLXDhsGyhC?=',
						// body is stored as UTF-8 in log and here, so both must be translated
						'body' => 'BODY 日本語',
					]
				],
			],
			'sending email 2' => [
				'subject' => 'SUBJECT',
				'body' => 'BODY',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'e1@test.com',
					'e2@test.com'
				],
				'replace' => null,
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'e1@test.com',
						'subject' => 'SUBJECT',
						'body' => 'BODY',
					],
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'e2@test.com',
						'subject' => 'SUBJECT',
						'body' => 'BODY',
					]
				],
			],
			'sending email 1: dynamic' => [
				'subject' => 'SUBJECT {FOO}',
				'body' => 'BODY {FOO} {VAR}',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => [
					'FOO' => 'foo',
					'VAR' => 'bar',
				],
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT foo',
						'body' => 'BODY foo bar',
					]
				],
			],
			'sending email 1: dynamic encoded' => [
				'subject' => 'SUBJECT 日本語 {FOO}',
				'body' => 'BODY 日本語 {FOO} {VAR}',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					'test@test.com'
				],
				'replace' => [
					'FOO' => 'foo',
					'VAR' => 'bar',
				],
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6KqeIGZvbw==?=',
						'body' => 'BODY 日本語 foo bar',
					]
				],
			],
			'sending email 1: dynamic, to override' => [
				'subject' => 'SUBJECT {FOO}',
				'body' => 'BODY {FOO} {VAR}',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					[
						'email' => 'test@test.com',
						'replace' => [
							'FOO' => 'foo to'
						]
					]
				],
				'replace' => [
					'FOO' => 'foo',
					'VAR' => 'bar',
				],
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT foo to',
						'body' => 'BODY foo to bar',
					]
				],
			],
			'sending email 1: dynamic, to override encoded' => [
				'subject' => 'SUBJECT 日本語 {FOO}',
				'body' => 'BODY 日本語 {FOO} {VAR}',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					[
						'email' => 'test@test.com',
						'replace' => [
							'FOO' => 'foo to'
						]
					]
				],
				'replace' => [
					'FOO' => 'foo',
					'VAR' => 'bar',
				],
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 'test@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6KqeIGZvbyB0bw==?=',
						'body' => 'BODY 日本語 foo to bar',
					]
				],
			],
			'sending email 3: dynamic, to mixed override' => [
				'subject' => 'SUBJECT {FOO}',
				'body' => 'BODY {FOO} {VAR}',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					[
						'email' => 't1@test.com',
						'replace' => [
							'FOO' => 'foo to 1'
						]
					],
					[
						'email' => 't2@test.com',
						'replace' => [
							'FOO' => 'foo to 2'
						]
					],
					[
						'email' => 't3@test.com',
					],
				],
				'replace' => [
					'FOO' => 'foo',
					'VAR' => 'bar',
				],
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 't1@test.com',
						'subject' => 'SUBJECT foo to 1',
						'body' => 'BODY foo to 1 bar',
					],
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 't2@test.com',
						'subject' => 'SUBJECT foo to 2',
						'body' => 'BODY foo to 2 bar',
					],
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 't3@test.com',
						'subject' => 'SUBJECT foo',
						'body' => 'BODY foo bar',
					],
				],
			],
			'sending email 3: dynamic, to mixed override encoded' => [
				'subject' => 'SUBJECT 日本語 {FOO}',
				'body' => 'BODY 日本語 {FOO} {VAR}',
				'from_email' => 'test@test.com',
				'from_name' => '',
				'to_email' => [
					[
						'email' => 't1@test.com',
						'replace' => [
							'FOO' => 'foo to 1'
						]
					],
					[
						'email' => 't2@test.com',
						'replace' => [
							'FOO' => 'foo to 2'
						]
					],
					[
						'email' => 't3@test.com',
					],
				],
				'replace' => [
					'FOO' => 'foo',
					'VAR' => 'bar',
				],
				'encoding' => null,
				'kv_folding' => null,
				'expected_status' => 2,
				'expected_content' => [
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 't1@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6KqeIGZvbyB0byAx?=',
						'body' => 'BODY 日本語 foo to 1 bar',
					],
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 't2@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6KqeIGZvbyB0byAy?=',
						'body' => 'BODY 日本語 foo to 2 bar',
					],
					[
						'header' => [
							'From' => 'test@test.com'
						],
						'to' => 't3@test.com',
						'subject' => 'SUBJECT =?UTF-8?B?5pel5pys6KqeIGZvbw==?=',
						'body' => 'BODY 日本語 foo bar',
					],
				],
			],
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
	 * @param  bool|null   $kv_folding
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
		?bool $kv_folding,
		int $expected_status,
		array $expected_content
	): void {
		if ($replace === null) {
			$replace = [];
		}
		if ($encoding === null) {
			$encoding = 'UTF-8';
		}
		if ($kv_folding === null) {
			$kv_folding = false;
		}
		// force new set for each run
		self::$log->setLogUniqueId(true);
		// set on of unique log id
		self::$log->setLogFlag(\CoreLibs\Logging\Logger\Flag::per_run);
		// init logger
		$status = \CoreLibs\Create\Email::sendEmail(
			$subject,
			$body,
			$from_email,
			$from_name,
			$to_email,
			$replace,
			$encoding,
			$kv_folding,
			true,
			self::$log
		);
		$this->assertEquals(
			$expected_status,
			$status,
			'Assert sending status'
		);
		// assert content: must load JSON from log file
		if ($status == 2) {
			// open file, get last entry with 'SEND EMAIL JSON' key
			$file = file_get_contents(
				self::$log->getLogFolder() . self::$log->getLogFile()
			);
			if ($file !== false) {
				// extract SEND EMAIL JSON line
				$found = preg_match_all("/^.* <SEND EMAIL JSON> - (.*)$/m", $file, $matches);
				// print "Found: $found | EMAIL: " . print_r($matches, true) . "\n";
				if (!empty($matches[1])) {
					foreach ($matches[1] as $pos => $email_json) {
						$email = \CoreLibs\Convert\Json::jsonConvertToArray($email_json);
						// print "EMAIL: " . print_r($email, true) . "\n";
						$this->assertEquals(
							$expected_content[$pos]['header']['From'] ?? 'MISSING FROM',
							$email['header']['From'] ?? '',
							'Email check: assert header from'
						);
						$this->assertEquals(
							'text/plain; charset=' . $encoding ?? 'UTF-8',
							$email['header']['Content-type'] ?? '',
							'Email check: assert header content type'
						);
						$this->assertEquals(
							'1.0',
							$email['header']['MIME-Version'] ?? '',
							'Email check: assert header mime version'
						);
						$this->assertEquals(
							$expected_content[$pos]['to'] ?? 'MISSING TO',
							$email['to'] ?? '',
							'Email check: assert to'
						);
						$this->assertEquals(
							$expected_content[$pos]['subject'] ?? 'MISSING SUBJECT',
							$email['subject'] ?? '',
							'Email check: assert subject'
						);
						// body must be translated back to encoding if encoding is not UTF-8
						$this->assertEquals(
							$encoding != 'UTF-8' ?
								mb_convert_encoding($expected_content[$pos]['body'] ?? '', $encoding, 'UTF-8') :
								$expected_content[$pos]['body'] ?? 'MISSING BODY',
							$email['encoding'] != 'UTF-8' ?
								mb_convert_encoding($email['body'] ?? '', $email['encoding'], 'UTF-8') :
								$email['body'] ?? '',
							'Email check: assert body'
						);
					}
				}
			}
		}
	}
}

// __END__
