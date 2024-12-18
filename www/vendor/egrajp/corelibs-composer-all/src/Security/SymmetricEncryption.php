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

	/** @var ?string bin hex key */
	private ?string $key = null;

	/**
	 * init class
	 * if key not passed, key must be set with createKey
	 *
	 * @param string|null $key encryption key
	 */
	public function __construct(
		?string $key = null
	) {
		if ($key !== null) {
			$this->setKey($key);
		}
	}

	/**
	 * Returns the singleton self object.
	 * For function wrapper use
	 *
	 * @param  string|null $key encryption key
	 * @return SymmetricEncryption object
	 */
	public static function getInstance(?string $key = null): self
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

	/**
	 * clean up
	 *
	 * @return void
	 */
	public function __deconstruct()
	{
		if (empty($this->key)) {
			return;
		}
		try {
			// would set it to null, but we we do not want to make key null
			sodium_memzero($this->key);
			return;
		} catch (SodiumException) {
			// empty catch
		}
		if (is_null($this->key)) {
			return;
		}
		$zero = str_repeat("\0", mb_strlen($this->key, '8bit'));
		$this->key = $this->key ^ (
			$zero ^ $this->key
		);
		unset($zero);
		unset($this->key); /** @phan-suppress-current-line PhanTypeObjectUnsetDeclaredProperty */
	}

	/* ************************************************************************
	 * MARK: PRIVATE
	 * *************************************************************************/

	/**
	 * create key and check validity
	 *
	 * @param  ?string $key The key from which the binary key will be created
	 * @return string       Binary key string
	 * @throws \UnexpectedValueException empty key
	 * @throws \UnexpectedValueException invalid hex key
	 * @throws \RangeException invalid length
	 */
	private function createKey(
		#[\SensitiveParameter]
		?string $key
	): string {
		if (empty($key)) {
			throw new \UnexpectedValueException('Key cannot be empty');
		}
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
	 * @throws \UnexpectedValueException key cannot be empty
	 * @throws \UnexpectedValueException decipher message failed
	 * @throws \UnexpectedValueException invalid key
	 */
	private function decryptData(
		#[\SensitiveParameter]
		string $encrypted,
		#[\SensitiveParameter]
		?string $key
	): string {
		if (empty($encrypted)) {
			throw new \UnexpectedValueException('Encrypted string cannot be empty');
		}
		$key = $this->createKey($key);
		$decoded = base64_decode($encrypted);
		$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
		$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

		$plaintext = false;
		try {
			$plaintext = sodium_crypto_secretbox_open(
				$ciphertext,
				$nonce,
				$key
			);
		} catch (SodiumException $e) {
			sodium_memzero($ciphertext);
			sodium_memzero($key);
			throw new \UnexpectedValueException('Decipher message failed: ' . $e->getMessage());
		}
		sodium_memzero($ciphertext);
		sodium_memzero($key);
		if (!is_string($plaintext)) {
			throw new \UnexpectedValueException('Invalid Key');
		}
		return $plaintext;
	}

	/**
	 * Encrypt a message
	 *
	 * @param  string  $message Message to encrypt
	 * @param  ?string $key     Mandatory encryption key, will throw exception if empty
	 * @return string           Ciphered text
	 * @throws \UnexpectedValueException create message failed
	 */
	private function encryptData(
		#[\SensitiveParameter]
		string $message,
		#[\SensitiveParameter]
		?string $key
	): string {
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
			sodium_memzero($message);
			sodium_memzero($key);
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
	 * @throws \UnexpectedValueException key cannot be empty
	 */
	public function setKey(
		#[\SensitiveParameter]
		string $key
	) {
		if (empty($key)) {
			throw new \UnexpectedValueException('Key cannot be empty');
		}
		// check that this is a valid key
		$this->createKey($key);
		// set key
		$this->key = $key;
		sodium_memzero($key);
	}

	/**
	 * Checks if set key is equal to parameter key
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function compareKey(
		#[\SensitiveParameter]
		string $key
	): bool {
		return $key === $this->key;
	}

	/**
	 * returns the current set key, null if not set
	 *
	 * @return ?string
	 */
	public function getKey(): ?string
	{
		return $this->key;
	}

	/**
	 * Decrypt a message
	 * static version
	 *
	 * @param  string $encrypted Message encrypted with safeEncrypt()
	 * @param  string $key       Encryption key (as hex string)
	 * @return string
	 */
	public static function decryptKey(
		#[\SensitiveParameter]
		string $encrypted,
		#[\SensitiveParameter]
		string $key
	): string {
		return self::getInstance()->decryptData($encrypted, $key);
	}

	/**
	 * Decrypt a message
	 *
	 * @param  string $encrypted Message encrypted with safeEncrypt()
	 * @return string
	 */
	public function decrypt(
		#[\SensitiveParameter]
		string $encrypted
	): string {
		return $this->decryptData($encrypted, $this->key);
	}

	/**
	 * Encrypt a message
	 * static version
	 *
	 * @param  string $message Message to encrypt
	 * @param  string $key     Encryption key (as hex string)
	 * @return string
	 */
	public static function encryptKey(
		#[\SensitiveParameter]
		string $message,
		#[\SensitiveParameter]
		string $key
	): string {
		return self::getInstance()->encryptData($message, $key);
	}

	/**
	 * Encrypt a message
	 *
	 * @param  string $message Message to encrypt
	 * @return string
	 */
	public function encrypt(
		#[\SensitiveParameter]
		string $message
	): string {
		return $this->encryptData($message, $this->key);
	}
}

// __END__
