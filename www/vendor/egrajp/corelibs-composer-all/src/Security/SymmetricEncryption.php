<?php

/**
 * very simple symmetric encryption
 * Better use:
 * https://paragonie.com/project/halite
 * https://github.com/paragonie/halite
 *
 * current code is just to encrypt and decrypt
 *
 * must use a valid encryption key created with
 * Secruty\CreateKey class
 */

declare(strict_types=1);

namespace CoreLibs\Security;

use CoreLibs\Security\CreateKey;
use SodiumException;

class SymmetricEncryption
{
	/** @var SymmetricEncryption self instance */
	private static SymmetricEncryption $instance;

	/** @var string bin hex key */
	private string $key = '';

	/**
	 * init class
	 * if key not passed, key must be set with createKey
	 *
	 * @param  string|null|null $key
	 */
	public function __construct(
		string|null $key = null
	) {
		if ($key != null) {
			$this->setKey($key);
		}
	}

	/**
	 * Returns the singleton self object.
	 * For function wrapper use
	 *
	 * @return SymmetricEncryption object
	 */
	public static function getInstance(string|null $key = null): self
	{
		// new if no instsance or key is different
		if (
			empty(self::$instance) ||
			self::$instance->key != $key
		) {
			self::$instance = new self($key);
		}
		return self::$instance;
	}

	/* ************************************************************************
	 * MARK: PRIVATE
	 * *************************************************************************/

	/**
	 * create key and check validity
	 *
	 * @param  string $key The key from which the binary key will be created
	 * @return string      Binary key string
	 */
	private function createKey(string $key): string
	{
		try {
			$key = CreateKey::hex2bin($key);
		} catch (SodiumException $e) {
			throw new \UnexpectedValueException('Invalid hex key: ' . $e->getMessage());
		}
		if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
			throw new \RangeException(
				'Key is not the correct size (must be '
					. SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ' bytes long).'
			);
		}
		return $key;
	}

	/**
	 * Decryption call
	 *
	 * @param  string  $encrypted Text to decrypt
	 * @param  ?string $key       Mandatory encryption key, will throw exception if empty
	 * @return string             Plain text
	 * @throws \RangeException
	 * @throws \UnexpectedValueException
	 * @throws \UnexpectedValueException
	 */
	private function decryptData(string $encrypted, ?string $key): string
	{
		if (empty($key)) {
			throw new \UnexpectedValueException('Key not set');
		}
		$key = $this->createKey($key);
		$decoded = base64_decode($encrypted);
		$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
		$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

		$plain = false;
		try {
			$plain = sodium_crypto_secretbox_open(
				$ciphertext,
				$nonce,
				$key
			);
		} catch (SodiumException $e) {
			throw new \UnexpectedValueException('Decipher message failed: ' . $e->getMessage());
		}
		if (!is_string($plain)) {
			throw new \UnexpectedValueException('Invalid Key');
		}
		sodium_memzero($ciphertext);
		sodium_memzero($key);
		return $plain;
	}

	/**
	 * Encrypt a message
	 *
	 * @param  string  $message Message to encrypt
	 * @param  ?string $key     Mandatory encryption key, will throw exception if empty
	 * @return string
	 * @throws \Exception
	 * @throws \RangeException
	 */
	private function encryptData(string $message, ?string $key): string
	{
		if ($key === null) {
			throw new \UnexpectedValueException('Key not set');
		}
		$key = $this->createKey($key);
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		try {
			$cipher = base64_encode(
				$nonce
				. sodium_crypto_secretbox(
					$message,
					$nonce,
					$key,
				)
			);
		} catch (SodiumException $e) {
			throw new \UnexpectedValueException("Create encrypted message failed: " . $e->getMessage());
		}
		sodium_memzero($message);
		sodium_memzero($key);
		return $cipher;
	}

	/* ************************************************************************
	 * MARK: PUBLIC
	 * *************************************************************************/


	/**
	 * set a new key for encryption
	 *
	 * @param  string $key
	 * @return void
	 */
	public function setKey(string $key)
	{
		if (empty($key)) {
			throw new \UnexpectedValueException('Key cannot be empty');
		}
		$this->key = $key;
	}

	/**
	 * Decrypt a message
	 * static version
	 *
	 * @param  string $encrypted Message encrypted with safeEncrypt()
	 * @param  string $key       Encryption key (as hex string)
	 * @return string
	 * @throws \Exception
	 * @throws \RangeException
	 * @throws \UnexpectedValueException
	 * @throws \UnexpectedValueException
	 */
	public static function decryptKey(string $encrypted, string $key): string
	{
		return self::getInstance()->decryptData($encrypted, $key);
	}

	/**
	 * Decrypt a message
	 *
	 * @param  string $encrypted Message encrypted with safeEncrypt()
	 * @return string
	 * @throws \RangeException
	 * @throws \UnexpectedValueException
	 * @throws \UnexpectedValueException
	 */
	public function decrypt(string $encrypted): string
	{
		return $this->decryptData($encrypted, $this->key);
	}

	/**
	 * Encrypt a message
	 * static version
	 *
	 * @param  string $message Message to encrypt
	 * @param  string $key     Encryption key (as hex string)
	 * @return string
	 * @throws \Exception
	 * @throws \RangeException
	 */
	public static function encryptKey(string $message, string $key): string
	{
		return self::getInstance()->encryptData($message, $key);
	}

	/**
	 * Encrypt a message
	 *
	 * @param  string $message Message to encrypt
	 * @return string
	 * @throws \Exception
	 * @throws \RangeException
	 */
	public function encrypt(string $message): string
	{
		return $this->encryptData($message, $this->key);
	}
}

// __END__
