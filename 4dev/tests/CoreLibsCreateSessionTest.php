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
		// 1: type p: parameter, g: global, d: php.ini default
		// 2: mock data as array
		//    checkCliStatus: true/false,
		//    getSessionStatus: PHP_SESSION_DISABLED for abort,
		//                      PHP_SESSION_NONE/ACTIVE for ok
		//    setSessionName: true/false,
		//    checkActiveSession: true/false, [1st call, 2nd call]
		//    getSessionId: string or false
		// 3: exepcted name (session)
		// 4: expected error string
		return [
			'session parameter' => [
				'sessionNameParameter',
				'p',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'sessionNameParameter',
				''
			],
			'session globals' => [
				'sessionNameGlobals',
				'g',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'sessionNameGlobals',
				''
			],
			'session name default' => [
				'',
				'd',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'',
				''
			],
			// error checks
			// 1: we are in cli
			'on cli error' => [
				'',
				'd',
				[
					'checkCliStatus' => true,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'',
				'[SESSION] No sessions in php cli'
			],
			// 2: session disabled
			'session disabled error' => [
				'',
				'd',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_DISABLED,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'',
				'[SESSION] Sessions are disabled'
			],
			// 3: invalid session name: string
			'invalid name chars error' => [
				'1invalid$session#;',
				'p',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => false,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'',
				'[SESSION] Invalid session name: 1invalid$session#;'
			],
			// 3: invalid session name: only numbers
			'invalid name numbers only error' => [
				'123',
				'p',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => false,
					'checkActiveSession' => [false, true],
					'getSessionId' => '1234abcd4567'
				],
				'',
				'[SESSION] Invalid session name: 123'
			],
			// 3: invalid session name: invalid name short
			// 3: invalid session name: too long (128)
			// 4: failed to start session (2nd false on check active session)
			'invalid name numbers only error' => [
				'',
				'd',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, false],
					'getSessionId' => '1234abcd4567'
				],
				'',
				'[SESSION] Failed to activate session'
			],
			// 5: get session id return false
			'invalid name numbers only error' => [
				'',
				'd',
				[
					'checkCliStatus' => false,
					'getSessionStatus' => PHP_SESSION_NONE,
					'setSessionName' => true,
					'checkActiveSession' => [false, true],
					'getSessionId' => false
				],
				'',
				'[SESSION] getSessionId did not return a session id'
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
	 * @param string $type
	 * @param array<mixed> $mock_data
	 * @param string $expected
	 * @param string $expected_error
	 * @return void
	 */
	public function testStartSession(
		string $input,
		string $type,
		array $mock_data,
		string $expected,
		string $expected_error
	): void {
		// override expected
		if ($type == 'd') {
			$expected = ini_get('session.name');
		}
		/** @var \CoreLibs\Create\Session&MockObject $session_mock */
		$session_mock = $this->createPartialMock(
			\CoreLibs\Create\Session::class,
			[
				'checkCliStatus', 'getSessionStatus', 'checkActiveSession',
				'setSessionName', 'startSessionCall', 'getSessionId',
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
		// dummy set for session name
		$session_mock->method('setSessionName')->with($input)->willReturn($mock_data['setSessionName']);
		// set session name & return bsed on request data
		$session_mock->method('getSessionName')->willReturn($expected);
		// will not return anything
		$session_mock->method('startSessionCall');
		// in test case only return string
		// false: will return false
		$session_mock->method('getSessionId')->willReturn($mock_data['getSessionId']);

		// regex for session id
		$ression_id_regex = "/^\w+$/";

		unset($GLOBALS['SET_SESSION_NAME']);
		$session_id = '';
		switch ($type) {
			case 'p':
				$session_id = $session_mock->startSession($input);
				break;
			case 'g':
				$GLOBALS['SET_SESSION_NAME'] = $input;
				$session_id = $session_mock->startSession();
				break;
			case 'd':
				$session_id = $session_mock->startSession();
				break;
		}
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
		} else {
			// false checks
			$this->assertEquals(
				$expected_error,
				$session_mock->getErrorStr(),
				'error assert'
			);
		}
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
	public function sessionDataProvider(): array
	{
		return [
			'test' => [
				'foo',
				'bar',
				'bar',
			],
			'int key test' => [
				123,
				'bar',
				'bar',
			],
			// more complex value tests
			'array values' => [
				'array',
				[1, 2, 3],
				[1, 2, 3],
			]
		];
	}

	/**
	 * method call test
	 *
	 * @covers ::setS
	 * @covers ::getS
	 * @covers ::issetS
	 * @covers ::unsetS
	 * @dataProvider sessionDataProvider
	 * @testdox setS/getS/issetS/unsetS $name with $input is $expected [$_dataName]
	 *
	 * @param  string|int $name
	 * @param  mixed $input
	 * @param  mixed $expected
	 * @return void
	 */
	public function testMethodSetGet($name, $input, $expected): void
	{
		$session = new \CoreLibs\Create\Session();
		$session->setS($name, $input);
		$this->assertEquals(
			$expected,
			$session->getS($name),
			'method set assert'
		);
		// isset true
		$this->assertTrue(
			$session->issetS($name),
			'method isset assert ok'
		);
		$session->unsetS($name);
		$this->assertEquals(
			'',
			$session->getS($name),
			'method unset assert'
		);
		// iset false
		$this->assertFalse(
			$session->issetS($name),
			'method isset assert false'
		);
	}

	/**
	 * magic call test
	 *
	 * @covers ::__set
	 * @covers ::__get
	 * @covers ::__isset
	 * @covers ::__unset
	 * @dataProvider sessionDataProvider
	 * @testdox __set/__get/__iseet/__unset $name with $input is $expected [$_dataName]
	 *
	 * @param  string|int $name
	 * @param  mixed $input
	 * @param  mixed $expected
	 * @return void
	 */
	public function testMagicSetGet($name, $input, $expected): void
	{
		$session = new \CoreLibs\Create\Session();
		$session->$name = $input;
		$this->assertEquals(
			$expected,
			$session->$name,
			'magic set assert'
		);
		// isset true
		$this->assertTrue(
			isset($session->$name),
			'magic isset assert ok'
		);
		unset($session->$name);
		$this->assertEquals(
			'',
			$session->$name,
			'magic unset assert'
		);
		// isset true
		$this->assertFalse(
			isset($session->$name),
			'magic isset assert false'
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
		$test_values = [
			'foo' => 'abc',
			'bar' => '123'
		];
		$session = new \CoreLibs\Create\Session();
		foreach ($test_values as $name => $value) {
			$session->setS($name, $value);
			// confirm set
			$this->assertEquals(
				$value,
				$session->getS($name),
				'set assert: ' . $name
			);
		}
		// unset all
		$session->unsetAllS();
		// check unset
		foreach (array_keys($test_values) as $name) {
			$this->assertEquals(
				'',
				$session->getS($name),
				'unsert assert: ' . $name
			);
		}
	}
}

// __END__
