<?php

/*
 * hash wrapper functions for old problem fixes
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Hash
{
	/** @var string default short hash -> deprecated use STANDARD_HASH_SHORT */
	public const DEFAULT_HASH = 'adler32';
	/** @var string default long hash (40 chars) */
	public const STANDARD_HASH_LONG = 'ripemd160';
	/** @var string default short hash (8 chars) */
	public const STANDARD_HASH_SHORT = 'adler32';
	/** @var string this is the standard hash to use hashStd and hash (64 chars) */
	public const STANDARD_HASH = 'sha256';

	/**
	 * checks php version and if >=5.2.7 it will flip the string
	 * can return empty string if none of string sets work
	 * hash returns false
	 * preg_replace fails for older php version
	 * Use __hash with crc32b or hash('crc32b', ...) for correct output
	 * For future short hashes use hashShort() instead
	 *
	 * @param  string $string string to crc
	 * @return string         crc32b hash (old type)
	 */
	public static function __crc32b(string $string): string
	{
		// do normal hash crc32b
		$string = hash('crc32b', $string);
		// if bigger than 5.2.7, we need to "unfix" the fix
		if (\CoreLibs\Check\PhpVersion::checkPHPVersion('5.2.7')) {
			// flip it back to old (two char groups)
			$string = preg_replace("/^([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})$/", "$4$3$2$1", $string);
		}
		if (!is_string($string)) {
			$string = '';
		}
		return $string;
	}

	/**
	 * replacement for __crc32b call
	 *
	 * @param  string $string  string to hash
	 * @param  bool   $use_sha [default=false] use sha1 instead of crc32b
	 * @return string          hash of the string
	 * @deprecated use __crc32b() for drop in replacement with default, or sha1Short() for use sha true
	 */
	public static function __sha1Short(string $string, bool $use_sha = false): string
	{
		if ($use_sha) {
			return self::sha1Short($string);
		} else {
			return self::__crc32b($string);
		}
	}

	/**
	 * returns a short sha1
	 *
	 * @param  string $string  string to hash
	 * @return string          hash of the string
	 */
	public static function sha1Short(string $string): string
	{
		// return only the first 9 characters
		return substr(hash('sha1', $string), 0, 9);
	}

	/**
	 * replacemend for __crc32b call (alternate)
	 * defaults to adler 32
	 * allowed: any in hash algos list, default to adler 32
	 * all that create 8 char long hashes
	 *
	 * @param  string $string    string to hash
	 * @param  string $hash_type [default=STANDARD_HASH_SHORT] hash type (default adler32)
	 * @return string            hash of the string
	 * @deprecated use hashShort() of short hashes with adler 32 or hash() for other hash types
	 */
	public static function __hash(
		string $string,
		string $hash_type = self::STANDARD_HASH_SHORT
	): string {
		return self::hash($string, $hash_type);
	}

	/**
	 * check if hash type is valid, returns false if not
	 *
	 * @param  string $hash_type
	 * @return bool
	 */
	public static function isValidHashType(string $hash_type): bool
	{
		if (!in_array($hash_type, hash_algos())) {
			return false;
		}
		return true;
	}

	/**
	 * check if hash hmac type is valid, returns false if not
	 *
	 * @param  string $hash_hmac_type
	 * @return bool
	 */
	public static function isValidHashHmacType(string $hash_hmac_type): bool
	{
		if (!in_array($hash_hmac_type, hash_hmac_algos())) {
			return false;
		}
		return true;
	}

	/**
	 * creates a hash over string if any valid hash given.
	 * if no hash type set use sha256
	 *
	 * @param  string $string    string to hash
	 * @param  string $hash_type [default=STANDARD_HASH] hash type (default sha256)
	 * @return string            hash of the string
	 */
	public static function hash(
		string $string,
		string $hash_type = self::STANDARD_HASH
	): string {
		if (
			empty($hash_type) ||
			!in_array($hash_type, hash_algos())
		) {
			// fallback to default hash type if empty or invalid
			$hash_type = self::STANDARD_HASH;
		}
		return hash($hash_type, $string);
	}

	/**
	 * creates a hash mac key
	 *
	 * @param  string $string    string to hash mac
	 * @param  string $key       key to use
	 * @param  string $hash_type [default=STANDARD_HASH]
	 * @return string            hash mac string
	 */
	public static function hashHmac(
		string $string,
		#[\SensitiveParameter]
		string $key,
		string $hash_type = self::STANDARD_HASH
	): string {
		if (
			empty($hash_type) ||
			!in_array($hash_type, hash_hmac_algos())
		) {
			// fallback to default hash type if e or invalid
			$hash_type = self::STANDARD_HASH;
		}
		return hash_hmac($hash_type, $string, $key);
	}

	/**
	 * short hash with max length of 8, uses adler32
	 *
	 * @param  string $string string to hash
	 * @return string         hash of the string
	 */
	public static function hashShort(string $string): string
	{
		return hash(self::STANDARD_HASH_SHORT, $string);
	}

	/**
	 * Wrapper function for standard long hash
	 *
	 * @param string $string String to be hashed
	 * @return string        Hashed string
	 * @deprecated use hashLong()
	 */
	public static function __hashLong(string $string): string
	{
		return self::hashLong($string);
	}

	/**
	 * Wrapper function for standard long hash, uses ripmd160
	 *
	 * @param string $string String to be hashed
	 * @return string        Hashed string
	 */
	public static function hashLong(string $string): string
	{
		return hash(self::STANDARD_HASH_LONG, $string);
	}

	/**
	 * create standard hash basd on STANDAR_HASH, currently sha256
	 *
	 * @param  string $string string in
	 * @return string         hash of the string
	 */
	public static function hashStd(string $string): string
	{
		return self::hash($string, self::STANDARD_HASH);
	}
}

// __END__
