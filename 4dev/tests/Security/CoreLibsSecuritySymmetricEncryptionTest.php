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
	// MARK: key set compare

	/**
	 * Undocumented function
	 *
	 * @covers ::compareKey
	 * @covers ::getKey
	 * @testdox Check if init class set key matches to created key
	 *
	 * @return void
	 */
	public function testKeyInitGetCompare(): void
	{
		$key = CreateKey::generateRandomKey();
		$crypt = new SymmetricEncryption($key);
		$this->assertTrue(
			$crypt->compareKey($key),
			'set key not equal to original key'
		);
		$this->assertEquals(
			$key,
			$crypt->getKey(),
			'set key returned not equal to original key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setKey
	 * @covers ::compareKey
	 * @covers ::getKey
	 * @testdox Check if set key after class init matches to created key
	 *
	 * @return void
	 */
	public function testKeySetGetCompare(): void
	{
		$key = CreateKey::generateRandomKey();
		$crypt = new SymmetricEncryption();
		$crypt->setKey($key);
		$this->assertTrue(
			$crypt->compareKey($key),
			'set key not equal to original key'
		);
		$this->assertEquals(
			$key,
			$crypt->getKey(),
			'set key returned not equal to original key'
		);
	}

	// MARK: empty encrypted string

	/**
	 * Undocumented function
	 *
	 * @covers ::decryptKey
	 * @covers ::decrypt
	 * @testdox Test empty encrypted string to decrypt
	 *
	 * @return void
	 */
	public function testEmptyDecryptionString(): void
	{
		$this->expectExceptionMessage('Encrypted string cannot be empty');
		SymmetricEncryption::decryptKey('', CreateKey::generateRandomKey());
	}

	// MARK: encrypt/decrypt compare

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
	}

	/**
	 * test encrypt/decrypt produce correct output
	 *
	 * @covers ::generateRandomKey
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerEncryptDecryptSuccess
	 * @testdox encrypt/decrypt indirect $input must be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testEncryptDecryptSuccessIndirect(string $input, string $expected): void
	{
		$key = CreateKey::generateRandomKey();
		// test indirect
		$encrypted = SymmetricEncryption::getInstance($key)->encrypt($input);
		$decrypted = SymmetricEncryption::getInstance($key)->decrypt($encrypted);
		$this->assertEquals(
			$expected,
			$decrypted,
			'Class Instance call',
		);
	}

	/**
	 * test encrypt/decrypt produce correct output
	 *
	 * @covers ::generateRandomKey
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerEncryptDecryptSuccess
	 * @testdox encrypt/decrypt static $input must be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testEncryptDecryptSuccessStatic(string $input, string $expected): void
	{
		$key = CreateKey::generateRandomKey();
		// test static
		$encrypted = SymmetricEncryption::encryptKey($input, $key);
		$decrypted = SymmetricEncryption::decryptKey($encrypted, $key);

		$this->assertEquals(
			$expected,
			$decrypted,
			'Static call',
		);
	}

	// MARK: invalid key

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
		$crypt->setKey($wrong_key);
		$crypt->decrypt($encrypted);
	}

	/**
	 * Test decryption with wrong key
	 *
	 * @covers ::generateRandomKey
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerEncryptFailed
	 * @testdox decrypt indirect with wrong key $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testEncryptFailedIndirect(string $input, string $exception_message): void
	{
		$key = CreateKey::generateRandomKey();
		$wrong_key = CreateKey::generateRandomKey();

		// class instance
		$encrypted = SymmetricEncryption::getInstance($key)->encrypt($input);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($wrong_key)->decrypt($encrypted);
	}

	/**
	 * Test decryption with wrong key
	 *
	 * @covers ::generateRandomKey
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerEncryptFailed
	 * @testdox decrypt static with wrong key $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testEncryptFailedStatic(string $input, string $exception_message): void
	{
		$key = CreateKey::generateRandomKey();
		$wrong_key = CreateKey::generateRandomKey();

		// class static
		$encrypted = SymmetricEncryption::encryptKey($input, $key);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($encrypted, $wrong_key);
	}

	// MARK: wrong key

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
			'empty key' => [
				'key' => '',
				'excpetion_message' => 'Key cannot be empty'
			]
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
		$this->expectExceptionMessage($exception_message);
		$crypt = new SymmetricEncryption($key);
		$this->expectExceptionMessage($exception_message);
		$crypt->encrypt('test');
		$crypt->setKey($enc_key);
		$encrypted = $crypt->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		$crypt->setKey($key);
		$crypt->decrypt($encrypted);
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongKey
	 * @testdox wrong key indirect $key throws $exception_message [$_dataName]
	 *
	 * @param  string $key
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongKeyIndirect(string $key, string $exception_message): void
	{
		$enc_key = CreateKey::generateRandomKey();

		// class instance
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($key)->encrypt('test');
		// we must encrypt valid thing first so we can fail with the wrong key
		$encrypted = SymmetricEncryption::getInstance($enc_key)->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($key)->decrypt($encrypted);
	}

		/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongKey
	 * @testdox wrong key static $key throws $exception_message [$_dataName]
	 *
	 * @param  string $key
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongKeyStatic(string $key, string $exception_message): void
	{
		$enc_key = CreateKey::generateRandomKey();

		// class static
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::encryptKey('test', $key);
		// we must encrypt valid thing first so we can fail with the wrong key
		$encrypted = SymmetricEncryption::encryptKey('test', $enc_key);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($encrypted, $key);
	}

	// MARK: wrong input

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

	/**
	 * Undocumented function
	 *
	 * @covers ::decrypt
	 * @dataProvider providerWrongCiphertext
	 * @testdox too short ciphertext indirect $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongCiphertextIndirect(string $input, string $exception_message): void
	{
		$key = CreateKey::generateRandomKey();

		// class instance
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::getInstance($key)->decrypt($input);

		// class static
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($input, $key);
	}

		/**
	 * Undocumented function
	 *
	 * @covers ::decrypt
	 * @dataProvider providerWrongCiphertext
	 * @testdox too short ciphertext static $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongCiphertextStatic(string $input, string $exception_message): void
	{
		$key = CreateKey::generateRandomKey();
		// class static
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decryptKey($input, $key);
	}
}

// __END__
