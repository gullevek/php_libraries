<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for Create\Session
 * @coversDefaultClass \CoreLibs\Create\Session
 * @testdox \CoreLibs\Create\Session method tests
 */
final class CoreLibsCreateSessionTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function sessionProvider(): array
	{
		// 0: session name as parameter or for GLOBAL value
		// 2: mock data as array
		//    checkCliStatus: true/false,
		//    getSessionStatus: PHP_SESSION_DISABLED for abort,
		//                      PHP_SESSION_NONE/ACTIVE for ok
		//    setSessionName: true/false,
		//    checkActiveSession: true/false, [1st call, 2nd call]
		//    getSessionId: string or false
		// 3: exepcted name (session)]
		// 4: auto write close flag
		return [
			'session parameter' => [
				'sessionNameParameter',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'sessionNameParameter',
				null,
			],
			'session globals' => [
				'sessionNameGlobals',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'sessionNameGlobals',
				[
					'auto_write_close' => false,
				],
			],
			'auto write close' => [
				'sessionNameAutoWriteClose',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'sessionNameAutoWriteClose',
				[
					'auto_write_close' => true,
				],
			],
		];
	}

	/**
	 * Test session start
	 *
	 * @covers ::startSession
	 * @dataProvider sessionProvider
	 * @testdox startSession $input name for $type will be $expected (error: $expected_error) [$_dataName]
	 *
	 * @param string $input
	 * @param array<mixed> $mock_data
	 * @param string $expected
	 * @param array<string,mixed> $options
	 * @return void
	 */
	public function testStartSession(
		string $input,
		array $mock_data,
		string $expected,
		?array $options,
	): void {
		/** @var \CoreLibs\Create\Session&MockObject $session_mock */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			[
				'checkCliStatus',
				'getSessionStatus', 'checkActiveSession',
				'getSessionId',
				'getSessionName'
			]
		);
		// set return values based requested input values
		// OK: true
		// error: false
		$session_mock->method('checkCliStatus')->willReturn($mock_data['checkCliStatus']);
		// OK: PHP_SESSION_ACTIVE, PHP_SESSION_NONE
		// error: PHP_SESSION_DISABLED
		$session_mock->method('getSessionStatus')->willReturn($mock_data['getSessionStatus']);
		// false: try start
		// true: skip start
		// note that on second call if false -> error
		$session_mock->method('checkActiveSession')
			->willReturnOnConsecutiveCalls(
				$mock_data['checkActiveSession'][0],
				$mock_data['checkActiveSession'][1],
			);
		// set session name & return bsed on request data
		$session_mock->method('getSessionName')->willReturn($expected);
		// in test case only return string
		// false: will return false
		$session_mock->method('getSessionId')->willReturn($mock_data['getSessionId']);

		// regex for session id
		$ression_id_regex = "/^\w+$/";

		$session_id = $session_mock->getSessionId();
		// asert checks
		if (!empty($session_id)) {
			$this->assertMatchesRegularExpression(
				$ression_id_regex,
				(string)$session_id,
				'session id regex from retrun'
			);
			$this->assertMatchesRegularExpression(
				$ression_id_regex,
				(string)$session_mock->getSessionId()
			);
			$this->assertEquals(
				$expected,
				$session_mock->getSessionName()
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerSessionException(): array
	{
		return [
			'not cli' => [
				'TEST_EXCEPTION',
				\RuntimeException::class,
				1,
				'/^\[SESSION\] No sessions in php cli$/',
			],
			/* 'session disabled ' => [
				'TEST_EXCEPTION',
				\RuntimeException::class,
				2,
				'/^\[SESSION\] Sessions are disabled/'
			],
			'invalid session name' => [
				'--#as^-292p-',
				\UnexpectedValueException::class,
				3,
				'/^\[SESSION\] Invalid session name: /'
			],
			'failed to activate session' => [
				'TEST_EXCEPTION',
				\RuntimeException::class,
				4,
				'/^\[SESSION\] Failed to activate session/'
			],
			'expired session' => [
				\RuntimeException::class,
				5,
				'/^\[SESSION\] Expired session found/'
			],
			'not a valid session id returned' => [
				\UnexpectedValueException::class,
				6,
				'/^\[SESSION\] getSessionId did not return a session id/'
			], */
		];
	}

	/**
	 * exception checks
	 *
	 * @covers ::initSession
	 * @dataProvider providerSessionException
	 * @testdox create session $session_name with exception $exception ($exception_code) [$_dataName]
	 *
	 * @param  string $session_name
	 * @param  string $exception
	 * @param  int    $exception_code
	 * @param  string $expected_error
	 * @return void
	 */
	public function testSessionException(
		string $session_name,
		string $exception,
		int $exception_code,
		string $expected_error,
	): void {
		//
		// throws only on new Object creation
		$this->expectException($exception);
		$this->expectExceptionCode($exception_code);
		$this->expectExceptionMessageMatches($expected_error);
		// cannot set ini after header sent, plus we are on command line there are no headers
		new \CoreLibs\Create\Session($session_name, ['session_strict' => false]);
	}

	/**
	 * provider for session name check
	 *
	 * @return array
	 */
	public function sessionNameProvider(): array
	{
		// 0: string for session
		// 1: expected return as bool
		return [
			'valid name' => [
				'abc',
				true
			],
			'valid name longer' => [
				'something-abc-123',
				true
			],
			'invalid name' => [
				'abc#abc',
				false
			],
			'only numbers' => [
				'123',
				false
			],
			'longer than 128 chars' => [
				'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'
					. 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'
					. 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz',
				false
			],
			'too short' => [
				'',
				false
			],
		];
	}

	/**
	 * test valid session name
	 *
	 * @covers ::checkValidSessionName
	 * @dataProvider sessionNameProvider
	 * @testdox checkValidSessionName $input seessionn name is $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  bool   $expected
	 * @return void
	 */
	public function testCheckValidSessionName(string $input, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Create\Session::checkValidSessionName($input)
		);
	}

	/**
	 * provider for set/get tests
	 *
	 * @return array
	 */
	public function providerSessionData(): array
	{
		return [
			'test' => [
				'foo',
				'bar',
				'bar',
				null,
			],
			'int key test' => [
				123,
				'bar',
				'bar',
				\UnexpectedValueException::class
			],
			// more complex value tests
			'array values' => [
				'array',
				[1, 2, 3],
				[1, 2, 3],
				null,
			]
		];
	}

	// NOTE: with auto start session, we cannot test this in the command line

	/**
	 * method call test
	 *
	 * @covers ::set
	 * @covers ::get
	 * @covers ::isset
	 * @covers ::unset
	 * @dataProvider providerSessionData
	 * @testdox set/get/isset/unset $name with $input is $expected ($exception) [$_dataName]
	 *
	 * @param  string|int $name
	 * @param  mixed $input
	 * @param  mixed $expected
	 * @param  ?mixed $exception
	 * @return void
	 */
	public function testMethodSetGet($name, $input, $expected, $exception): void
	{
		if (\CoreLibs\Get\System::checkCLI()) {
			$this->markTestSkipped('Cannot run testMethodSetGet in CLI');
		}
		$session = new \CoreLibs\Create\Session('TEST_METHOD');
		if ($expected !== null) {
			$this->expectException($exception);
		}
		$session->set($name, $input);
		$this->assertEquals(
			$expected,
			$session->get($name),
			'method set assert'
		);
		// isset true
		$this->assertTrue(
			$session->isset($name),
			'method isset assert ok'
		);
		$session->unset($name);
		$this->assertEquals(
			'',
			$session->get($name),
			'method unset assert'
		);
		// isset false
		$this->assertFalse(
			$session->isset($name),
			'method isset assert false'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerSessionDataMany(): array
	{
		return [
			'valid set' => [
				[
					'foo 1' => 'bar 1',
					'foo 2' => 'bar 1',
				],
				[
					'foo 1' => 'bar 1',
					'foo 2' => 'bar 1',
				],
				null,
			],
			'invalid entry' => [
				[
					'foo 1' => 'bar 1',
					123 => 'bar 1',
				],
				[
					'foo 1' => 'bar 1',
				],
				\UnexpectedValueException::class
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setMany
	 * @covers ::getMany
	 * @dataProvider providerSessionDataMany
	 * @testdox setMany/getMany/unsetMany $set is $expected ($exception) [$_dataName]
	 *
	 * @param  array<string|int,mixed> $set
	 * @param  array<string,mixed> $expected
	 * @param  ?mixed $exception
	 * @return void
	 */
	public function testMany($set, $expected, $exception): void
	{
		if (\CoreLibs\Get\System::checkCLI()) {
			$this->markTestSkipped('Cannot run testMethodSetGet in CLI');
		}
		$session = new \CoreLibs\Create\Session('TEST_METHOD');
		if ($expected !== null) {
			$this->expectException($exception);
		}
		$session->setMany($set);
		$this->assertEquals(
			$expected,
			$session->getMany(array_keys($set)),
			'set many failed'
		);
		$session->unsetMany(array_keys($set));
		$this->assertEquals(
			[],
			$session->getMany(array_keys($set)),
			'unset many failed'
		);
	}

	/**
	 * unset all test
	 *
	 * @covers ::unsetAllS
	 * @testdox unsetAllS test
	 *
	 * @return void
	 */
	public function testUnsetAll(): void
	{
		if (\CoreLibs\Get\System::checkCLI()) {
			$this->markTestSkipped('Cannot run testUnsetAll in CLI');
		}
		$test_values = [
			'foo' => 'abc',
			'bar' => '123'
		];
		$session = new \CoreLibs\Create\Session('TEST_UNSET');
		foreach ($test_values as $name => $value) {
			$session->set($name, $value);
			// confirm set
			$this->assertEquals(
				$value,
				$session->get($name),
				'set assert: ' . $name
			);
		}
		// unset all
		$session->clear();
		// check unset
		foreach (array_keys($test_values) as $name) {
			$this->assertEquals(
				'',
				$session->get($name),
				'unsert assert: ' . $name
			);
		}
	}
}

// __END__
