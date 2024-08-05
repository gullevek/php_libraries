<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Security\CreateKey;
use CoreLibs\Security\SymmetricEncryption;

/**
 * Test class for Security\SymmetricEncryption and Security\CreateKey
 * @coversDefaultClass \CoreLibs\Security\SymmetricEncryption
 * @testdox \CoreLibs\Security\SymmetricEncryption method tests
 */
final class CoreLibsSecuritySymmetricEncryptionTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerEncryptDecryptSuccess(): array
	{
		return [
			'valid string' => [
				'input' => 'I am a secret',
				'expected' => 'I am a secret',
			],
		];
	}

	/**
	 * test encrypt/decrypt produce correct output
	 *
	 * @covers ::generateRandomKey
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerEncryptDecryptSuccess
	 * @testdox encrypt/decrypt $input must be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testEncryptDecryptSuccess(string $input, string $expected): void
	{
		$key = CreateKey::generateRandomKey();

		// test class
		$crypt = new SymmetricEncryption($key);
		$encrypted = $crypt->encrypt($input);
		$decrypted = $crypt->decrypt($encrypted);
		$this->assertEquals(
			$expected,
			$decrypted,
			'Class call',
		);

		// test indirect
		$encrypted = SymmetricEncryption::getInstance($key)->encrypt($input);
		$decrypted = SymmetricEncryption::getInstance($key)->decrypt($encrypted);
		$this->assertEquals(
			$expected,
			$decrypted,
			'Class Instance call',
		);

		// test static
		$encrypted = SymmetricEncryption::encryptKey($input, $key);
		$decrypted = SymmetricEncryption::decryptKey($encrypted, $key);

		$this->assertEquals(
			$expected,
			$decrypted,
			'Static call',
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerEncryptFailed(): array
	{
		return [
			'wrong decryption key' => [
				'input' => 'I am a secret',
				'excpetion_message' => 'Invalid Key'
			],
		];
	}

	/**
	 * Test decryption with wrong key
	 *
	 * @covers ::generateRandomKey
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerEncryptFailed
	 * @testdox decrypt with wrong key $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testEncryptFailed(string $input, string $exception_message): void
	{
		$key = CreateKey::generateRandomKey();
		$wrong_key = CreateKey::generateRandomKey();

		// wrong key in class call
		$crypt = new SymmetricEncryption($key);
		$encrypted = $crypt->encrypt($input);
		$this->expectExceptionMessage($exception_message);
		$crypt->setKey($key);
		$crypt->decrypt($encrypted);

		// class instance
		$encrypted = SymmetricEncryption::getInstance($key)->encrypt($input);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($wrong_key)->decrypt($encrypted);

		// class static
		$encrypted = SymmetricEncryption::encryptKey($input, $key);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($encrypted, $wrong_key);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerWrongKey(): array
	{
		return [
			'not hex key' => [
				'key' => 'not_a_hex_key',
				'exception_message' => 'Invalid hex key'
			],
			'too short hex key' => [
				'key' => '1cabd5cba9e042f12522f4ff2de5c31d233b',
				'excpetion_message' => 'Key is not the correct size (must be '
			],
		];
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongKey
	 * @testdox wrong key $key throws $exception_message [$_dataName]
	 *
	 * @param  string $key
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongKey(string $key, string $exception_message): void
	{
		$enc_key = CreateKey::generateRandomKey();

		// class
		$crypt = new SymmetricEncryption($key);
		$this->expectExceptionMessage($exception_message);
		$crypt->encrypt('test');
		$crypt->setKey($enc_key);
		$encrypted = $crypt->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		$crypt->setKey($key);
		$crypt->decrypt($encrypted);

		// class instance
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($key)->encrypt('test');
		// we must encrypt valid thing first so we can fail with the wrong key
		$encrypted = SymmetricEncryption::getInstance($enc_key)->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($key)->decrypt($encrypted);

		// class static
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::encryptKey('test', $key);
		// we must encrypt valid thing first so we can fail with the wrong key
		$encrypted = SymmetricEncryption::encryptKey('test', $enc_key);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($encrypted, $key);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerWrongCiphertext(): array
	{
		return [
			'too short ciphertext' => [
				'input' => 'short',
				'exception_message' => 'Decipher message failed: '
			],
		];
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::decrypt
	 * @dataProvider providerWrongCiphertext
	 * @testdox too short ciphertext $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongCiphertext(string $input, string $exception_message): void
	{
		$key = CreateKey::generateRandomKey();
		// class
		$crypt = new SymmetricEncryption($key);
		$this->expectExceptionMessage($exception_message);
		$crypt->decrypt($input);

		// class instance
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($key)->decrypt($input);

		// class static
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($input, $key);
	}
}

// __END__
