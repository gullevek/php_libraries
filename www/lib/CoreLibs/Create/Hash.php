<?php

/*
 * hash wrapper functions for old problem fixes
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Hash
{
	/**
	 * checks php version and if >=5.2.7 it will flip the string
	 * can return empty string if none of string sets work
	 * hash returns false
	 * preg_replace fails for older php version
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
	 * allowed adler32, fnv132, fnv1a32, joaat
	 * all that create 8 char long hashes
	 * @param  string $string    string to hash
	 * @param  string $hash_type hash type (default adler32)
	 * @return string            hash of the string
	 */
	public static function __hash(string $string, string $hash_type = 'adler32'): string
	{
		if (!in_array($hash_type, ['adler32', 'fnv132', 'fnv1a32', 'joaat'])) {
			$hash_type = 'adler32';
		}
		return hash($hash_type, $string);
	}

	/**
	 * create a unique id with the standard hash type defined in __hash
	 *
	 * @return string Unique ID with fixed length of 8 characters
	 */
	public static function __uniqId(): string
	{
		return self::__hash(uniqid((string)rand(), true));
	}
}

// __END__
