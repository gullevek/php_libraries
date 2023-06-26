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
		$encrypted = SymmetricEncryption::encrypt($input, $key);
		$decrypted = SymmetricEncryption::decrypt($encrypted, $key);

		$this->assertEquals(
			$expected,
			$decrypted
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
		$encrypted = SymmetricEncryption::encrypt($input, $key);
		$wrong_key = CreateKey::generateRandomKey();
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decrypt($encrypted, $wrong_key);
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
					. 'SODIUM_CRYPTO_SECRETBOX_KEYBYTES bytes long).'
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
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::encrypt('test', $key);
		// we must encrypt valid thing first so we can fail with the wrong kjey
		$enc_key = CreateKey::generateRandomKey();
		$encrypted = SymmetricEncryption::encrypt('test', $enc_key);
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decrypt($encrypted, $key);
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
				'exception_message' => 'Invalid ciphertext (too short)'
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
		$this->expectExceptionMessage($exception_message);
		SymmetricEncryption::decrypt($input, $key);
	}
}

// __END__
