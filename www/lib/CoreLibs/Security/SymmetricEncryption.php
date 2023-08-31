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
	/**
	 * Encrypt a message
	 *
	 * @param  string $message Message to encrypt
	 * @param  string $key     Encryption key (as hex string)
	 * @return string
	 * @throws \Exception
	 * @throws \RangeException
	 */
	public static function encrypt(string $message, string $key): string
	{
		try {
			$key = CreateKey::hex2bin($key);
		} catch (SodiumException $e) {
			throw new \UnexpectedValueException('Invalid hex key');
		}
		if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
			throw new \RangeException(
				'Key is not the correct size (must be '
					. 'SODIUM_CRYPTO_SECRETBOX_KEYBYTES bytes long).'
			);
		}
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

		$cipher = base64_encode(
			$nonce
			. sodium_crypto_secretbox(
				$message,
				$nonce,
				$key
			)
		);
		sodium_memzero($message);
		sodium_memzero($key);
		return $cipher;
	}

	/**
	 * Decrypt a message
	 *
	 * @param  string $encrypted Message encrypted with safeEncrypt()
	 * @param  string $key       Encryption key (as hex string)
	 * @return string
	 * @throws \Exception
	 */
	public static function decrypt(string $encrypted, string $key): string
	{
		try {
			$key = CreateKey::hex2bin($key);
		} catch (SodiumException $e) {
			throw new \Exception('Invalid hex key');
		}
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
			throw new \UnexpectedValueException('Invalid ciphertext (too short)');
		}
		if (!is_string($plain)) {
			throw new \UnexpectedValueException('Invalid Key');
		}
		sodium_memzero($ciphertext);
		sodium_memzero($key);
		return $plain;
	}
}

// __END__
