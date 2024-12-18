<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use CoreLibs\Security\CreateKey;
use CoreLibs\Security\AsymmetricAnonymousEncryption;

/**
 * Test class for Security\AsymmetricAnonymousEncryption and Security\CreateKey
 * @coversDefaultClass \CoreLibs\Security\AsymmetricAnonymousEncryption
 * @testdox \CoreLibs\Security\AsymmetricAnonymousEncryption method tests
 */
final class CoreLibsSecurityAsymmetricAnonymousEncryptionTest extends TestCase
{
	// MARK: key set and compare

	/**
	 * Undocumented function
	 *
	 * @covers ::getKeyPair
	 * @covers ::compareKeyPair
	 * @covers ::getPublicKey
	 * @covers ::comparePublicKey
	 * @testdox Check if init class set key pair matches to created key pair and public key
	 *
	 * @return void
	 */
	public function testKeyPairInitGetCompare(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption($key_pair);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair),
			'set key pair not equal to original key pair'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'automatic set public key not equal to original public key'
		);
		$this->assertEquals(
			$key_pair,
			$crypt->getKeyPair(),
			'set key pair returned not equal to original key pair'
		);
		$this->assertEquals(
			$public_key,
			$crypt->getPublicKey(),
			'automatic set public key returned not equal to original public key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getKeyPair
	 * @covers ::compareKeyPair
	 * @covers ::getPublicKey
	 * @covers ::comparePublicKey
	 * @testdox Check if init class set key pair and public key matches to created key pair and public key
	 *
	 * @return void
	 */
	public function testKeyPairPublicKeyInitGetCompare(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption($key_pair, $public_key);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair),
			'set key pair not equal to original key pair'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'set public key not equal to original public key'
		);
		$this->assertEquals(
			$key_pair,
			$crypt->getKeyPair(),
			'set key pair returned not equal to original key pair'
		);
		$this->assertEquals(
			$public_key,
			$crypt->getPublicKey(),
			'set public key returned not equal to original public key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::getKeyPair
	 * @covers ::getPublicKey
	 * @covers ::comparePublicKey
	 * @testdox Check if init class set public key matches to created public key
	 *
	 * @return void
	 */
	public function testPublicKeyInitGetCompare(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption(public_key:$public_key);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'set public key not equal to original public key'
		);
		$this->assertEquals(
			null,
			$crypt->getKeyPair(),
			'unset set key pair returned not equal to original key pair'
		);
		$this->assertEquals(
			$public_key,
			$crypt->getPublicKey(),
			'set public key returned not equal to original public key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setKeyPair
	 * @covers ::getKeyPair
	 * @covers ::compareKeyPair
	 * @covers ::getPublicKey
	 * @covers ::comparePublicKey
	 * @testdox Check if set key pair after class init matches to created key pair and public key
	 *
	 * @return void
	 */
	public function testKeyPairSetGetCompare(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption();
		$crypt->setKeyPair($key_pair);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair),
			'post class init set key pair not equal to original key pair'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'post class init automatic set public key not equal to original public key'
		);
		$this->assertEquals(
			$key_pair,
			$crypt->getKeyPair(),
			'post class init set key pair returned not equal to original key pair'
		);
		$this->assertEquals(
			$public_key,
			$crypt->getPublicKey(),
			'post class init automatic set public key returned not equal to original public key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::setKeyPair
	 * @covers ::setPublicKey
	 * @covers ::getKeyPair
	 * @covers ::compareKeyPair
	 * @covers ::getPublicKey
	 * @covers ::comparePublicKey
	 * @testdox Check if set key pair after class init matches to created key pair and public key
	 *
	 * @return void
	 */
	public function testKeyPairPublicKeySetGetCompare(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption();
		$crypt->setKeyPair($key_pair);
		$crypt->setPublicKey($public_key);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair),
			'post class init set key pair not equal to original key pair'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'post class init set public key not equal to original public key'
		);
		$this->assertEquals(
			$key_pair,
			$crypt->getKeyPair(),
			'post class init set key pair returned not equal to original key pair'
		);
		$this->assertEquals(
			$public_key,
			$crypt->getPublicKey(),
			'post class init set public key returned not equal to original public key'
		);
	}

		/**
	 * Undocumented function
	 *
	 * @covers ::setPublicKey
	 * @covers ::getKeyPair
	 * @covers ::compareKeyPair
	 * @covers ::getPublicKey
	 * @covers ::comparePublicKey
	 * @testdox Check if set key pair after class init matches to created key pair and public key
	 *
	 * @return void
	 */
	public function testPublicKeySetGetCompare(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption();
		$crypt->setPublicKey($public_key);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'post class init set public key not equal to original public key'
		);
		$this->assertEquals(
			null,
			$crypt->getKeyPair(),
			'post class init unset key pair returned not equal to original key pair'
		);
		$this->assertEquals(
			$public_key,
			$crypt->getPublicKey(),
			'post class init set public key returned not equal to original public key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Check different key pair and public key set
	 *
	 * @return void
	 */
	public function testDifferentSetKeyPairPublicKey()
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$key_pair_2 = CreateKey::createKeyPair();
		$public_key_2 = CreateKey::getPublicKey($key_pair_2);
		$crypt = new AsymmetricAnonymousEncryption($key_pair, $public_key_2);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair),
			'key pair set matches key pair created'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key_2),
			'alternate public key set matches alternate public key created'
		);
		$this->assertFalse(
			$crypt->comparePublicKey($public_key),
			'alternate public key set does not match key pair public key'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @testdox Check if new set privat key does not overwrite set public key
	 *
	 * @return void
	 */
	public function testUpdateKeyPairNotUpdatePublicKey(): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$crypt = new AsymmetricAnonymousEncryption($key_pair);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair),
			'set key pair not equal to original key pair'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'set public key not equal to original public key'
		);
		$key_pair_2 = CreateKey::createKeyPair();
		$public_key_2 = CreateKey::getPublicKey($key_pair_2);
		$crypt->setKeyPair($key_pair_2);
		$this->assertTrue(
			$crypt->compareKeyPair($key_pair_2),
			'new set key pair not equal to original new key pair'
		);
		$this->assertTrue(
			$crypt->comparePublicKey($public_key),
			'original set public key not equal to original public key'
		);
		$this->assertFalse(
			$crypt->comparePublicKey($public_key_2),
			'new public key equal to original public key'
		);
	}

	// MARK: empty encrytped string

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
		AsymmetricAnonymousEncryption::decryptKey('', CreateKey::generateRandomKey());
	}

	// MARK: encrypt/decrypt

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
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		// test class
		$crypt = new AsymmetricAnonymousEncryption($key_pair);
		$encrypted = $crypt->encrypt($input);
		$decrypted = $crypt->decrypt($encrypted);
		$this->assertEquals(
			$expected,
			$decrypted,
			'Class call',
		);
		$crypt = new AsymmetricAnonymousEncryption($key_pair, $public_key);
		$encrypted = $crypt->encrypt($input);
		$decrypted = $crypt->decrypt($encrypted);
		$this->assertEquals(
			$expected,
			$decrypted,
			'Class call botjh set',
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
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		// test indirect
		$encrypted = AsymmetricAnonymousEncryption::getInstance(public_key:$public_key)->encrypt($input);
		$decrypted = AsymmetricAnonymousEncryption::getInstance($key_pair)->decrypt($encrypted);
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
	 * @testdox encrypt/decrypt indirect with public key $input must be $expected [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $expected
	 * @return void
	 */
	public function testEncryptDecryptSuccessIndirectPublicKey(string $input, string $expected): void
	{
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		// test indirect
		$encrypted = AsymmetricAnonymousEncryption::getInstance(public_key:$public_key)->encrypt($input);
		$decrypted = AsymmetricAnonymousEncryption::getInstance($key_pair)->decrypt($encrypted);
		$this->assertEquals(
			$expected,
			$decrypted,
			'Class Instance call public key',
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
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		// test static
		$encrypted = AsymmetricAnonymousEncryption::encryptKey($input, $public_key);
		$decrypted = AsymmetricAnonymousEncryption::decryptKey($encrypted, $key_pair);

		$this->assertEquals(
			$expected,
			$decrypted,
			'Static call',
		);
	}

	// MARK: invalid decrypt key

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
				'excpetion_message' => 'Invalid key pair'
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
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$wrong_key_pair = CreateKey::createKeyPair();

		// wrong key in class call
		$crypt = new AsymmetricAnonymousEncryption(public_key:$public_key);
		$encrypted = $crypt->encrypt($input);
		$this->expectExceptionMessage($exception_message);
		$crypt->setKeyPair($wrong_key_pair);
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
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$wrong_key_pair = CreateKey::createKeyPair();

		// class instance
		$encrypted = AsymmetricAnonymousEncryption::getInstance(public_key:$public_key)->encrypt($input);
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::getInstance($wrong_key_pair)->decrypt($encrypted);
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
		$key_pair = CreateKey::createKeyPair();
		$public_key = CreateKey::getPublicKey($key_pair);
		$wrong_key_pair = CreateKey::createKeyPair();

		// class static
		$encrypted = AsymmetricAnonymousEncryption::encryptKey($input, $public_key);
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::decryptKey($encrypted, $wrong_key_pair);
	}

	// MARK: invalid key pair

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerWrongKeyPair(): array
	{
		return [
			'not hex key pair' => [
				'key_pair' => 'not_a_hex_key_pair',
				'exception_message' => 'Invalid hex key pair'
			],
			'too short hex key pair' => [
				'key_pair' => '1cabd5cba9e042f12522f4ff2de5c31d233b',
				'excpetion_message' => 'Key pair is not the correct size (must be '
			],
			'empty key pair' => [
				'key_pair' => '',
				'excpetion_message' => 'Key pair cannot be empty'
			]
		];
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongKeyPair
	 * @testdox wrong key pair $key_pair throws $exception_message [$_dataName]
	 *
	 * @param  string $key_pair
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongKeyPair(string $key_pair, string $exception_message): void
	{
		$enc_key_pair = CreateKey::createKeyPair();

		// class
		$this->expectExceptionMessage($exception_message);
		$crypt = new AsymmetricAnonymousEncryption($key_pair);
		$this->expectExceptionMessage($exception_message);
		$crypt->encrypt('test');
		$crypt->setKeyPair($enc_key_pair);
		$encrypted = $crypt->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		$crypt->setKeyPair($key_pair);
		$crypt->decrypt($encrypted);
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongKeyPair
	 * @testdox wrong key pair indirect $key_pair throws $exception_message [$_dataName]
	 *
	 * @param  string $key_pair
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongKeyPairIndirect(string $key_pair, string $exception_message): void
	{
		$enc_key_pair = CreateKey::createKeyPair();

		// set valid encryption
		$encrypted = AsymmetricAnonymousEncryption::getInstance($enc_key_pair)->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::getInstance($key_pair)->decrypt($encrypted);
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongKeyPair
	 * @testdox wrong key pair static $key_pair throws $exception_message [$_dataName]
	 *
	 * @param  string $key_pair
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongKeyPairStatic(string $key_pair, string $exception_message): void
	{
		$enc_key_pair = CreateKey::createKeyPair();

		// set valid encryption
		$encrypted = AsymmetricAnonymousEncryption::encryptKey('test', CreateKey::getPublicKey($enc_key_pair));
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::decryptKey($encrypted, $key_pair);
	}

	// MARK: invalid public key

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerWrongPublicKey(): array
	{
		return [
			'not hex public key' => [
				'public_key' => 'not_a_hex_public_key',
				'exception_message' => 'Invalid hex public key'
			],
			'too short hex public key' => [
				'public_key' => '1cabd5cba9e042f12522f4ff2de5c31d233b',
				'excpetion_message' => 'Public key is not the correct size (must be '
			],
			'empty public key' => [
				'public_key' => '',
				'excpetion_message' => 'Public key cannot be empty'
			]
		];
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongPublicKey
	 * @testdox wrong public key $public_key throws $exception_message [$_dataName]
	 *
	 * @param  string $public_key
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongPublicKey(string $public_key, string $exception_message): void
	{
		$enc_key_pair = CreateKey::createKeyPair();
		// $enc_public_key = CreateKey::getPublicKey($enc_key_pair);

		// class
		$this->expectExceptionMessage($exception_message);
		$crypt = new AsymmetricAnonymousEncryption(public_key:$public_key);
		$this->expectExceptionMessage($exception_message);
		$crypt->decrypt('test');
		$crypt->setKeyPair($enc_key_pair);
		$encrypted = $crypt->encrypt('test');
		$this->expectExceptionMessage($exception_message);
		$crypt->setPublicKey($public_key);
		$crypt->decrypt($encrypted);
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongPublicKey
	 * @testdox wrong public key indirect $key throws $exception_message [$_dataName]
	 *
	 * @param  string $key
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongPublicKeyIndirect(string $key, string $exception_message): void
	{
		$enc_key = CreateKey::createKeyPair();

		// class instance
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::getInstance(public_key:$key)->encrypt('test');
		// we must encrypt valid thing first so we can fail with the wrong key
		$encrypted = AsymmetricAnonymousEncryption::getInstance($enc_key)->encrypt('test');
		// $this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::getInstance($key)->decrypt($encrypted);
	}

	/**
	 * test invalid key provided to decrypt or encrypt
	 *
	 * @covers ::encrypt
	 * @covers ::decrypt
	 * @dataProvider providerWrongPublicKey
	 * @testdox wrong public key static $key throws $exception_message [$_dataName]
	 *
	 * @param  string $key
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongPublicKeyStatic(string $key, string $exception_message): void
	{
		$enc_key = CreateKey::createKeyPair();

		// class static
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::encryptKey('test', $key);
		// we must encrypt valid thing first so we can fail with the wrong key
		$encrypted = AsymmetricAnonymousEncryption::encryptKey('test', $enc_key);
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::decryptKey($encrypted, $key);
	}

	// MARK: wrong cipher text

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function providerWrongCiphertext(): array
	{
		return [
			'invalid cipher text' => [
				'input' => 'short',
				'exception_message' => 'base642bin failed: '
			],
			'cannot decrypt' => [
				// phpcs:disable Generic.Files.LineLength
				'input' => 'Um8tBGiVfFAOg2YoUgA5fTqK1wXPB1S7uxhPNE1lqDxgntkEhYJDOmjXa0DMpBlYHjab6sC4mgzwZSzGCUnXDAgsHckwYwfAzs/r',
				// phpcs:enable Generic.Files.LineLength
				'exception_message' => 'Invalid key pair'
			],
			'invalid text' => [
				'input' => 'U29tZSB0ZXh0IGhlcmU=',
				'exception_message' => 'Invalid key pair'
			]
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
		$key = CreateKey::createKeyPair();
		// class
		$crypt = new AsymmetricAnonymousEncryption($key);
		$this->expectExceptionMessage($exception_message);
		$crypt->decrypt($input);
	}

	/**
	 * Undocumented function
	 *
	 * @covers ::decryptKey
	 * @dataProvider providerWrongCiphertext
	 * @testdox too short ciphertext indirect $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongCiphertextIndirect(string $input, string $exception_message): void
	{
		$key = CreateKey::createKeyPair();

		// class instance
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::getInstance($key)->decrypt($input);

		// class static
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::decryptKey($input, $key);
	}

		/**
	 * Undocumented function
	 *
	 * @covers ::decryptKey
	 * @dataProvider providerWrongCiphertext
	 * @testdox too short ciphertext static $input throws $exception_message [$_dataName]
	 *
	 * @param  string $input
	 * @param  string $exception_message
	 * @return void
	 */
	public function testWrongCiphertextStatic(string $input, string $exception_message): void
	{
		$key = CreateKey::createKeyPair();
		// class static
		$this->expectExceptionMessage($exception_message);
		AsymmetricAnonymousEncryption::decryptKey($input, $key);
	}
}

// __END__
