<?php

/**
 * very simple symmetric encryption
 * better use: https://paragonie.com/project/halite
 *
 * this is for creating secret keys for
 * Security\SymmetricEncryption
 */

declare(strict_types=1);

namespace CoreLibs\Security;

class CreateKey
{
	/**
	 * Create a random key that is a hex string
	 *
	 * @return string Hex string key for encrypting
	 */
	public static function generateRandomKey(): string
	{
		return self::bin2hex(self::randomKey());
	}

	/**
	 * create a random string as binary to encrypt data
	 * to store it in clear text in some .env file use bin2hex
	 *
	 * @return string Binary string for encryption
	 */
	public static function randomKey(): string
	{
		return random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
	}

	/**
	 * creates a sodium cyptobox keypair as hex string
	 *
	 * @return string hex string for the keypair
	 */
	public static function createKeyPair(): string
	{
		return self::bin2hex(sodium_crypto_box_keypair());
	}

	/**
	 * extracts the public key and returns it as hex string from the hex keypari
	 *
	 * @param  string $hex_keypair hex encoded keypair
	 * @return string              hex encoded public key
	 */
	public static function getPublicKey(
		#[\SensitiveParameter]
		string $hex_keypair
	): string {
		return self::bin2hex(sodium_crypto_box_publickey(self::hex2bin($hex_keypair)));
	}

	/**
	 * convert binary key to hex string
	 *
	 * @param  string $hex_key Convert binary key string to hex
	 * @return string
	 */
	public static function bin2hex(
		#[\SensitiveParameter]
		string $hex_key
	): string {
		return sodium_bin2hex($hex_key);
	}

	/**
	 * convert hex string to binary key
	 *
	 * @param  string $string_key Convery hex key string to binary
	 * @return string
	 */
	public static function hex2bin(
		#[\SensitiveParameter]
		string $string_key
	): string {
		return sodium_hex2bin($string_key);
	}
}

// __END__
