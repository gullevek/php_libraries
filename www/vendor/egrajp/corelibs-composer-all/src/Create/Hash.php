<?php

/*
 * hash wrapper functions for old problem fixes
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Hash
{
	public const DEFAULT_HASH = 'adler32';
	public const STANDARD_HASH_LONG = 'ripemd160';
	public const STANDARD_HASH_SHORT = 'adler32';

	/**
	 * checks php version and if >=5.2.7 it will flip the string
	 * can return empty string if none of string sets work
	 * hash returns false
	 * preg_replace fails for older php version
	 * Use __hash with crc32b or hash('crc32b', ...) for correct output
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
	 * @param  bool   $use_sha use sha instead of crc32b (default false)
	 * @return string          hash of the string
	 */
	public static function __sha1Short(string $string, bool $use_sha = false): string
	{
		if ($use_sha) {
			// return only the first 9 characters
			return substr(hash('sha1', $string), 0, 9);
		} else {
			return self::__crc32b($string);
		}
	}

	/**
	 * replacemend for __crc32b call (alternate)
	 * defaults to adler 32
	 * allowed: any in hash algos list, default to adler 32
	 * all that create 8 char long hashes
	 *
	 * @param  string $string    string to hash
	 * @param  string $hash_type hash type (default adler32)
	 * @return string            hash of the string
	 */
	public static function __hash(
		string $string,
		string $hash_type = self::DEFAULT_HASH
	): string {
		// if not empty, check if in valid list
		if (
			empty($hash_type) ||
			!in_array($hash_type, hash_algos())
		) {
			// fallback to default hash type if none set or invalid
			$hash_type = self::DEFAULT_HASH;
		}
		return hash($hash_type, $string);
	}

	/**
	 * Wrapper function for standard long hashd
	 *
	 * @param string $string String to be hashed
	 * @return string        Hashed string
	 */
	public static function __hashLong(string $string): string
	{
		return hash(self::STANDARD_HASH_LONG, $string);
	}
}

// __END__
