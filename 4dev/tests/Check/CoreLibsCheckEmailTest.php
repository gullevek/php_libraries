<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Undocumented class
 * @coversDefaultClass \CoreLibs\Check\Email
 * @testdox \CoreLibs\Check\Email method tests
 */
final class CoreLibsCheckEmailTest extends TestCase
{
	/**
	 * Array position to regex
	 *
	 * @return array<mixed>
	 */
	public function emailRegexProvider(): array
	{
		return [
			'get email regex invalid -1, will be 0' => [
				-1,
				"^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@"
					. "(?!-)[A-Za-z0-9-]{1,63}(?<!-)(?:\.[A-Za-z0-9-]{1,63}(?<!-))*\.[a-zA-Z]{2,6}$"
			],
			'get email regex invalid 10, will be 0' => [
				10,
				"^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@"
					. "(?!-)[A-Za-z0-9-]{1,63}(?<!-)(?:\.[A-Za-z0-9-]{1,63}(?<!-))*\.[a-zA-Z]{2,6}$"
			],
			'get email regex valid 1, will be 1' => [
				1,
				"@(.*)@(.*)"
			]
		];
	}

	/**
	 * Test regex level return
	 *
	 * @covers ::getEmailRegex
	 * @dataProvider emailRegexProvider
	 * @testdox getEmailRegex $input will be $expected [$_dataName]
	 *
	 * @param int $input
	 * @param string $expected
	 * @return void
	 */
	public function testGetEmailRegexReturn(int $input, string $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\Email::getEmailRegex($input)
		);
	}

	/**
	 * provides data for emailCheckProvider and emailCheckFullProvider
	 *
	 * @return array
	 */
	public function emailCheckList(): array
	{
		return [
			'valid email' => ['test@test.com', true, []],
			'invalid empty email' => ['', false, [0, 2, 3, 4, 5]],
			'invalid email' => ['-@-', false, [0, 3, 4, 5]],
			'invalid email leading dot' => ['.test@test.com', false, [0, 2]],
			'invalid email invalid domain' => ['test@t_est.com', false, [0, 3, 4]],
			'invalid email double @' => ['test@@test.com', false, [0, 1]],
			'invalid email double dot' => ['test@test..com', false, [0, 3, 6]],
			'invalid email end with dot' => ['test@test.', false, [0, 3, 5, 7]],
			'invalid email bad top level' => ['test@test.j', false, [0, 3, 5]],
			'invalid email double @ and double dot' => ['test@@test..com', false, [0, 1, 3, 6]],
		];
	}

	/**
	 * Valids or not valid email address
	 *
	 * @return array
	 */
	public function emailCheckProvider(): array
	{
		$list = [];
		foreach ($this->emailCheckList() as $key => $data) {
			$list[$key] = [$data[0], $data[1]];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::checkEmail
	 * @dataProvider emailCheckProvider
	 * @testdox checkEmail $input will be $expected [$_dataName]
	 *
	 * @return void
	 */
	public function testCheckEmail(string $input, bool $expected): void
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\Email::checkEmail($input)
		);
	}

	/**
	 * this is like emailCheckProvider but it has the full detail errors
	 * All errors should be tetsed in testGetEmailRegexErrorMessage
	 *
	 * @return array
	 */
	public function emailCheckFullProvider(): array
	{
		$list = [];
		foreach ($this->emailCheckList() as $key => $data) {
			$list[$key] = [$data[0], $data[2]];
		}
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::checkEmailFull
	 * @dataProvider emailCheckFullProvider
	 * @testdox checkEmailFull $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param array $expected
	 * @return void
	 */
	public function testCheckEmailFull(string $input, array $expected): void
	{
		$this->assertEqualsCanonicalizing(
			$expected,
			\CoreLibs\Check\Email::checkEmailFull($input, true)
		);
	}

	/**
	 * error data returned for each error position
	 *
	 * @return array
	 */
	public function emailRegexErrorProvider(): array
	{
		return [
			'error 0 will return general' => [
				0,
				[
					'error' => 0,
					'message' => 'Invalid email address',
					'regex' => "^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@"
						. "(?!-)[A-Za-z0-9-]{1,63}(?<!-)(?:\.[A-Za-z0-9-]{1,63}(?<!-))*\.[a-zA-Z]{2,6}$"
				]
			],
			'error 1 will return double @ error' => [
				1,
				[
					'error' => 1,
					'message' => 'Double @ mark in email address',
					'regex' => "@(.*)@(.*)"
				]
			],
			'error 2 will be invalid before @' => [
				2,
				[
					'error' => 2,
					'message' => 'Invalid email part before @ sign',
					'regex' => "^[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~][A-Za-z0-9!#$%:\(\)&'*+\-\/=?^_`{|}~\.]{0,63}@"
				]
			],
			'error 3 will be invalid domain and top level' => [
				3,
				[
					'error' => 3,
					'message' => 'Invalid domain part after @ sign',
					'regex' => "@(?!-)[A-Za-z0-9-]{1,63}(?<!-)(?:\.[A-Za-z0-9-]{1,63}(?<!-))*\.[a-zA-Z]{2,6}$"
				]
			],
			'error 4 will be invalid domain' => [
				4,
				[
					'error' => 4,
					'message' => 'Invalid domain name part',
					'regex' => "@(?!-)[A-Za-z0-9-]{1,63}(?<!-)(?:\.[A-Za-z0-9-]{1,63}(?<!-))*\."
				]
			],
			'error 5 will be invalid domain top level only' => [
				5,
				[
					'error' => 5,
					'message' => 'Wrong domain top level part',
					'regex' => "\.[a-zA-Z]{2,6}$"
				]
			],
			'error 6 will be domain double dot' => [
				6,
				[
					'error' => 6,
					'message' => 'Double consecutive dots in domain name (..)',
					'regex' => "@(.*)\.{2,}"
				]
			],
			'error 7 will domain ends with dot' => [
				7,
				[
					'error' => 7,
					'message' => 'Domain ends with a dot or is missing top level part',
					'regex' => "@.*\.$"
				]
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getEmailRegexErrorMessage
	 * @dataProvider emailRegexErrorProvider
	 * @testdox getEmailRegexErrorMessage $input will be $expected [$_dataName]
	 *
	 * @param integer $input
	 * @param array $expected
	 * @return void
	 */
	public function testGetEmailRegexErrorMessage(int $input, array $expected): void
	{
		$this->assertEqualsCanonicalizing(
			$expected,
			\CoreLibs\Check\Email::getEmailRegexErrorMessage($input)
		);
	}

	/**
	 * This holds all email type checks normal and short
	 *
	 * @return array
	 */
	public function emailTypeProvider(): array
	{
		return [
			['test@test.com', 'pc_html', 'pc'],
			['test@docomo.ne.jp', 'keitai_docomo', 'docomo'],
			['test@softbank.ne.jp', 'keitai_softbank', 'softbank'],
			['test@i.softbank.ne.jp', 'smartphone_softbank_iphone', 'iphone'],
			// TODO: add more test emails here
		];
	}

	/**
	 * Returns only normal email type checks
	 *
	 * @return array<mixed>
	 */
	public function emailTypeProviderLong(): array
	{
		$list = [];
		foreach ($this->emailTypeProvider() as $set) {
			$list['email ' . $set[0] . ' is valid and matches normal ' . $set[1]] = [$set[0], $set[1]];
		}
		$list['email is empty and not valid normal'] = ['', 'invalid'];
		return $list;
	}

	/**
	 * only short email type list
	 *
	 * @return array<mixed>
	 */
	public function emailTypeProviderShort(): array
	{
		$list = [];
		foreach ($this->emailTypeProvider() as $set) {
			$list['email ' . $set[0] . ' is valid and matches short ' . $set[2]] = [$set[0], $set[2]];
		}
		$list['email is empty and not valid short'] = ['', 'invalid'];
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getEmailType
	 * @dataProvider emailTypeProviderLong
	 * @testdox getEmailType $input will be normal $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testGetEmailTypeNormal(string $input, string $expected)
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\Email::getEmailType($input, false)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getEmailType
	 * @dataProvider emailTypeProviderShort
	 * @testdox getEmailType $input will be short $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string $expected
	 * @return void
	 */
	public function testGetEmailTypeShort(string $input, string $expected)
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\Email::getEmailType($input, true)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function emailProviderTypeLongToShort(): array
	{
		$mobile_email_type_short = [
			'keitai_docomo' => 'docomo',
			'keitai_kddi_ezweb' => 'kddi',
			'keitai_kddi' => 'kddi',
			'keitai_kddi_tu-ka' => 'kddi',
			'keitai_kddi_sky' => 'kddi',
			'keitai_softbank' => 'softbank',
			'smartphone_softbank_iphone' => 'iphone',
			'keitai_softbank_disney' => 'softbank',
			'keitai_softbank_vodafone' => 'softbank',
			'keitai_softbank_j-phone' => 'softbank',
			'keitai_willcom' => 'willcom',
			'keitai_willcom_pdx' => 'willcom',
			'keitai_willcom_bandai' => 'willcom',
			'keitai_willcom_pipopa' => 'willcom',
			'keitai_willcom_ymobile' => 'willcom',
			'keitai_willcom_emnet' => 'willcom',
			'pc_html' => 'pc',
		];
		$list = [];
		// use the static one
		foreach ($mobile_email_type_short as $long => $short) {
			$list[$long . ' matches to ' . $short] = [$long, $short];
		}
		// add invalid check
		$list['Not found will be bool false'] = ['invalid', false];
		return $list;
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getShortEmailType
	 * @dataProvider emailProviderTypeLongToShort
	 * @testdox getShortEmailType $input will be $expected [$_dataName]
	 *
	 * @param string $input
	 * @param string|bool $expected
	 * @return void
	 */
	public function testGetShortEmailType(string $input, $expected)
	{
		$this->assertEquals(
			$expected,
			\CoreLibs\Check\Email::getShortEmailType($input)
		);
	}
}

// __END__
