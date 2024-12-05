<?php

/**
 * Create uniqIds
 *
 * If convert ID to hash:
 * https://github.com/vinkla/hashids
 */

declare(strict_types=1);

namespace CoreLibs\Create;

class Uids
{
	// what to use as a default hash if non ise set and no DEFAULT_HASH is defined

	/** @var int */
	public const DEFAULT_UNNIQ_ID_LENGTH = 64;
	/** @var string */
	public const STANDARD_HASH_LONG = 'ripemd160';
	/** @var string */
	public const STANDARD_HASH_SHORT = 'adler32';

	/**
	 * Create unique id, lower length is for
	 *
	 * @param  int    $length Length for uniq id, min is 4 characters
	 *                        Uneven lengths will return lower bound (9 -> 8)
	 * @param  bool   $force_length [default=false] if set to true and we have
	 *                              uneven length, then we shorten to this length
	 * @return string         Uniq id
	 */
	private static function uniqIdL(int $length = 64, bool $force_length = false): string
	{
		$uniqid_length = ($length < 4) ? 4 : $length;
		if ($force_length) {
			$uniqid_length++;
		}
		/** @var int<1,max> make sure that internal this is correct */
		$random_bytes_length = (int)(($uniqid_length - ($uniqid_length % 2)) / 2);
		$uniqid = bin2hex(random_bytes($random_bytes_length));
		// if not forced shorten return next lower length
		if (!$force_length) {
			return $uniqid;
		}
		return substr($uniqid, 0, $length);
	}

	/**
	 * creates psuedo random uuid v4
	 * Code take from class here:
	 * https://www.php.net/manual/en/function.uniqid.php#94959
	 *
	 * @return string pseudo random uuid v4
	 */
	public static function uuidv4(): string
	{
		$data = random_bytes(16);
		assert(strlen($data) == 16);

		// 0-1: 32 bits for "time_low"
		//   2: 16 bits for "time_mid"
		//   3: 16 bits for "time_hi_and_version",
		//      four most significant bits holds version number 4
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		//   4: 16 bits, 8 bits for "clk_seq_hi_res",
		//      8 bits for "clk_seq_low",
		//      two most significant bits holds zero and one for variant DCE1.1
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
		// 5-7: 48 bits for "node"

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	/**
	 * regex validate uuid v4
	 *
	 * @param  string $uuidv4
	 * @return bool
	 */
	public static function validateUuuidv4(string $uuidv4): bool
	{
		if (!preg_match("/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/", $uuidv4)) {
			return false;
		}
		return true;
	}

	/**
	 * creates a uniq id based on lengths
	 *
	 * @param  int|string $length Either length in int, or fallback type for length
	 *                            for string type md5 (32), sha256 (64)
	 *                            STANDARD_HASH_LONG: ripemd160 (40)
	 *                            STANDARD_HASH_SHORT: adler32 (8)
	 *                            It is recommended to use the integer
	 * @param  bool       $force_length [default=false] if set to true and we have
	 *                                  uneven length, then we shorten to this length
	 * @return string             Uniq id
	 */
	public static function uniqId(
		int|string $length = self::DEFAULT_UNNIQ_ID_LENGTH,
		bool $force_length = false
	): string {
		if (is_int($length)) {
			return self::uniqIdL($length, $force_length);
		}
		switch ($length) {
			case 'md5':
				$length = 32;
				break;
			case 'sha256':
				$length = 64;
				break;
			case self::STANDARD_HASH_LONG:
				$length = 40;
				break;
			case self::STANDARD_HASH_SHORT:
				$length = 8;
				break;
			default:
				$length = 64;
				break;
		}
		return self::uniqIdL($length);
	}

	/**
	 * create a unique id with the standard hash type defined in __hash
	 *
	 * @return string Unique ID with fixed length of 8 characters
	 */
	public static function uniqIdShort(): string
	{
		return self::uniqId(self::STANDARD_HASH_SHORT);
	}

	/**
	 * create a unique id with the standard long hash type
	 * defined in __hashLong
	 *
	 * @return string Unique ID with length of current default long hash
	 */
	public static function uniqIdLong(): string
	{
		return self::uniqId(self::STANDARD_HASH_LONG);
	}
}

// __END__
