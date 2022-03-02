<?php

declare(strict_types=1);

namespace CoreLibs\Create;

class Uids
{
	// what to use as a default hash if non ise set and no DEFAULT_HASH is defined
	public const DEFAULT_HASH = 'sha256';
	public const STANDARD_HASH_LONG = 'ripemd160';
	public const STANDARD_HASH_SHORT = 'adler32';

	/**
	 * creates psuedo random uuid v4
	 * Code take from class here:
	 * https://www.php.net/manual/en/function.uniqid.php#94959
	 * @return string pseudo random uuid v4
	 */
	public static function uuidv4(): string
	{
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
			// 48 bits for "node"
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}

	/**
	 * TODO: make this a proper uniq ID creation
	 *       add uuidv4 subcall to the uuid function too
	 * creates a uniq id
	 * @param  string $type uniq id type, currently md5 or sha256 allowed
	 *                      if not set will use DEFAULT_HASH if set
	 * @return string       uniq id
	 */
	public static function uniqId(string $type = ''): string
	{
		$uniq_id = '';
		switch ($type) {
			case 'md5':
				$uniq_id = md5(uniqid((string)rand(), true));
				break;
			case self::DEFAULT_HASH:
				$uniq_id = hash(self::DEFAULT_HASH, uniqid((string)rand(), true));
				break;
			case self::STANDARD_HASH_LONG:
				$uniq_id = hash(self::STANDARD_HASH_LONG, uniqid((string)rand(), true));
				break;
			case self::STANDARD_HASH_SHORT:
				$uniq_id = hash(self::STANDARD_HASH_SHORT, uniqid((string)rand(), true));
				break;
			default:
				// if not empty, check if in valid list
				if (
					!empty($type) &&
					in_array($type, hash_algos())
				) {
					$hash = $type;
				} else {
					// fallback to default hash type if none set or invalid
					$hash = self::DEFAULT_HASH;
				}
				$uniq_id = hash($hash, uniqid((string)rand(), true));
				break;
		}
		return $uniq_id;
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
