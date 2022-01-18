<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

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
		return [
			'session parameter' => [
				'sessionNameParameter',
				'p',
				'sessionNameParameter',
				'/^\w+$/'
			],
			'session globals' => [
				'sessionNameGlobals',
				'g',
				'sessionNameGlobals',
				'/^\w+$/'
			],
			'session constant' => [
				'sessionNameConstant',
				'c',
				'sessionNameConstant',
				'/^\w+$/'
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		if (session_id()) {
			session_destroy();
		}
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider sessionProvider
	 * @testdox  startSession $input name for $type will be $expected_n with $expected_i [$_dataName]
	 *
	 * @param string $input
	 * @param string $type
	 * @param string|bool $expected_n
	 * @param string|bool $expected_i
	 * @return void
	 */
	public function testStartSession(string $input, string $type, $expected_n, $expected_i): void
	{
		/* $session_id = '';
		switch ($type) {
			case 'p':
				$session_id = \CoreLibs\Create\Session::startSession($input);
				break;
			case 'g':
				$GLOBALS['SET_SESSION_NAME'] = $input;
				$session_id = \CoreLibs\Create\Session::startSession();
				break;
			case 'c':
				define('SET_SESSION_NAME', $input);
				$session_id = \CoreLibs\Create\Session::startSession();
				break;
		}
		$this->assertMatchesRegularExpression(
			$expected_i,
			(string)$session_id
		);
		$this->assertMatchesRegularExpression(
			$expected_i,
			(string)\CoreLibs\Create\Session::getSessionId()
		);
		$this->assertEquals(
			$expected_n,
			\CoreLibs\Create\Session::getSessionName()
		);
		if ($type == 'g') {
			unset($GLOBALS['SET_SESSION_NAME']);
		} */
		$this->markTestSkipped('No implementation for Create\Session. Cannot run session_start in CLI');
	}
}

// __END__
