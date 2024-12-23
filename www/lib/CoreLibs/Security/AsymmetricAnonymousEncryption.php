<?php

/**
 * very simple asymmetric encryption
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

class AsymmetricAnonymousEncryption
{
	/** @var AsymmetricAnonymousEncryption self instance */
	private static AsymmetricAnonymousEncryption $instance;

	/** @var ?string key pair which holds secret and public key, needed for encryption */
	private ?string $key_pair = null;
	/** @var ?string public key, needed for decryption
	 * if not set but key_pair set, this will be extracted from key pair */
	private ?string $public_key = null;

	/**
	 * init class
	 * if key not passed, key must be set with createKey
	 *
	 * @param  string|null $key_pair
	 * @param  string|null $public_key
	 */
	public function __construct(
		#[\SensitiveParameter]
		string|null $key_pair = null,
		string|null $public_key = null
	) {
		if ($public_key !== null) {
			$this->setPublicKey($public_key);
		}
		if ($key_pair !== null) {
			$this->setKeyPair($key_pair);
			if (empty($public_key)) {
				$public_key = CreateKey::getPublicKey($key_pair);
				$this->setPublicKey($public_key);
			}
		}
	}

	/**
	 * Returns the singleton self object.
	 * For function wrapper use
	 *
	 * @param  string|null $key_pair
	 * @param  string|null $public_key
	 * @return AsymmetricAnonymousEncryption object
	 */
	public static function getInstance(
		#[\SensitiveParameter]
		string|null $key_pair = null,
		string|null $public_key = null
	): self {
		// new if no instsance or key is different
		if (
			empty(self::$instance) ||
			self::$instance->key_pair != $key_pair ||
			self::$instance->public_key != $public_key
		) {
			self::$instance = new self($key_pair, $public_key);
		}
		return self::$instance;
	}

	/**
	 * clean up
	 */
	public function __destruct()
	{
		if (empty($this->key_pair)) {
			return;
		}
		try {
			// would set it to null, but we we do not want to make key null
			sodium_memzero($this->key_pair);
			return;
		} catch (SodiumException) {
			// empty catch
		}
		if (is_null($this->key_pair)) {
			return;
		}
		$zero = str_repeat("\0", mb_strlen($this->key_pair, '8bit'));
		$this->key_pair = $this->key_pair ^ (
			$zero ^ $this->key_pair
		);
		unset($zero);
		unset($this->key_pair); /** @phan-suppress-current-line PhanTypeObjectUnsetDeclaredProperty */
	}

	/* ************************************************************************
	 * MARK: PRIVATE
	 * *************************************************************************/

	/**
	 * Create the internal key pair in binary
	 *
	 * @param  ?string $key_pair
	 * @return string
	 * @throws \UnexpectedValueException key pair empty
	 * @throws \UnexpectedValueException invalid hex key pair
	 * @throws \UnexpectedValueException key pair not correct size
	 */
	private function createKeyPair(
		#[\SensitiveParameter]
		?string $key_pair
	): string {
		if (empty($key_pair)) {
			throw new \UnexpectedValueException('Key pair cannot be empty');
		}
		try {
			$key_pair = CreateKey::hex2bin($key_pair);
		} catch (SodiumException $e) {
			sodium_memzero($key_pair);
			throw new \UnexpectedValueException('Invalid hex key pair: ' . $e->getMessage());
		}
		if (mb_strlen($key_pair, '8bit') !== SODIUM_CRYPTO_BOX_KEYPAIRBYTES) {
			sodium_memzero($key_pair);
			throw new \RangeException(
				'Key pair is not the correct size (must be '
					. SODIUM_CRYPTO_BOX_KEYPAIRBYTES . ' bytes long).'
			);
		}
		return $key_pair;
	}

	/**
	 * create the internal public key in binary
	 *
	 * @param  ?string $public_key
	 * @return string
	 * @throws \UnexpectedValueException public key empty
	 * @throws \UnexpectedValueException invalid hex key
	 * @throws \UnexpectedValueException invalid key length
	 */
	private function createPublicKey(?string $public_key): string
	{
		if (empty($public_key)) {
			throw new \UnexpectedValueException('Public key cannot be empty');
		}
		try {
			$public_key = CreateKey::hex2bin($public_key);
		} catch (SodiumException $e) {
			sodium_memzero($public_key);
			throw new \UnexpectedValueException('Invalid hex public key: ' . $e->getMessage());
		}
		if (mb_strlen($public_key, '8bit') !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
			sodium_memzero($public_key);
			throw new \RangeException(
				'Public key is not the correct size (must be '
					. SODIUM_CRYPTO_BOX_PUBLICKEYBYTES . ' bytes long).'
			);
		}
		return $public_key;
	}

	/**
	 * encrypt a message asymmetric with a bpulic key
	 *
	 * @param  string  $message
	 * @param  ?string $public_key
	 * @return string
	 * @throws \UnexpectedValueException create encryption failed
	 * @throws \UnexpectedValueException convert to base64 failed
	 */
	private function asymmetricEncryption(
		#[\SensitiveParameter]
		string $message,
		?string $public_key
	): string {
		$public_key = $this->createPublicKey($public_key);
		try {
			$encrypted = sodium_crypto_box_seal($message, $public_key);
		} catch (SodiumException $e) {
			sodium_memzero($message);
			throw new \UnexpectedValueException("Create encrypted message failed: " . $e->getMessage());
		}
		sodium_memzero($message);
		try {
			$result = sodium_bin2base64($encrypted, SODIUM_BASE64_VARIANT_ORIGINAL);
		} catch (SodiumException $e) {
			sodium_memzero($encrypted);
			throw new \UnexpectedValueException("bin2base64 failed: " . $e->getMessage());
		}
		sodium_memzero($encrypted);
		return $result;
	}

	/**
	 * decrypt a message that is asymmetric encrypted with a key pair
	 *
	 * @param  string  $message
	 * @param  ?string $key_pair
	 * @return string
	 * @throws \UnexpectedValueException message string empty
	 * @throws \UnexpectedValueException base64 decoding failed
	 * @throws \UnexpectedValueException decryption failed
	 * @throws \UnexpectedValueException could not decrypt message
	 */
	private function asymmetricDecryption(
		#[\SensitiveParameter]
		string $message,
		#[\SensitiveParameter]
		?string $key_pair
	): string {
		if (empty($message)) {
			throw new \UnexpectedValueException('Encrypted string cannot be empty');
		}
		$key_pair = $this->createKeyPair($key_pair);
		try {
			$result = sodium_base642bin($message, SODIUM_BASE64_VARIANT_ORIGINAL);
		} catch (SodiumException $e) {
			sodium_memzero($message);
			sodium_memzero($key_pair);
			throw new \UnexpectedValueException("base642bin failed: " . $e->getMessage());
		}
		sodium_memzero($message);
		$plaintext = false;
		try {
			$plaintext = sodium_crypto_box_seal_open($result, $key_pair);
		} catch (SodiumException $e) {
			sodium_memzero($message);
			sodium_memzero($key_pair);
			sodium_memzero($result);
			throw new \UnexpectedValueException("Decrypting message failed: " . $e->getMessage());
		}
		sodium_memzero($key_pair);
		sodium_memzero($result);
		if (!is_string($plaintext)) {
			throw new \UnexpectedValueException('Invalid key pair');
		}
		return $plaintext;
	}

	/* ************************************************************************
	 * MARK: PUBLIC
	 * *************************************************************************/

	/**
	 * sets the private key for encryption
	 *
	 * @param  string $key_pair Key pair in hex
	 * @return AsymmetricAnonymousEncryption
	 * @throws \UnexpectedValueException key pair empty
	 */
	public function setKeyPair(
		#[\SensitiveParameter]
		string $key_pair
	): AsymmetricAnonymousEncryption {
		if (empty($key_pair)) {
			throw new \UnexpectedValueException('Key pair cannot be empty');
		}
		// check if valid;
		$this->createKeyPair($key_pair);
		// set new key pair
		$this->key_pair = $key_pair;
		sodium_memzero($key_pair);
		// set public key if not set
		if (empty($this->public_key)) {
			$this->public_key = CreateKey::getPublicKey($this->key_pair);
			// check if valid
			$this->createPublicKey($this->public_key);
		}
		return $this;
	}

	/**
	 * check if set key pair matches given one
	 *
	 * @param  string $key_pair
	 * @return bool
	 */
	public function compareKeyPair(
		#[\SensitiveParameter]
		string $key_pair
	): bool {
		return $this->key_pair === $key_pair;
	}

	/**
	 * get the current set key pair, null if not set
	 *
	 * @return string|null
	 */
	public function getKeyPair(): ?string
	{
		return $this->key_pair;
	}

	/**
	 * sets the public key for decryption
	 * if only key pair exists Security\Create::getPublicKey() can be used to
	 * extract the public key from the key pair
	 *
	 * @param  string $public_key Public Key in hex
	 * @return AsymmetricAnonymousEncryption
	 * @throws \UnexpectedValueException public key empty
	 */
	public function setPublicKey(string $public_key): AsymmetricAnonymousEncryption
	{
		if (empty($public_key)) {
			throw new \UnexpectedValueException('Public key cannot be empty');
		}
		// check if valid
		$this->createPublicKey($public_key);
		$this->public_key = $public_key;
		sodium_memzero($public_key);
		return $this;
	}

	/**
	 * check if the set public key matches the given one
	 *
	 * @param  string $public_key
	 * @return bool
	 */
	public function comparePublicKey(string $public_key): bool
	{
		return $this->public_key === $public_key;
	}

	/**
	 * get the current set public key, null if not set
	 *
	 * @return string|null
	 */
	public function getPublicKey(): ?string
	{
		return $this->public_key;
	}

	/**
	 * Encrypt a message with a public key
	 * static version
	 *
	 * @param  string $message    Message to encrypt
	 * @param  string $public_key Public key in hex to encrypt message with
	 * @return string             Encrypted message as hex string
	 */
	public static function encryptKey(
		#[\SensitiveParameter]
		string $message,
		string $public_key
	): string {
		return self::getInstance()->asymmetricEncryption($message, $public_key);
	}

	/**
	 * Encrypt a message
	 *
	 * @param  string $message Message to ecnrypt
	 * @return string          Encrypted message as hex string
	 */
	public function encrypt(
		#[\SensitiveParameter]
		string $message
	): string {
		return $this->asymmetricEncryption($message, $this->public_key);
	}

	/**
	 * decrypt a message with a key pair
	 * static version
	 *
	 * @param  string $message  Message to decrypt in hex
	 * @param  string $key_pair Key pair in hex to decrypt the message with
	 * @return string           Decrypted message
	 */
	public static function decryptKey(
		#[\SensitiveParameter]
		string $message,
		#[\SensitiveParameter]
		string $key_pair
	): string {
		return self::getInstance()->asymmetricDecryption($message, $key_pair);
	}

	/**
	 * decrypt a message
	 *
	 * @param  string $message Message to decrypt in hex
	 * @return string          Decrypted message
	 */
	public function decrypt(
		#[\SensitiveParameter]
		string $message
	): string {
		return $this->asymmetricDecryption($message, $this->key_pair);
	}
}

// __END__
