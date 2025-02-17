<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Security\Password
 * @coversDefaultClass \CoreLibs\Security\Password
 * @testdox \CoreLibs\Security\Password method tests
 */
final class CoreLibsSecurityPasswordTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function passwordProvider(): array
	{
		return [
			'matching password' => ['test', 'test', true],
			'not matching password' => ['test', 'not_test', false],
		];
	}

	/**
	 * Note: we need different hash types for PHP versions
	 *
	 * @return array
	 */
	public function passwordRehashProvider(): array
	{
		return [
			'no rehash needed' => ['$2y$10$EgWJ2WE73DWi.hIyFRCdpejLXTvHbmTK3LEOclO1tAvXAXUNuUS4W', false],
			'rehash needed' => ['9c42a1346e333a770904b2a2b37fa7d3', true],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::passwordVerify
	 * @covers ::passwordSet
	 * @dataProvider passwordProvider
	 * @testdox passwordSet $input compare to $input_hash: passwordVerify $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $input_hash
	 * @param boolean $expected
	 * @return void
	 */
	public function testPasswordSetVerify(string $input, string $input_hash, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Security\Password::passwordVerify($input, \CoreLibs\Security\Password::passwordSet($input_hash))
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::passwordRehashCheck
	 * @dataProvider passwordRehashProvider
	 * @testdox passwordRehashCheck $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param boolean $expected
	 * @return void
	 */
	public function testPasswordRehashCheck(string $input, bool $expected): void
	{
		// in PHP 8.4 the length is $12
		if (PHP_VERSION_ID > 80400) {
			$input = str_replace('$2y$10$', '$2y$12$', $input);
		}
		$this->assertEquals(
			$expected,
			\CoreLibs\Security\Password::passwordRehashCheck($input)
		);
	}
}

// __END__
