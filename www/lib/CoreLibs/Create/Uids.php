<?php

declare(strict_types=1);

namespace CoreLibs\Create;

class Uids
{
	// what to use as a default hash if non ise set and no DEFAULT_HASH is defined
	private const FALLBACK_HASH = 'sha256';

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
			case 'sha256':
				$uniq_id = hash('sha256', uniqid((string)rand(), true));
				break;
			default:
				// fallback to this hash type
				$hash = self::FALLBACK_HASH;
				if (
					defined('DEFAULT_HASH') && !empty(DEFAULT_HASH) &&
					in_array(DEFAULT_HASH, hash_algos())
				) {
					$hash = DEFAULT_HASH;
				}
				$uniq_id = hash($hash, uniqid((string)rand(), true));
				break;
		}
		return $uniq_id;
	}
}

// __END__
